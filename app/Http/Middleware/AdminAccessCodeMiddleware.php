<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessCodeMiddleware
{
    public const SESSION_KEY = 'admin_access_granted';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get(self::SESSION_KEY)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(Response::HTTP_FORBIDDEN, __('Kode akses diperlukan.'));
        }

        return redirect()->guest(route('admin.access-code.show'));
    }
}
