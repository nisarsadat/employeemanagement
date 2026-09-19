<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Style\Alignment;

trait LocalizesExcelExport
{
    protected function normalizeLocale(?string $locale): string
    {
        $locale = strtolower(trim((string) $locale));

        return match ($locale) {
            'en', 'eng', 'english' => 'en',
            'ps', 'pashto', 'pa' => 'ps',
            default => 'fa',
        };
    }

    protected function isRtl(): bool
    {
        return $this->locale !== 'en';
    }

    protected function headingFor(string $key, string $fallback): string
    {
        $label = $this->columnLabels[$key] ?? null;

        if (is_string($label) && trim($label) !== '') {
            return trim($label);
        }

        $defaults = $this->defaultHeadingsByLocale();

        return $defaults[$this->locale][$key]
            ?? $defaults['fa'][$key]
            ?? $fallback;
    }

    protected function horizontalAlignment(): string
    {
        return $this->isRtl()
            ? Alignment::HORIZONTAL_RIGHT
            : Alignment::HORIZONTAL_LEFT;
    }
}
