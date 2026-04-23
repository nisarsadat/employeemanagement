<!DOCTYPE html>
<html lang="{{ $locale === 'ps' ? 'ps' : ($locale === 'en' ? 'en' : 'fa') }}"
      dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        @font-face {
            font-family: 'Amiri';
            font-weight: 400;
            src: url('/fonts/Amiri-Regular.ttf') format('truetype');
        }

        @font-face {
            font-family: 'Amiri';
            font-weight: 700;
            src: url('/fonts/Amiri-Bold.ttf') format('truetype');
        }

        body {
            margin: 0;
            padding: 0;
            direction: {{ $locale === 'en' ? 'ltr' : 'rtl' }};
            font-family: 'Amiri', serif;
            background: #ffffff;
            color: #334155;
            font-size: 11px;
        }

        .page {
            width: 100%;
        }

        .header {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #d9e3ea;
        }

        .title {
            font-size: 20px;
            font-weight: 700;
            font-family: 'Amiri', serif;
        }

        .sub {
            font-size: 10px;
            color: #64748b;
        }

        .footer {
            margin-top: 12px;
            font-size: 10px;
            color: #64748b;
        }

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

        .relation-btn-orange,
        .relation-btn-cyan,
        .relation-btn-pink,
        .relation-btn-warning {
            display: inline-block;
            min-width: 64px;
            padding: 7px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11px;
            line-height: 1;
            text-align: center;
        }

        .relation-btn-orange {
            background: #f4e7d7;
            color: #f97316;
        }

        .relation-btn-cyan {
            background: #dceef1;
            color: #0891b2;
        }

        .relation-btn-pink {
            background: #f7dce7;
            color: #e11d48;
        }

        .relation-btn-warning {
            background: #fff4d6;
            color: #d97706;
        }

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
    </style>
</head>

<body onload="window.print()">

<div class="page">
    <div class="header">
        <div class="title">{{ $title }}</div>

        <div class="sub">
            {{ $printLabels['record_count'] ?? 'Record count' }} : {{ $rows->count() }}
        </div>
    </div>

    <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th class="index-col">{{ $printLabels['index'] ?? '#' }}</th>

                    @foreach ($selectedColumns as $column)
                        <th>{{ $columnLabels[$column] ?? $column }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse ($rows as $index => $row)
                    <tr>
                        <td class="index-col">
                            <span class="index-pill">{{ $index + 1 }}</span>
                        </td>

                        @foreach ($selectedColumns as $column)
                            @php
                                $value = data_get($row, $column, '-');

                                $dateColumns = [
                                    'event_date',
                                    'resource_saved_date',
                                    'information_saved_date',
                                    'created_at',
                                    'updated_at',
                                    'date',
                                    'from_date',
                                    'to_date',
                                    'debut',
                                ];

                                $isDateColumn = in_array($column, $dateColumns, true);

                                if ($isDateColumn && !empty($value)) {
                                    $stringValue = trim((string) $value);

                                    if (preg_match('/(\d{4}[\/-]\d{2}[\/-]\d{2})/', $stringValue, $matches)) {
                                        $value = str_replace('-', '/', $matches[1]);
                                    } else {
                                        $value = preg_replace('/\s*\d{2}:\d{2}:\d{2}$/', '', $stringValue);
                                        $value = trim((string) $value);
                                    }
                                }

                                $orangeRelationColumns = [
                                    'code_number',
                                    'resedint_code',
                                    'resource_display',
                                    'resource_code',
                                    'source_code',
                                ];

                                $cyanRelationColumns = [
                                    'office_employee_id',
                                    'office_employee_display',
                                    'office_employee_akhistonky_code',
                                    'employee_code',
                                    'receiver_employee_code',
                                    'office_employee_code',
                                    'office_employee_info_id',
                                    'office_employee_info_display',
                                ];

                                $pinkRelationColumns = [
                                    'khayrat_reports_count',
                                    'events_count',
                                    'informations_count',
                                    'fifty_six_formats_count',
                                ];

                                $warningRelationColumns = [
                                    'letry_format_name',
                                    'format_name',
                                ];

                                $redNumberColumns = [
                                    'events_count',
                                    'informations_count',
                                    'fifty_six_formats_count',
                                ];

                                $darkNumberColumns = [
                                    'payed_money',
                                    'trust_percentage',
                                    'finance_paid_amount',
                                ];
                            @endphp

                            <td>
                                @if (in_array($column, $orangeRelationColumns, true))
                                    <span class="relation-btn-orange">{{ $value ?: '-' }}</span>

                                @elseif (in_array($column, $cyanRelationColumns, true))
                                    <span class="relation-btn-cyan">{{ $value ?: '-' }}</span>

                                @elseif (in_array($column, $pinkRelationColumns, true) && (int) $value > 0)
                                    <span class="relation-btn-pink">{{ $value }}</span>

                                @elseif (in_array($column, $warningRelationColumns, true))
                                    <span class="relation-btn-warning">{{ $value ?: '-' }}</span>

                                @elseif (in_array($column, $redNumberColumns, true))
                                    <span class="num-red">{{ $value ?: 0 }}</span>

                                @elseif (in_array($column, $darkNumberColumns, true))
                                    <span class="num-dark">{{ $value ?: 0 }}</span>

                                @elseif ($column === 'activity')
                                    @if (trim((string) $value) === 'فعال')
                                        <span class="status-dot status-active"></span>
                                    @elseif (trim((string) $value) === 'غیر فعال')
                                        <span class="status-dot status-inactive"></span>
                                    @else
                                        -
                                    @endif

                                @elseif ($isDateColumn)
                                    <span dir="ltr" style="unicode-bidi: embed; white-space: nowrap;">
                                        {{ $value ?: '-' }}
                                    </span>

                                @else
                                    {{ $value ?: '' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($selectedColumns) + 1 }}">
                            {{ $printLabels['no_data'] ?? 'No data available for printing.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        {{ $printLabels['footer_note'] ?? '' }}
    </div>
</div>

</body>
</html>