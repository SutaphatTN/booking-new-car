<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
    //
  })->create();
