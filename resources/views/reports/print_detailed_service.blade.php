<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Performance Report - {{ $monthTitle }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
            background: #fff;
            padding: 30px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 20px; }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 12px;
        }
        .month-header-label {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            margin-bottom: 4px;
        }

        table.grid-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 30px;
        }
        table.grid-table th, table.grid-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            font-size: 13px;
        }
        table.grid-table th {
            font-weight: bold;
            text-transform: uppercase;
            background-color: #ffffff;
        }
        table.grid-table td.date-col {
            font-weight: bold;
            text-align: center;
            background-color: #ffffff;
            width: 120px;
        }

        .summary-row td {
            font-weight: bold;
            font-size: 14px;
            background-color: #ffffff;
        }

        .auxiliary-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 25px;
        }

        table.aux-table {
            border-collapse: collapse;
            border: 2px solid #000;
            font-size: 12px;
        }
        table.aux-table th, table.aux-table td {
            border: 1px solid #000;
            padding: 6px 14px;
            text-align: center;
        }
        table.aux-table th {
            font-weight: bold;
            text-transform: uppercase;
            background-color: #ffffff;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        @media print {
            body {
                padding: 10px;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print mb-4" style="text-align: right;">
        <button onclick="window.print()" class="btn-print">
            🖨️ Print Performance Report
        </button>
    </div>

    <div class="report-header">
        <div style="font-size: 18px; font-weight: bold; text-transform: uppercase;">
            Kerala Vision Service Center - Service Performance Matrix
        </div>
        <div class="month-header-label">
            {{ $monthTitle }}
        </div>
    </div>

    <!-- Main Technician Performance Matrix Grid Table -->
    <table class="grid-table">
        <thead>
            <tr>
                <th rowspan="2" style="vertical-align: middle;">DATE</th>
                @foreach($technicians as $tech)
                <th colspan="3" style="font-size: 14px;">{{ $tech->name }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($technicians as $tech)
                <th style="width: 70px;">RE PAIRED</th>
                <th style="width: 70px;">FLASH BOX</th>
                <th style="width: 70px;">SW ISSUE</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($datesList as $dStr)
            <tr>
                <td class="date-col">{{ $dStr }}</td>
                @foreach($technicians as $tech)
                @php
                    $rep = $matrix[$dStr][$tech->id]['repaired'] ?? 0;
                    $fls = $matrix[$dStr][$tech->id]['flash'] ?? 0;
                    $swi = $matrix[$dStr][$tech->id]['software_issue'] ?? 0;
                @endphp
                <td>{{ $rep > 0 ? $rep : '' }}</td>
                <td>{{ $fls > 0 ? $fls : '' }}</td>
                <td>{{ $swi > 0 ? $swi : '' }}</td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ 1 + (count($technicians) * 3) }}" style="padding: 20px;">
                    No service transactions recorded in this selected period.
                </td>
            </tr>
            @endforelse

            <!-- Row 1: TOTAL Sum -->
            <tr class="summary-row" style="border-top: 2px solid #000;">
                <td class="fw-bold">TOTAL</td>
                @foreach($technicians as $tech)
                <td>{{ $totals[$tech->id]['repaired'] ?? 0 }}</td>
                <td>{{ $totals[$tech->id]['flash'] ?? 0 }}</td>
                <td>{{ $totals[$tech->id]['software_issue'] ?? 0 }}</td>
                @endforeach
            </tr>

            <!-- Row 2: RE SERVICE Count -->
            <tr class="summary-row">
                <td class="fw-bold">RE SERVICE</td>
                @foreach($technicians as $tech)
                <td>{{ ($totals[$tech->id]['reservice'] ?? 0) > 0 ? $totals[$tech->id]['reservice'] : '' }}</td>
                <td></td>
                <td></td>
                @endforeach
            </tr>

            <!-- Row 3: NET TOTAL (Repaired Minus Reservice) -->
            <tr class="summary-row" style="border-top: 2px solid #000;">
                <td class="fw-bold">TOTAL</td>
                @foreach($technicians as $tech)
                @php
                    $netRepared = ($totals[$tech->id]['repaired'] ?? 0) - ($totals[$tech->id]['reservice'] ?? 0);
                @endphp
                <td>{{ $netRepared }}</td>
                <td>{{ $totals[$tech->id]['flash'] ?? 0 }}</td>
                <td>{{ $totals[$tech->id]['software_issue'] ?? 0 }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <!-- Auxiliary Summary Tables at Bottom -->
    <div class="auxiliary-container">
        <!-- Left Box: Net Repaired Total Summary -->
        <table class="aux-table" style="width: 320px;">
            <thead>
                <tr>
                    <th colspan="2" style="font-size: 13px;">SUMMARY OVERVIEW</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold" style="text-align: left; padding: 8px 12px;">NET REPAIRED STBS</td>
                    <td class="fw-bold" style="font-size: 14px; padding: 8px 12px;">
                        @php
                            $grandNetRepared = 0;
                            foreach($technicians as $tech) {
                                $grandNetRepared += (($totals[$tech->id]['repaired'] ?? 0) - ($totals[$tech->id]['reservice'] ?? 0));
                            }
                        @endphp
                        {{ $grandNetRepared }}
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold" style="text-align: left; padding: 8px 12px;">TOTAL FLASH / PUD STBS</td>
                    <td class="fw-bold" style="font-size: 14px; padding: 8px 12px;">
                        @php
                            $grandFlash = 0;
                            foreach($technicians as $tech) {
                                $grandFlash += ($totals[$tech->id]['flash'] ?? 0);
                            }
                        @endphp
                        {{ $grandFlash }}
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold" style="text-align: left; padding: 8px 12px;">TOTAL SOFTWARE ISSUE STBS</td>
                    <td class="fw-bold" style="font-size: 14px; padding: 8px 12px;">
                        @php
                            $grandSoftware = 0;
                            foreach($technicians as $tech) {
                                $grandSoftware += ($totals[$tech->id]['software_issue'] ?? 0);
                            }
                        @endphp
                        {{ $grandSoftware }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Right Box: Other Services Summary -->
        <table class="aux-table" style="width: 320px;">
            <thead>
                <tr>
                    <th>TECHNICIAN</th>
                    <th>OTHER SERVICE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($technicians as $tech)
                <tr>
                    <td class="fw-bold">{{ strtoupper($tech->name) }}</td>
                    <td>SERVICE DONE</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
