<?php

namespace App\Support;

class InfoTypeMapper
{
    public static function normalize(?string $value): ?string
    {
        if (!$value) return null;

        $v = trim($value);

        // Map FRONT values (Pashto/Dari) to your internal keys
        $map = [
            'ساعین بالفساد' => 'saeenfasad',
            'شر او فساد' => 'fasad',
            'جواسیس' => 'jawasis',
            'اختطافچیان' => 'robber',
            'د نشه یی توکو پلورونکي' => 'mawads',
            'غله' => 'heists',
            'غیر مسوله وسلوال' => 'noneresposablepeole',
            'زورواکان او ولکه داران' => 'badmashano',
            'لوچکان' => 'luchakano',
            'نوې راغلې کورونه' => 'mahajeerat',
            'مشکوک کورونه' => 'mashkok_homes',
            'مشکوک دوکانونه' => 'mashkok_shops',
            'مشکوک افراد' => 'mashkok_people',
            'راخوشي شوي بندیان' => 'captives',
        ];

        // If front sometimes sends also the english key, accept it too
        if (isset($map[$v])) return $map[$v];

        // fallback: return trimmed value (maybe already slug)
        return $v;
    }
}