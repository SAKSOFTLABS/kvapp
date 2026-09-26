<?php

namespace App\Services;

use App\Models\Item;
use App\Models\MainStock;
use App\Models\StaffStock;
use App\Models\StockTransaction;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\ServiceTransaction;
use App\Models\ServiceItem;
use App\Models\SetTopBox;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Exception;

class StockManagementService
{
    /**
     * Add stock to main store ledger
     */
    public function addMainStock(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $item = Item::findOrFail($data['item_id']);

            $unitPrice = isset($data['unit_price']) ? (float)$data['unit_price'] : (float)($data['purchase_price'] ?? 0);

            // Record transaction
            $st = StockTransaction::create([
                'transaction_type' => 'purchase',
                'date' => $data['date'],
                'item_id' => $data['item_id'],
                'quantity' => $data['quantity'],
                'unit_price' => $unitPrice,
                'supplier' => $data['supplier'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $userId,
            ]);

            // Update main stock quantity
            $mainStock = MainStock::firstOrCreate(
                ['item_id' => $data['item_id']],
                ['quantity' => 0]
            );

            $mainStock->increment('quantity', $data['quantity']);

            return $st;
        });
    }

    /**
     * Update existing main stock entry and adjust main stock ledger
     */
    public function updateMainStock($id, array $data, $userId)
    {
        return DB::transaction(function () use ($id, $data, $userId) {
            $st = StockTransaction::findOrFail($id);
            
            $oldItemId = $st->item_id;
            $oldQty = (float) $st->quantity;
            $newItemId = $data['item_id'];
            $newQty = (float) $data['quantity'];
            $unitPrice = isset($data['unit_price']) ? (float)$data['unit_price'] : (float)($data['purchase_price'] ?? 0);

            if ($oldItemId == $newItemId) {
                $diff = $newQty - $oldQty;
                $mainStock = MainStock::firstOrCreate(['item_id' => $newItemId], ['quantity' => 0]);
                
                if ($mainStock->quantity + $diff < 0) {
                    throw new Exception("Cannot update stock entry: Main Store stock for this item would fall below 0. Available: {$mainStock->quantity}.");
                }
                
                $mainStock->quantity += $diff;
                $mainStock->save();
            } else {
                // Remove old stock
                $oldMainStock = MainStock::where('item_id', $oldItemId)->first();
                if ($oldMainStock && $oldMainStock->quantity < $oldQty) {
                    throw new Exception("Cannot change item: Available stock for original item is less than entry quantity.");
                }
                if ($oldMainStock) {
                    $oldMainStock->decrement('quantity', $oldQty);
                }

                // Add new stock
                $newMainStock = MainStock::firstOrCreate(['item_id' => $newItemId], ['quantity' => 0]);
                $newMainStock->increment('quantity', $newQty);
            }

            $st->update([
                'date' => $data['date'],
                'item_id' => $newItemId,
                'quantity' => $newQty,
                'unit_price' => $unitPrice,
                'supplier' => $data['supplier'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            return $st;
        });
    }

    /**
     * Delete main stock entry and revert main stock ledger
     */
    public function deleteMainStock($id, $userId)
    {
        return DB::transaction(function () use ($id, $userId) {
            $st = StockTransaction::findOrFail($id);
            $qty = (float) $st->quantity;

            $mainStock = MainStock::where('item_id', $st->item_id)->first();
            if ($mainStock && $mainStock->quantity < $qty) {
                throw new Exception("Cannot delete stock entry: Available Main Store stock ({$mainStock->quantity}) is less than entry quantity ({$qty}).");
            }

            if ($mainStock) {
                $mainStock->decrement('quantity', $qty);
            }

            $st->delete();
            return true;
        });
    }

    /**
     * Create Stock Transfer to Staff
     */
    public function transferStock(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $staffId = $data['staff_id'];
            $itemId = $data['item_id'];
            $qty = (float) $data['quantity'];

            // Check main stock availability
            $mainStock = MainStock::where('item_id', $itemId)->first();
            $available = $mainStock ? (float)$mainStock->quantity : 0;

            if ($available < $qty) {
                $item = Item::find($itemId);
                $itemName = $item ? $item->item_name : "Item #{$itemId}";
                throw new Exception("Insufficient main store stock for '{$itemName}'. Available: {$available}, Requested: {$qty}.");
            }

            // Deduct from Main Stock
            $mainStock->decrement('quantity', $qty);

            // Add to Staff Stock
            $staffStock = StaffStock::firstOrCreate(
                ['staff_id' => $staffId, 'item_id' => $itemId],
                ['quantity' => 0]
            );
            $staffStock->increment('quantity', $qty);

            // Generate transfer code
            $transferCode = 'TRF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $transfer = StockTransfer::create([
                'transfer_code' => $transferCode,
                'transfer_date' => $data['transfer_date'],
                'staff_id' => $staffId,
                'item_id' => $itemId,
                'quantity' => $qty,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $userId,
            ]);

            // Record transaction log
            $item = Item::find($itemId);
            StockTransaction::create([
                'transaction_type' => 'transfer_out',
                'date' => $data['transfer_date'],
                'item_id' => $itemId,
                'quantity' => $qty,
                'unit_price' => $item ? (float)$item->purchase_price : 0,
                'staff_id' => $staffId,
                'remarks' => "Transfer to Staff {$staffId} (Code: {$transferCode})",
                'created_by' => $userId,
            ]);

            return $transfer;
        });
    }

    /**
     * Update Stock Transfer
     */
    public function updateStockTransfer($id, array $data, $userId)
    {
        return DB::transaction(function () use ($id, $data, $userId) {
            $transfer = StockTransfer::findOrFail($id);

            $oldStaffId = $transfer->staff_id;
            $oldItemId = $transfer->item_id;
            $oldQty = (float) $transfer->quantity;

            $newStaffId = $data['staff_id'];
            $newItemId = $data['item_id'];
            $newQty = (float) $data['quantity'];

            // 1. Revert Old Transfer
            // Check if old technician has enough stock balance to revert
            $oldStaffStock = StaffStock::where('staff_id', $oldStaffId)->where('item_id', $oldItemId)->first();
            $oldStaffAvail = $oldStaffStock ? (float)$oldStaffStock->quantity : 0;

            if ($oldStaffAvail < $oldQty) {
                throw new Exception("Cannot edit transfer: Technician has already used some of the transferred stock. Available technician stock: {$oldStaffAvail}.");
            }

            // Revert old staff stock
            $oldStaffStock->decrement('quantity', $oldQty);

            // Revert old main stock
            $oldMainStock = MainStock::firstOrCreate(['item_id' => $oldItemId], ['quantity' => 0]);
            $oldMainStock->increment('quantity', $oldQty);

            // 2. Apply New Transfer
            // Check main stock availability for new item
            $newMainStock = MainStock::where('item_id', $newItemId)->first();
            $newMainAvail = $newMainStock ? (float)$newMainStock->quantity : 0;

            if ($newMainAvail < $newQty) {
                // Rollback by restoring state before throwing exception
                $item = Item::find($newItemId);
                $itemName = $item ? $item->item_name : "Item #{$newItemId}";
                throw new Exception("Insufficient main store stock for '{$itemName}'. Available: {$newMainAvail}, Requested: {$newQty}.");
            }

            // Deduct new main stock
            $newMainStock->decrement('quantity', $newQty);

            // Add new staff stock
            $newStaffStock = StaffStock::firstOrCreate(
                ['staff_id' => $newStaffId, 'item_id' => $newItemId],
                ['quantity' => 0]
            );
            $newStaffStock->increment('quantity', $newQty);

            // 3. Update Transfer record
            $transfer->update([
                'transfer_date' => $data['transfer_date'],
                'staff_id' => $newStaffId,
                'item_id' => $newItemId,
                'quantity' => $newQty,
                'remarks' => $data['remarks'] ?? null,
            ]);

            return $transfer;
        });
    }

    /**
     * Delete Stock Transfer and revert ledgers
     */
    public function deleteStockTransfer($id, $userId)
    {
        return DB::transaction(function () use ($id, $userId) {
            $transfer = StockTransfer::findOrFail($id);
            $qty = (float) $transfer->quantity;

            // Check if technician has enough stock balance to revert
            $staffStock = StaffStock::where('staff_id', $transfer->staff_id)
                ->where('item_id', $transfer->item_id)
                ->first();

            $staffAvail = $staffStock ? (float)$staffStock->quantity : 0;

            if ($staffAvail < $qty) {
                throw new Exception("Cannot delete transfer: Technician has already used some of this transferred stock. Available technician stock: {$staffAvail}.");
            }

            // Deduct from staff stock
            $staffStock->decrement('quantity', $qty);

            // Return to main stock
            $mainStock = MainStock::firstOrCreate(['item_id' => $transfer->item_id], ['quantity' => 0]);
            $mainStock->increment('quantity', $qty);

            $transfer->delete();
            return true;
        });
    }

    /**
     * Transfer stock from main store to a technician staff (Batch)
     */
    public function transferStockToStaff(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $staffId = $data['staff_id'];
            $items = $data['items']; // array of ['item_id' => x, 'quantity' => y]

            // Check main stock availability for all items first
            foreach ($items as $itemData) {
                $itemId = $itemData['item_id'];
                $qty = (float) $itemData['quantity'];

                $mainStock = MainStock::where('item_id', $itemId)->first();
                $available = $mainStock ? (float)$mainStock->quantity : 0;

                if ($available < $qty) {
                    $item = Item::find($itemId);
                    $itemName = $item ? $item->item_name : "Item #{$itemId}";
                    throw new Exception("Insufficient main stock for '{$itemName}'. Available: {$available}, Requested: {$qty}.");
                }
            }

            // Generate transfer code
            $transferCode = 'TRF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $transfer = StockTransfer::create([
                'transfer_code' => $transferCode,
                'transfer_date' => $data['transfer_date'],
                'staff_id' => $staffId,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($items as $itemData) {
                $itemId = $itemData['item_id'];
                $qty = (float) $itemData['quantity'];
                $item = Item::find($itemId);
                $unitPrice = $item ? (float)$item->purchase_price : 0;

                // Deduct from Main Stock
                MainStock::where('item_id', $itemId)->decrement('quantity', $qty);

                // Add to Staff Stock
                $staffStock = StaffStock::firstOrCreate(
                    ['staff_id' => $staffId, 'item_id' => $itemId],
                    ['quantity' => 0]
                );
                $staffStock->increment('quantity', $qty);

                // Record transfer line item
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'item_id' => $itemId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                ]);

                // Record ledger transaction
                StockTransaction::create([
                    'transaction_type' => 'transfer_out',
                    'date' => $data['transfer_date'],
                    'item_id' => $itemId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'staff_id' => $staffId,
                    'remarks' => "Transfer to Staff {$staffId} (Transfer #{$transferCode})",
                    'created_by' => $userId,
                ]);
            }

            return $transfer;
        });
    }

    /**
     * Record service completion and deduct technician staff stock
     */
    public function recordService(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $stbId = $data['set_top_box_id'];
            $staffId = $data['staff_id'];
            $actionType = $data['action_type'] ?? 'complete'; // 'complete' or 'flash'
            $serviceDate = $data['service_date'];
            $remarks = $data['remarks'] ?? null;
            $items = $data['items'] ?? []; // array of ['item_id' => x, 'quantity' => y, 'unit_price' => z]

            // Filter out empty items
            $validItems = [];
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (!empty($item['item_id']) && isset($item['quantity']) && (float)$item['quantity'] > 0) {
                        $validItems[] = $item;
                    }
                }
            }

            // Validate staff stock availability for all valid items
            foreach ($validItems as $itemData) {
                $itemId = $itemData['item_id'];
                $reqQty = (float) $itemData['quantity'];

                $staffStock = StaffStock::where('staff_id', $staffId)
                    ->where('item_id', $itemId)
                    ->lockForUpdate()
                    ->first();

                $available = $staffStock ? (float)$staffStock->quantity : 0;
                if ($available < $reqQty) {
                    $itemModel = Item::find($itemId);
                    $itemName = $itemModel ? $itemModel->item_name : "Item #{$itemId}";
                    throw new Exception("Technician stock insufficient for '{$itemName}'. Available: {$available}, Requested: {$reqQty}.");
                }
            }

            // Calculate total cost & create service header
            $serviceCode = 'SRV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $totalCost = 0;

            foreach ($validItems as $itemData) {
                $unitPrice = (float)($itemData['unit_price'] ?? 0);
                $reqQty = (float) $itemData['quantity'];
                $totalCost += ($unitPrice * $reqQty);
            }

            $remarksPrefix = match ($actionType) {
                'flash' => '[FLASH - DEAD BOX] ',
                'software_issue' => '[SOFTWARE ISSUE - DEAD BOX] ',
                'send_to_pud' => '[SENT TO PUD SERVICE CENTER] ',
                default => '',
            };

            $serviceTx = ServiceTransaction::create([
                'service_code' => $serviceCode,
                'service_date' => $serviceDate,
                'set_top_box_id' => $stbId,
                'staff_id' => $staffId,
                'total_cost' => $totalCost,
                'remarks' => $remarksPrefix . $remarks,
                'created_by' => $userId,
            ]);

            // Deduct staff stock and record service items if any used
            foreach ($validItems as $itemData) {
                $itemId = $itemData['item_id'];
                $reqQty = (float) $itemData['quantity'];
                $unitPrice = (float) ($itemData['unit_price'] ?? 0);

                StaffStock::where('staff_id', $staffId)
                    ->where('item_id', $itemId)
                    ->decrement('quantity', $reqQty);

                ServiceItem::create([
                    'service_transaction_id' => $serviceTx->id,
                    'item_id' => $itemId,
                    'quantity' => $reqQty,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $reqQty,
                ]);
            }

            // Update Set Top Box status based on action_type
            $stb = SetTopBox::find($stbId);
            if ($stb) {
                if (in_array($actionType, ['flash', 'software_issue', 'send_to_pud'])) {
                    $stb->stb_status = $actionType;
                } else {
                    $stb->stb_status = 'service_done';
                }
                $stb->save();
            }

            return $serviceTx;
        });
    }

    /**
     * Record batch service entry (Voucher Style)
     */
    public function recordBatchService(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $serviceDate = $data['service_date'];
            $staffId = $data['staff_id'];
            $batchRemarks = $data['remarks'] ?? null;
            $boxes = $data['boxes'];

            $createdServices = [];

            foreach ($boxes as $boxData) {
                $stbId = $boxData['set_top_box_id'];
                $actionType = $boxData['action_type'] ?? 'complete'; // 'complete', 'flash', 'software_issue', 'send_to_pud'
                $boxRemarks = $boxData['remarks'] ?? '';
                $items = $boxData['items'] ?? [];

                // Filter valid spare items
                $validItems = [];
                if (!empty($items)) {
                    foreach ($items as $item) {
                        if (!empty($item['item_id']) && isset($item['quantity']) && (float)$item['quantity'] > 0) {
                            $validItems[] = $item;
                        }
                    }
                }

                // Validate technician stock availability
                foreach ($validItems as $itemData) {
                    $itemId = $itemData['item_id'];
                    $reqQty = (float) $itemData['quantity'];

                    $staffStock = StaffStock::where('staff_id', $staffId)
                        ->where('item_id', $itemId)
                        ->lockForUpdate()
                        ->first();

                    $available = $staffStock ? (float)$staffStock->quantity : 0;
                    if ($available < $reqQty) {
                        $itemModel = Item::find($itemId);
                        $itemName = $itemModel ? $itemModel->item_name : "Item #{$itemId}";
                        throw new Exception("Technician stock insufficient for '{$itemName}'. Available: {$available}, Requested: {$reqQty}.");
                    }
                }

                // Calculate total cost & create service header
                $serviceCode = 'SRV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                $totalCost = 0;

                foreach ($validItems as $itemData) {
                    $unitPrice = (float)($itemData['unit_price'] ?? 0);
                    $reqQty = (float) $itemData['quantity'];
                    $totalCost += ($unitPrice * $reqQty);
                }

                $combinedRemarks = trim($batchRemarks . ' ' . $boxRemarks);
                $remarksPrefix = match ($actionType) {
                    'flash' => '[FLASH - DEAD BOX] ',
                    'software_issue' => '[SOFTWARE ISSUE - DEAD BOX] ',
                    'send_to_pud' => '[SENT TO PUD SERVICE CENTER] ',
                    default => '',
                };

                $serviceTx = ServiceTransaction::create([
                    'service_code' => $serviceCode,
                    'service_date' => $serviceDate,
                    'set_top_box_id' => $stbId,
                    'staff_id' => $staffId,
                    'total_cost' => $totalCost,
                    'remarks' => $remarksPrefix . $combinedRemarks,
                    'created_by' => $userId,
                ]);

                // Deduct staff stock and record service items if any used
                foreach ($validItems as $itemData) {
                    $itemId = $itemData['item_id'];
                    $reqQty = (float) $itemData['quantity'];
                    $unitPrice = (float) ($itemData['unit_price'] ?? 0);

                    StaffStock::where('staff_id', $staffId)
                        ->where('item_id', $itemId)
                        ->decrement('quantity', $reqQty);

                    ServiceItem::create([
                        'service_transaction_id' => $serviceTx->id,
                        'item_id' => $itemId,
                        'quantity' => $reqQty,
                        'unit_price' => $unitPrice,
                        'total_price' => $unitPrice * $reqQty,
                    ]);
                }

                // Update Set Top Box status based on action_type
                $stb = SetTopBox::find($stbId);
                if ($stb) {
                    if (in_array($actionType, ['flash', 'software_issue', 'send_to_pud'])) {
                        $stb->stb_status = $actionType;
                    } else {
                        $stb->stb_status = 'service_done';
                    }
                    $stb->save();
                }

                $createdServices[] = $serviceTx;
            }

            return $createdServices;
        });
    }
}
