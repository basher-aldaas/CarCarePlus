<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * اللغات المدعومة في التطبيق.
     *
     * @var array<int, string>
     */
    protected array $supportedLocales = ['en', 'ar'];

    /**
     * يحدد لغة التطبيق بناءً على ترويسة Accept-Language.
     * يقبل قيمًا مثل: "ar" أو "ar-SA" أو "en-US,en;q=0.9".
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Accept-Language');

        if ($header) {
            // خذ أول لغة قبل الفاصلة، ثم رمز اللغة قبل الشرطة (ar-SA => ar)
            $locale = strtolower(substr(trim(explode(',', $header)[0]), 0, 2));

            if (in_array($locale, $this->supportedLocales, true)) {
                app()->setLocale($locale);
            }
        }

        return $next($request);
    }
}
