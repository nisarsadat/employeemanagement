<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            margin: 0;
            padding: 0;
            direction: rtl;
            font-family: DejaVu Sans, sans-serif;
            background: #ffffff;
            color: #334155;
            font-size: 11px;
        }

        .page {
            width: 100%;
        }

        /* ===============================
           HEADER
        =============================== */

        .header {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #d9e3ea;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .sub {
            font-size: 10px;
            color: #64748b;
        }

        /* ===============================
           TABLE WRAPPER
        =============================== */

        .table-shell {
            border: 1px solid #76a9bb;
            border-radius: 8px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        /* ===============================
           HEADER ROW
        =============================== */

        thead th {
            background: #4b93a8;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            padding: 11px 6px;
            white-space: nowrap;
            border-left: 1px solid rgba(255,255,255,0.15);
        }

        thead th:first-child {
            border-left: none;
        }

        /* ===============================
           BODY ROWS
        =============================== */

        tbody tr:nth-child(odd) {
            background: #f7f8f9;
        }

        tbody tr:nth-child(even) {
            background: #edf0f2;
        }

        tbody td {
            text-align: center;
            vertical-align: middle;
            padding: 10px 6px;
            border-bottom: 1px solid #dde4e8;
            word-break: break-word;
            color: #374151;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* ===============================
           INDEX COLUMN
        =============================== */

        .index-col {
            width: 52px;
        }

        .index-pill {
            display: inline-block;
            min-width: 28px;
            padding: 7px 10px;
            border-radius: 999px;
            background: #d1d5db;
            color: #374151;
            font-weight: 700;
            font-size: 10px;
            line-height: 1;
        }

        /* ===============================
           RELATIONSHIP BUTTON STYLE
           (same feeling as app buttons)
        =============================== */

        .relation-btn-orange {
            display: inline-block;
            min-width: 64px;
            padding: 7px 12px;
            border-radius: 6px;
            background: #f4e7d7;
            color: #f97316;
            font-weight: 700;
            font-size: 11px;
            line-height: 1;
            text-align: center;
        }

        .relation-btn-cyan {
            display: inline-block;
            min-width: 64px;
            padding: 7px 12px;
            border-radius: 6px;
            background: #dceef1;
            color: #0891b2;
            font-weight: 700;
            font-size: 11px;
            line-height: 1;
            text-align: center;
        }

        .relation-btn-pink {
            display: inline-block;
            min-width: 64px;
            padding: 7px 12px;
            border-radius: 6px;
            background: #f7dce7;
            color: #e11d48;
            font-weight: 700;
            font-size: 11px;
            line-height: 1;
            text-align: center;
        }

        .relation-btn-warning {
            display: inline-block;
            min-width: 64px;
            padding: 7px 12px;
            border-radius: 6px;
            background: #fff4d6;
            color: #d97706;
            font-weight: 700;
            font-size: 11px;
            line-height: 1;
            text-align: center;
        }

        /* ===============================
           NUMBERS
        =============================== */

        .num-red {
            color: #ef4444;
            font-weight: 700;
            font-size: 11px;
        }

        .num-dark {
            color: #334155;
            font-weight: 600;
            font-size: 11px;
        }

        /* ===============================
           ACTIVITY DOT
        =============================== */

        .status-dot {
            display: inline-block;
            width: 11px;
            height: 11px;
            border-radius: 999px;
        }

        .status-active {
            background: #22c55e;
        }

        .status-inactive {
            background: #ef4444;
        }

        /* ===============================
           FOOTER
        =============================== */

        .footer {
            margin-top: 12px;
            font-size: 10px;
            color: #64748b;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body onload="window.print()">

<div class="page">

    <!-- ===============================
         HEADER
    ================================ -->

    <div class="header">
        <div class="title">{{ $title }}</div>

        <div class="sub">
            تعداد ریکاردها: {{ $rows->count() }}
        </div>
    </div>

    <!-- ===============================
         TABLE
    ================================ -->

    <div class="table-shell">
        <table>

            <!-- ===============================
                 TABLE HEAD
            ================================ -->

            <thead>
                <tr>
                    <th class="index-col">ردیف</th>

                    @foreach ($selectedColumns as $column)
                        <th>
                            {{ $columnLabels[$column] ?? $column }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <!-- ===============================
                 TABLE BODY
            ================================ -->

            <tbody>

            @forelse ($rows as $index => $row)

                <tr>

                    <!-- INDEX -->
                    <td class="index-col">
                        <span class="index-pill">
                            {{ $index + 1 }}
                        </span>
                    </td>

                    @foreach ($selectedColumns as $column)

                        @php
                            $value = data_get($row, $column, '-');

                            /*
                            |--------------------------------------------------------------------------
                            | Relationship style columns
                            |--------------------------------------------------------------------------
                            */

                            $orangeRelationColumns = [
                                'code_number',
                                'resedint_code',
                            ];

                            $cyanRelationColumns = [
                                'office_employee_id',
                            ];

                            $pinkRelationColumns = [
                                'khayrat_reports_count',
                            ];

                            $warningRelationColumns = [
                                'letry_format_name',
                            ];

                            /*
                            |--------------------------------------------------------------------------
                            | Number columns
                            |--------------------------------------------------------------------------
                            */

                            $redNumberColumns = [
                                'events_count',
                                'informations_count',
                                'fifty_six_formats_count',
                            ];

                            $darkNumberColumns = [
                                'payed_money',
                                'trust_percentage',
                            ];
                        @endphp

                        <td>

                            {{-- =========================
                                 ORANGE RELATION BUTTON
                            ========================== --}}
                            @if (in_array($column, $orangeRelationColumns, true))
                                <span class="relation-btn-orange">
                                    {{ $value ?: '-' }}
                                </span>

                            {{-- =========================
                                 CYAN RELATION BUTTON
                            ========================== --}}
                            @elseif (in_array($column, $cyanRelationColumns, true))
                                <span class="relation-btn-cyan">
                                    {{ $value ?: '-' }}
                                </span>

                            {{-- =========================
                                 PINK RELATION BUTTON
                            ========================== --}}
                            @elseif (
                                in_array($column, $pinkRelationColumns, true)
                                && (int) $value > 0
                            )
                                <span class="relation-btn-pink">
                                    {{ $value }}
                                </span>

                            {{-- =========================
                                 WARNING RELATION BUTTON
                            ========================== --}}
                            @elseif (in_array($column, $warningRelationColumns, true))
                                <span class="relation-btn-warning">
                                    {{ $value ?: '-' }}
                                </span>

                            {{-- =========================
                                 RED NUMBERS
                            ========================== --}}
                            @elseif (in_array($column, $redNumberColumns, true))
                                <span class="num-red">
                                    {{ $value ?: 0 }}
                                </span>

                            {{-- =========================
                                 DARK NUMBERS
                            ========================== --}}
                            @elseif (in_array($column, $darkNumberColumns, true))
                                <span class="num-dark">
                                    {{ $value ?: 0 }}
                                </span>

                            {{-- =========================
                                 ACTIVITY DOT
                            ========================== --}}
                            @elseif ($column === 'activity')

                                @if (trim((string) $value) === 'فعال')
                                    <span class="status-dot status-active"></span>

                                @elseif (trim((string) $value) === 'غیر فعال')
                                    <span class="status-dot status-inactive"></span>

                                @else
                                    -
                                @endif

                            {{-- =========================
                                 NORMAL TEXT
                            ========================== --}}
                            @else
                                {{ $value ?: '-' }}

                            @endif

                        </td>

                    @endforeach

                </tr>

            @empty

                <tr>
                    <td colspan="{{ count($selectedColumns) + 1 }}">
                        هیچ معلوماتی برای چاپ موجود نیست.
                    </td>
                </tr>

            @endforelse

            </tbody>
        </table>
    </div>

    <!-- ===============================
         FOOTER
    ================================ -->

    <div class="footer">
        این چاپ براساس فلترهای فعلی سیستم و ستون‌های انتخاب‌شده ساخته شده است.
    </div>

</div>

</body>
</html>