<?php

namespace App\Support;

class MapboxGlyphs
{
    /**
     * Build one Mapbox glyph stack protobuf from the local font folders.
     * MapLibre asks for "FontA,FontB" in a single URL. Those folders do not
     * exist, so the stacks are merged here and the name is set to the exact
     * string MapLibre requested.
     */
    public static function render(string $fontstack, string $range): string
    {
        $fonts = preg_split('/\s*,\s*/', rawurldecode($fontstack)) ?: [];
        $seen = [];
        $glyphs = [];

        foreach ($fonts as $font) {
            $path = self::findRangeFile($font, $range);
            if ($path === null) {
                continue;
            }

            foreach (self::extractGlyphs((string) file_get_contents($path)) as $id => $blob) {
                if (isset($seen[$id])) {
                    continue;
                }
                $seen[$id] = true;
                $glyphs[] = $blob;
            }
        }

        $stack = self::encodeLen(1, $fontstack).self::encodeLen(2, $range);
        foreach ($glyphs as $blob) {
            $stack .= self::encodeLen(3, $blob);
        }

        return self::encodeLen(1, $stack);
    }

    public static function findRangeFile(string $font, string $range): ?string
    {
        $font = trim(rawurldecode($font));
        if ($font === '' || str_contains($font, '..') || str_contains($font, '/') || str_contains($font, '\\')) {
            return null;
        }
        if (! preg_match('/^\d+-\d+$/', $range)) {
            return null;
        }

        $folder = self::resolveFolder($font);
        if ($folder === null) {
            return null;
        }

        $path = public_path('fonts/'.$folder.'/'.$range.'.pbf');

        return is_file($path) ? $path : null;
    }

    private static function resolveFolder(string $font): ?string
    {
        $folders = self::folders();
        if (isset($folders[$font])) {
            return $folders[$font];
        }

        $key = self::compact($font);
        $aliases = [
            'notosansregular' => 'NotoSans-Regular',
            'notosansbold' => 'NotoSans-Bold',
            'notosansitalic' => 'NotoSans-Regular',
            'notonaskharabicregular' => 'NotoNaskhArabic-Regular',
            'notonaskharabicbold' => 'NotoNaskhArabic-Bold',
            'robotoregular' => 'NotoSans-Regular',
            'robotomedium' => 'NotoSans-Regular',
            'robotobold' => 'NotoSans-Bold',
            'robotocondenseditalic' => 'NotoSans-Regular',
        ];

        $candidate = $aliases[$key] ?? null;
        if ($candidate !== null && isset($folders[$candidate])) {
            return $folders[$candidate];
        }

        foreach ($folders as $name) {
            if (self::compact($name) === $key) {
                return $name;
            }
        }

        return null;
    }

    private static function folders(): array
    {
        static $folders;

        if ($folders !== null) {
            return $folders;
        }

        $folders = [];
        $root = public_path('fonts');
        if (! is_dir($root)) {
            return $folders;
        }

        foreach (scandir($root) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (is_dir($root.DIRECTORY_SEPARATOR.$entry)) {
                $folders[$entry] = $entry;
            }
        }

        return $folders;
    }

    private static function compact(string $name): string
    {
        return strtolower((string) preg_replace('/[\s_-]+/', '', $name));
    }

    private static function extractGlyphs(string $binary): array
    {
        $glyphs = [];

        foreach (self::parseMessage($binary) as [$field, $wire, $value]) {
            if ($field !== 1 || $wire !== 2 || ! is_string($value)) {
                continue;
            }

            foreach (self::parseMessage($value) as [$stackField, $stackWire, $stackValue]) {
                if ($stackField !== 3 || $stackWire !== 2 || ! is_string($stackValue)) {
                    continue;
                }

                $id = self::glyphId($stackValue);
                if ($id === null) {
                    continue;
                }
                $glyphs[$id] = $stackValue;
            }
        }

        return $glyphs;
    }

    private static function glyphId(string $glyph): ?int
    {
        foreach (self::parseMessage($glyph) as [$field, $wire, $value]) {
            if ($field === 1 && $wire === 0) {
                return (int) $value;
            }
        }

        return null;
    }

    private static function parseMessage(string $buffer): array
    {
        $fields = [];
        $length = strlen($buffer);
        $offset = 0;

        while ($offset < $length) {
            $key = self::readVarint($buffer, $offset);
            if ($key === null) {
                break;
            }

            $field = $key >> 3;
            $wire = $key & 7;

            if ($wire === 0) {
                $value = self::readVarint($buffer, $offset);
                if ($value === null) {
                    break;
                }
            } elseif ($wire === 2) {
                $size = self::readVarint($buffer, $offset);
                if ($size === null || $offset + $size > $length) {
                    break;
                }
                $value = substr($buffer, $offset, $size);
                $offset += $size;
            } elseif ($wire === 5) {
                if ($offset + 4 > $length) {
                    break;
                }
                $value = substr($buffer, $offset, 4);
                $offset += 4;
            } elseif ($wire === 1) {
                if ($offset + 8 > $length) {
                    break;
                }
                $value = substr($buffer, $offset, 8);
                $offset += 8;
            } else {
                break;
            }

            $fields[] = [$field, $wire, $value];
        }

        return $fields;
    }

    private static function readVarint(string $buffer, int &$offset): ?int
    {
        $value = 0;
        $shift = 0;
        $length = strlen($buffer);

        while ($offset < $length && $shift <= 28) {
            $byte = ord($buffer[$offset]);
            $offset++;
            $value |= ($byte & 0x7F) << $shift;
            if (($byte & 0x80) === 0) {
                return $value;
            }
            $shift += 7;
        }

        return null;
    }

    private static function encodeVarint(int $value): string
    {
        $out = '';
        while ($value >= 0x80) {
            $out .= chr(($value & 0x7F) | 0x80);
            $value >>= 7;
        }

        return $out.chr($value);
    }

    private static function encodeLen(int $field, string $data): string
    {
        return self::encodeVarint(($field << 3) | 2).self::encodeVarint(strlen($data)).$data;
    }
}
