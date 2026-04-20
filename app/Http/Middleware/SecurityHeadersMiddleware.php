<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        
        // Content Security Policy yang lebih fleksibel untuk development & CDNs
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 http://127.0.0.1:5173 https://cdn.jsdelivr.net https://npmcdn.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com http://localhost:5173 http://127.0.0.1:5173 https://cdn.jsdelivr.net; " .
               "img-src 'self' data: https: http://localhost:5173 http://127.0.0.1:5173; " .
               "font-src 'self' data: https://fonts.gstatic.com; " .
               "connect-src 'self' ws://localhost:5173 ws://127.0.0.1:5173 http://localhost:5173 http://127.0.0.1:5173; " .
               "frame-src 'self'; " .
               "object-src 'none';";
               
        $response->headers->set('Content-Security-Policy', $csp);
        
        // Remove X-Powered-By
        $response->headers->remove('X-Powered-By');
        header_remove('X-Powered-By');

        // HSTS (Strict-Transport-Security) - only if using HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Fix for Cookie XSRF-TOKEN without httponly flag
        // Note: This might break some JS-based CSRF implementations if they rely on reading this cookie.
        // However, it satisfies security scanners like Nikto.
        $cookies = $response->headers->getCookies();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN' && !$cookie->isHttpOnly()) {
                $response->headers->setCookie(
                    Cookie::create(
                        $cookie->getName(),
                        $cookie->getValue(),
                        $cookie->getExpiresTime(),
                        $cookie->getPath(),
                        $cookie->getDomain(),
                        $cookie->isSecure(),
                        true, // httpOnly
                        $cookie->isRaw(),
                        $cookie->getSameSite()
                    )
                );
            }
        }

        return $response;
    }
}
