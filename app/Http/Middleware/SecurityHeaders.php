<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $headerName = env('CSP_ENFORCE', false)
            ? 'Content-Security-Policy'
            : 'Content-Security-Policy-Report-Only';

        $response->headers->set($headerName, $this->contentSecurityPolicy());

        return $response;
    }

    /**
     * Daftar sumber eksternal di sini mengikuti yang benar-benar dipakai di
     * layouts/rumah_sakit.blade.php & halaman terkait (Google Fonts, jsDelivr,
     * unpkg untuk AOS, cdnjs untuk pdf.js di viewer majalah). Alpine.js & Livewire
     * memerlukan 'unsafe-inline'/'unsafe-eval' untuk x-data, x-init, dan ekspresi
     * inline lainnya — tanpa itu, sebagian besar interaksi di portal akan patah.
     */
    private function contentSecurityPolicy(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com",
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
