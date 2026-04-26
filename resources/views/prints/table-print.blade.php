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

        .page { width: 100%; }

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

        .print-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid #dbe4ea;
            background: #fff;
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

        tbody tr:nth-child(odd) { background: #f7f8f9; }
        tbody tr:nth-child(even) { background: #edf0f2; }

        tbody td {
            text-align: center;
            vertical-align: middle;
            padding: 10px 6px;
            border-bottom: 1px solid #dde4e8;
            word-break: break-word;
            color: #374151;
        }

        .index-col { width: 52px; }

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

        .relation-btn-teal,
        .relation-btn-purple,
        .relation-btn-blue {
            display: inline-block;
            min-width: 44px;
            padding: 7px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11px;
            line-height: 1;
            text-align: center;
        }

        .relation-btn-teal {
            background: #d9f0ec;
            color: #0f766e;
        }

        .relation-btn-purple {
            background: #ede3ff;
            color: #6d28d9;
        }

        .relation-btn-blue {
            background: #dbeafe;
            color: #2563eb;
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

        .status-active { background: #22c55e; }
        .status-inactive { background: #ef4444; }
    </style>
</head>

<body onload="window.print()">

@php
    $imageColumns = ['picture', 'image', 'photo', 'profile_picture'];

    $selectedImageColumn = collect($selectedColumns)
        ->first(fn ($col) => in_array($col, $imageColumns, true));
@endphp

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

                    @if ($selectedImageColumn)
                        <th>{{ $columnLabels[$selectedImageColumn] ?? $selectedImageColumn }}</th>
                    @endif

                    @foreach ($selectedColumns as $column)
                        @continue(in_array($column, $imageColumns, true))
                        <th>{{ $columnLabels[$column] ?? $column }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse ($rows as $index => $row)
                    @php
                        $imageValue = $selectedImageColumn
                            ? data_get($row, $selectedImageColumn)
                            : null;
                    @endphp

                    <tr>
                        <td class="index-col">
                            <span class="index-pill">{{ $index + 1 }}</span>
                        </td>

                        @if ($selectedImageColumn)
                            <td>
                                @if (!empty($imageValue))
                                    <img
                                        src="{{
                                            str_starts_with($imageValue, 'http://') ||
                                            str_starts_with($imageValue, 'https://') ||
                                            str_starts_with($imageValue, 'data:') ||
                                            str_starts_with($imageValue, 'blob:')
                                                ? $imageValue
                                                : asset(
                                                    str_starts_with($imageValue, 'storage/')
                                                        ? $imageValue
                                                        : 'storage/' . ltrim($imageValue, '/')
                                                )
                                        }}"
                                        alt="انځور نشته"
                                        class="print-image"
                                    >
                                @else
                                    -
                                @endif
                            </td>
                        @endif

                        @foreach ($selectedColumns as $column)
                            @continue(in_array($column, $imageColumns, true))

                            @php
                                $value = data_get($row, $column, '-');

                               $dateColumns = [
                                        'event_date',
                                        'resource_saved_date',
                                        'information_saved_date',
                                        'captive_date',
                                        'saved_date',
                                        'office_outgoing_date',
                                        'office_incoming_date',
                                        'reyasat_outgoing_date',
                                        'reyasat_incomming_date',
                                        'loy_reyasat_outgoing_date',
                                        'answer_date',
                                        'outgoing_incoming_date',

                                        'join_date',
                                        'left_date',
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
                                    'resource_id',
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

                                $tealRelationColumns = [
                                    'resources_count',
                                    'resources_active_count',
                                    'resources_inactive_count',
                                ];

                                $purpleRelationColumns = [
                                    'events_count',
                                ];

                                $blueRelationColumns = [
                                    'informations_count',
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

                                @elseif (in_array($column, $tealRelationColumns, true))
                                    <span class="relation-btn-teal">{{ $value ?: 0 }}</span>

                                @elseif (in_array($column, $purpleRelationColumns, true))
                                    <span class="relation-btn-purple">{{ $value ?: 0 }}</span>

                                @elseif (in_array($column, $blueRelationColumns, true))
                                    <span class="relation-btn-blue">{{ $value ?: 0 }}</span>

                                @elseif (in_array($column, $pinkRelationColumns, true) && (int) $value > 0)
                                    <span class="relation-btn-pink">{{ $value }}</span>

                                @elseif (in_array($column, $warningRelationColumns, true))
                                    <span class="relation-btn-warning">{{ $value ?: '-' }}</span>

                                @elseif (in_array($column, $redNumberColumns, true))
                                    <span class="num-red">{{ $value ?: 0 }}</span>

                                @elseif (in_array($column, $darkNumberColumns, true))
                                    <span class="num-dark">{{ $value ?: 0 }}</span>

                                
                                    @elseif ($column === 'activity')
    @php
        $activityValue = trim((string) $value);

        $normalizedActivity = str_replace(['ي', 'ى', 'ك'], ['ی', 'ی', 'ک'], $activityValue);
        $normalizedActivity = preg_replace('/[\s\x{200c}\x{200d}]+/u', '', $normalizedActivity);

        $activityLower = strtolower($normalizedActivity);

        $isInactive = in_array($normalizedActivity, [
            'غیرفعال',
            'غيرفعال',
            'نافعال',
        ], true) || in_array($activityLower, [
            'inactive',
            'disabled',
            'false',
            '0',
        ], true);

        $isActive = !$isInactive && (
            $normalizedActivity === 'فعال' ||
            in_array($activityLower, [
                'active',
                'enabled',
                'true',
                '1',
            ], true)
        );
    @endphp

    @if ($isInactive)
        <span class="status-dot status-inactive"></span>
    @elseif ($isActive)
        <span class="status-dot status-active"></span>
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