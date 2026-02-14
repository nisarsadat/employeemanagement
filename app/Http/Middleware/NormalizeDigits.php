<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeDigits
{
    private array $map = [
        '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
        '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
    ];

    private function normalize($value)
    {
        if (is_string($value)) return strtr($value, $this->map);

        if (is_array($value)) {
            foreach ($value as $k => $v) $value[$k] = $this->normalize($v);
            return $value;
        }

        return $value;
    }

    public function handle(Request $request, Closure $next)
    {
        $request->merge($this->normalize($request->all()));
        return $next($request);
    }
}
