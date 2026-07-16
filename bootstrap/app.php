<?php

use App\Constants\HttpStatusConstants;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Responses\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
// 1. تشغيل منطق اللغة تلقائياً لكل الطلبات القادمة للـ API
//        $middleware->append(function ($request, $next) {
//            if ($request->hasHeader('Accept-Language')) {
//                app()->setLocale($request->header('Accept-Language'));
//            }
//            return $next($request);
//        });


        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);


        // 2. إجبار لارافيل على إرجاع رد JSON مسبك (401) للزوار غير المسجلين في الـ API
        $middleware->redirectGuestsTo(fn () => null);

        //
        //وأضف Middleware الخاص بـ Spatieمن أجل permissions  بالطلب Story 4 (Roles & Permissions)
//3. تسجيل ميدل وير التحقق من الدور (يعمل مع Sanctum)
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'permission' => PermissionMiddleware::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // أخطاء التحقق من المدخلات (422)
        $exceptions->render(function (ValidationException $e, $request) {
            return Response::Validation(
                $e->errors(),
                $e->getMessage(),
                HttpStatusConstants::HTTP_422_UNPROCESSABLE_ENTITY
            );
        });

        // مستخدم غير مصادق (401)
        $exceptions->render(function (AuthenticationException $e, $request) {
            return Response::Error('', __('Unauthenticated'), HttpStatusConstants::HTTP_401_UNAUTHORIZED);
        });

        // صلاحيات غير كافية (403)
        $exceptions->render(function (AccessDeniedHttpException $e, $request) {
            return Response::Error('', __('strings.You do not have the required authorization'), HttpStatusConstants::HTTP_403_FORBIDDEN);
        });

        // مورد غير موجود (404)
        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, $request) {
            return Response::Error('', __('Resource not found'), HttpStatusConstants::HTTP_404_NOT_FOUND);
        });
    })->create(); // ⚠️ أضف هذه هنا وتأكد من الفاصلة المنقوطة
