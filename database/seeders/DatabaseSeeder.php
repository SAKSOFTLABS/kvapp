<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Staff;
use App\Models\Item;
use App\Models\BoxModel;
use App\Models\Operator;
use App\Models\SetTopBox;
use App\Models\MainStock;
use App\Models\StaffStock;
use App\Models\StockTransaction;
use App\Models\StockTransfer;
use App\Models\ServiceTransaction;
use App\Models\ServiceItem;
use App\Models\QcCheck;
use App\Models\Setting;
use App\Models\ActivityLog;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Settings
        Setting::set('company_name', 'Kerala Vision Service Center');
        Setting::set('company_phone', '+91 98470 12345');
        Setting::set('company_email', 'support@keralavision.in');
        Setting::set('company_address', 'KV Tower, Main Road, Kochi, Kerala - 682011');
        Setting::set('currency_symbol', '₹');
        Setting::set('low_stock_threshold', '10');

        // 2. Admin User
        $admin = User::create([
            'name' => 'System Administrator',
            'username' => 'admin',
            'email' => 'admin@keralavision.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // 2.5 QC Inspector User
        $qcUser = User::create([
            'name' => 'QC Lead Inspector',
            'username' => 'qc_inspector',
            'email' => 'qc@keralavision.com',
            'password' => Hash::make('qc123'),
            'role' => 'qc',
            'status' => 'active',
        ]);

        // 3. Staff Members
        $staffMembers = [
            [
                'name' => 'Rajesh Kumar',
                'designation' => 'Senior Service Technician',
                'mobile' => '9846011223',
                'address' => 'Palarivattom, Kochi',
                'username' => 'rajesh',
            ],
            [
                'name' => 'Anoop V',
                'designation' => 'Field Support Engineer',
                'mobile' => '9846022334',
                'address' => 'Kaloor, Kochi',
                'username' => 'anoop',
            ],
            [
                'name' => 'Divya Nair',
                'designation' => 'Customer Care Technician',
                'mobile' => '9846033445',
                'address' => 'Edappally, Kochi',
                'username' => 'divya',
            ],
        ];

        $createdStaff = [];
        foreach ($staffMembers as $s) {
            $staff = Staff::create([
                'name' => $s['name'],
                'designation' => $s['designation'],
                'mobile' => $s['mobile'],
                'address' => $s['address'],
                'username' => $s['username'],
                'status' => 'active',
            ]);

            // Create Staff user account
            User::create([
                'name' => $s['name'],
                'username' => $s['username'],
                'email' => strtolower($s['username']) . '@keralavision.com',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'staff_id' => $staff->id,
                'status' => 'active',
            ]);

            $createdStaff[] = $staff;
        }

        // 3.5 Cable Operators (LCOs / Franchises)
        $operatorsData = [
            [
                'operator_name' => 'Kaloor Cable Vision',
                'operator_code' => 'LCO-KLR-001',
                'contact_person' => 'Suresh Kumar',
                'mobile' => '9847012345',
                'location' => 'Kaloor Junction, Ernakulam',
                'status' => 'active',
            ],
            [
                'operator_name' => 'Ernakulam Digital Network',
                'operator_code' => 'LCO-EKM-002',
                'contact_person' => 'Mathew Joseph',
                'mobile' => '9847023456',
                'location' => 'MG Road, Kochi',
                'status' => 'active',
            ],
            [
                'operator_name' => 'Cochin Cable Communications',
                'operator_code' => 'LCO-COCH-003',
                'contact_person' => 'Firoz Khan',
                'mobile' => '9847034567',
                'location' => 'Fort Kochi',
                'status' => 'active',
            ],
        ];

        $createdOperators = [];
        foreach ($operatorsData as $opd) {
            $createdOperators[$opd['operator_name']] = Operator::create($opd);
        }

        // 4. Items & Main Stock
        $itemsData = [
            [
                'item_name' => 'HDMI Cable 1.5m Gold Plated',
                'item_code' => 'ITM-HDMI-01',
                'opening_stock' => 150,
                'purchase_price' => 120.00,
                'sales_price' => 250.00,
                'description' => 'High speed 4K HDMI cable 1.5 meters',
            ],
            [
                'item_name' => '12V 1.5A Power Adapter',
                'item_code' => 'ITM-PWR-02',
                'opening_stock' => 200,
                'purchase_price' => 180.00,
                'sales_price' => 350.00,
                'description' => 'Standard set top box power supply unit',
            ],
            [
                'item_name' => 'KV Universal Remote Control',
                'item_code' => 'ITM-RMT-03',
                'opening_stock' => 300,
                'purchase_price' => 90.00,
                'sales_price' => 200.00,
                'description' => 'Kerala Vision smart universal remote',
            ],
            [
                'item_name' => 'RG6 Coaxial Cable (per meter)',
                'item_code' => 'ITM-CBL-04',
                'opening_stock' => 1000,
                'purchase_price' => 12.00,
                'sales_price' => 25.00,
                'description' => 'Heavy duty shielded coaxial cable',
            ],
            [
                'item_name' => 'Single Output Ku-Band LNB',
                'item_code' => 'ITM-LNB-05',
                'opening_stock' => 80,
                'purchase_price' => 250.00,
                'sales_price' => 450.00,
                'description' => 'Universal single output LNB',
            ],
            [
                'item_name' => '2-Way Signal Splitter 5-2400MHz',
                'item_code' => 'ITM-SPL-06',
                'opening_stock' => 120,
                'purchase_price' => 45.00,
                'sales_price' => 100.00,
                'description' => 'High frequency RF signal splitter',
            ],
            [
                'item_name' => 'AV Composite Cable 3-RCA',
                'item_code' => 'ITM-RCA-07',
                'opening_stock' => 90,
                'purchase_price' => 40.00,
                'sales_price' => 90.00,
                'description' => 'Standard 3.5mm to RCA audio video cable',
            ],
            [
                'item_name' => 'Smartcard Chip Module',
                'item_code' => 'ITM-SCM-08',
                'opening_stock' => 50,
                'purchase_price' => 300.00,
                'sales_price' => 600.00,
                'description' => 'CAS encryption conditional access smart card',
            ],
        ];

        $createdItems = [];
        foreach ($itemsData as $idat) {
            $item = Item::create([
                'item_name' => $idat['item_name'],
                'item_code' => $idat['item_code'],
                'opening_stock' => $idat['opening_stock'],
                'purchase_price' => $idat['purchase_price'],
                'sales_price' => $idat['sales_price'],
                'description' => $idat['description'],
                'status' => 'active',
            ]);

            // Main stock ledger initialization
            MainStock::create([
                'item_id' => $item->id,
                'quantity' => $idat['opening_stock'],
            ]);

            // Transaction log for opening stock
            StockTransaction::create([
                'transaction_type' => 'opening',
                'date' => now()->subDays(30)->toDateString(),
                'item_id' => $item->id,
                'quantity' => $idat['opening_stock'],
                'unit_price' => $idat['purchase_price'],
                'supplier' => 'Initial Opening Balance',
                'remarks' => 'Opening stock initialized',
                'created_by' => $admin->id,
            ]);

            $createdItems[] = $item;
        }

        // 4.5 Box Models (STB Item Groups)
        $modelsData = [
            ['model_name' => 'KV HD Smart Box 4K Model A1', 'model_code' => 'MDL-KV-4KA1', 'description' => '4K Android Smart Hybrid STB', 'status' => 'active'],
            ['model_name' => 'KV HEVC Hybrid STB Model H2', 'model_code' => 'MDL-KV-H2', 'description' => 'HEVC High Definition Box', 'status' => 'active'],
            ['model_name' => 'KV Standard Digital Box S10', 'model_code' => 'MDL-KV-S10', 'description' => 'Standard Digital DVB-C Box', 'status' => 'active'],
        ];

        $createdModels = [];
        foreach ($modelsData as $md) {
            $createdModels[$md['model_name']] = BoxModel::create($md);
        }

        // 5. Set Top Boxes (with Cable Operators & Status Lifecycles)
        $op1 = $createdOperators['Kaloor Cable Vision'];
        $op2 = $createdOperators['Ernakulam Digital Network'];
        $op3 = $createdOperators['Cochin Cable Communications'];

        $stbData = [
            [
                'model_name' => 'KV HD Smart Box 4K Model A1',
                'operator_id' => $op1->id,
                'barcode_number' => '890123456701',
                'stb_status' => 'service_done',
                'remarks' => 'Operator: Kaloor Cable. Customer: Sreekumar P',
            ],
            [
                'model_name' => 'KV HD Smart Box 4K Model A1',
                'operator_id' => $op1->id,
                'barcode_number' => '890123456702',
                'stb_status' => 'complaint',
                'remarks' => 'Operator: Kaloor Cable. Issue: No Display',
            ],
            [
                'model_name' => 'KV HEVC Hybrid STB Model H2',
                'operator_id' => $op2->id,
                'barcode_number' => '890123456703',
                'stb_status' => 'tested_ok',
                'remarks' => 'Operator: Ernakulam Digital. QC Tested Pass',
            ],
            [
                'model_name' => 'KV HEVC Hybrid STB Model H2',
                'operator_id' => $op2->id,
                'barcode_number' => '890123456704',
                'stb_status' => 'flash',
                'remarks' => 'Operator: Ernakulam Digital. Main IC burnt out (Dead)',
            ],
            [
                'model_name' => 'KV Standard Digital Box S10',
                'operator_id' => $op3->id,
                'barcode_number' => '890123456705',
                'stb_status' => 'send_to_pk',
                'remarks' => 'Operator: Cochin Cable. Dispatched to PK Factory',
            ],
            [
                'model_name' => 'KV Standard Digital Box S10',
                'operator_id' => $op3->id,
                'barcode_number' => '890123456706',
                'stb_status' => 'complaint',
                'remarks' => 'Operator: Cochin Cable. Red Light Blinking',
            ],
        ];

        $createdBoxes = [];
        foreach ($stbData as $b) {
            $bm = $createdModels[$b['model_name']] ?? null;
            $box = SetTopBox::create([
                'box_model_id' => $bm ? $bm->id : null,
                'operator_id' => $b['operator_id'],
                'box_name' => $b['model_name'],
                'barcode_number' => $b['barcode_number'],
                'stb_status' => $b['stb_status'],
                'remarks' => $b['remarks'],
                'status' => 'active',
            ]);
            $createdBoxes[] = $box;
        }

        // 6. Transfers to Technicians
        $tech1 = $createdStaff[0];
        $tech2 = $createdStaff[1];

        $t1_item1 = $createdItems[0]; // HDMI
        $t1_item2 = $createdItems[1]; // Power Adapter
        $t1_item3 = $createdItems[2]; // Remote

        MainStock::where('item_id', $t1_item1->id)->decrement('quantity', 15);
        StaffStock::create(['staff_id' => $tech1->id, 'item_id' => $t1_item1->id, 'quantity' => 15]);
        StockTransfer::create([
            'transfer_code' => 'TRF-20260701-001',
            'transfer_date' => now()->subDays(10)->toDateString(),
            'staff_id' => $tech1->id,
            'item_id' => $t1_item1->id,
            'quantity' => 15,
            'remarks' => 'Field toolkit replenishment',
            'created_by' => $admin->id,
        ]);

        MainStock::where('item_id', $t1_item2->id)->decrement('quantity', 20);
        StaffStock::create(['staff_id' => $tech1->id, 'item_id' => $t1_item2->id, 'quantity' => 20]);
        StockTransfer::create([
            'transfer_code' => 'TRF-20260701-002',
            'transfer_date' => now()->subDays(10)->toDateString(),
            'staff_id' => $tech1->id,
            'item_id' => $t1_item2->id,
            'quantity' => 20,
            'remarks' => 'Power adapter batch issue',
            'created_by' => $admin->id,
        ]);

        MainStock::where('item_id', $t1_item3->id)->decrement('quantity', 25);
        StaffStock::create(['staff_id' => $tech1->id, 'item_id' => $t1_item3->id, 'quantity' => 25]);
        StockTransfer::create([
            'transfer_code' => 'TRF-20260701-003',
            'transfer_date' => now()->subDays(10)->toDateString(),
            'staff_id' => $tech1->id,
            'item_id' => $t1_item3->id,
            'quantity' => 25,
            'remarks' => 'Remote control stock issue',
            'created_by' => $admin->id,
        ]);

        // 7. Service & QC History Record
        $box1 = $createdBoxes[0];
        $st1 = ServiceTransaction::create([
            'service_code' => 'SRV-20260720-001',
            'service_date' => now()->subDays(3)->toDateString(),
            'set_top_box_id' => $box1->id,
            'staff_id' => $tech1->id,
            'total_cost' => 550.00,
            'remarks' => 'Replaced power supply unit and provided remote control.',
            'created_by' => $admin->id,
        ]);

        ServiceItem::create([
            'service_transaction_id' => $st1->id,
            'item_id' => $t1_item2->id,
            'quantity' => 1,
            'unit_price' => 350.00,
            'total_price' => 350.00,
        ]);
        StaffStock::where('staff_id', $tech1->id)->where('item_id', $t1_item2->id)->decrement('quantity', 1);

        ServiceItem::create([
            'service_transaction_id' => $st1->id,
            'item_id' => $t1_item3->id,
            'quantity' => 1,
            'unit_price' => 200.00,
            'total_price' => 200.00,
        ]);
        StaffStock::where('staff_id', $tech1->id)->where('item_id', $t1_item3->id)->decrement('quantity', 1);

        // QC Check Record for Box #3 (tested_ok)
        $box3 = $createdBoxes[2];
        QcCheck::create([
            'set_top_box_id' => $box3->id,
            'qc_user_id' => $qcUser->id,
            'qc_status' => 'tested_ok',
            'qc_date' => now()->subDays(1)->toDateString(),
            'remarks' => 'Signal quality 100%, Audio/Video clear.',
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'SYSTEM_INIT',
            'description' => 'Initialized system seed data with Cable Operators, QC inspector, and STB status state machine',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
