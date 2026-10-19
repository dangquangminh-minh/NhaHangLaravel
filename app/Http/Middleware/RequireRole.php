<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $allowed = $user && ($user->hasRole(...$roles) || (in_array('admin', $roles, true) && $user->isAdmin()));
        abort_unless($allowed, 403, 'Bạn không có quyền thực hiện chức năng này.');

        return $next($request);
    }
}
