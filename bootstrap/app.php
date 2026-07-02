<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Middleware\NotSale;
use App\Http\Middleware\BrandSwitcher;
use App\Http\Middleware\BranchSwitcher;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
      'notsale' => NotSale::class,
    ]);
    $middleware->appendToGroup('web', BrandSwitcher::class);
    $middleware->appendToGroup('web', BranchSwitcher::class);

    // ลิงก์อนุมัติสถานที่ในเมล (MD ไม่ได้ login จึงไม่มี CSRF token)
    $middleware->validateCsrfTokens(except: [
      'source/approval/*',
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    // record หลุด scope (เช่น สลับ brand/branch แล้วเปิด record ของ brand เดิม → findOrFail ล้มเหลว)
    // ให้เด้งกลับหน้า home แทนหน้า 404 — เฉพาะการเปิดหน้าเว็บ (GET, ไม่ใช่ AJAX/JSON) และล็อกอินอยู่
    // NOTE: Laravel แปลง ModelNotFoundException เป็น NotFoundHttpException ก่อน render callback
    //       จึงต้องดัก NotFoundHttpException แล้วเช็ค getPrevious() ว่ามาจาก findOrFail
    //       URL ที่ผิดจริง (route ไม่มี) จะไม่มี previous เป็น ModelNotFound → ยังขึ้นหน้า 404 ตามเดิม
    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
      if ($e->getPrevious() instanceof ModelNotFoundException
        && $request->user() && $request->isMethod('GET') && !$request->expectsJson() && !$request->ajax()) {
        return redirect()->route('home');
      }
    });
  })->create();
