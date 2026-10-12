<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hosting berjalan di belakang CDN/proxy (TLS termination),
        // jadi semua proxy dipercaya agar deteksi HTTPS/asset/cookie benar.
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [SecurityHeaders::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (LockTimeoutException $exception, Request $request) {
            if ($request->routeIs('pelapor.ai-assistant')) {
                return response()->json(['message' => 'Pesan sebelumnya masih diproses. Tunggu sebentar lalu coba lagi.'], 429)
                    ->header('Retry-After', '2');
            }
        });
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Ukuran file yang diunggah melebihi batas maksimum server.'], 413);
            }

            return back()->withErrors(['file' => 'Ukuran file yang diunggah terlalu besar. Batas maksimum untuk arsip adalah 50 MB dan untuk laporan 5 MB per file.'])->withInput();
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
