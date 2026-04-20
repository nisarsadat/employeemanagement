<?php

namespace App\Support;

class InfoTypeMapper
{
    public static function normalize(?string $value): ?string
    {
        if (!$value) return null;

        $v = trim($value);

        // normalize Arabic/Persian variants
        $v = str_replace(['ي', 'ى', 'ك', 'ۀ', 'ة'], ['ی', 'ی', 'ک', 'ه', 'ه'], $v);

        // remove extra internal spaces
        $v = preg_replace('/\s+/u', ' ', $v);

        $map = [
            'ساعین بالفساد' => 'saeenfasad',
            'شر او فساد' => 'fasad',
            'جواسیس' => 'jawasis',
            'اختطافچیان' => 'robber',
            'د نشه یی توکو پلورونکي' => 'mawads',
            'غله' => 'heists',
            'غیر مسؤوله وسلوال' => 'noneresposablepeole',
            'زورواکان او ولکه داران' => 'badmashano',
            'لوچکان' => 'luchakano',
            'نوې راغلې کورونه' => 'mahajeerat',
            'مشکوک کورونه' => 'mashkok_homes',
            'مشکوک دوکانونه' => 'mashkok_shops',
            'مشکوک افراد' => 'mashkok_people',
            'راخوشي شوي بندیان' => 'captives',
        ];

        return $map[$v] ?? $v;
    }
}