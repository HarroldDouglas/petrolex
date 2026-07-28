<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the web admin panel to staff roles.
 *
 * Without this, any user in the `users` table — including customers and
 * delivery persons who authenticate with the same web guard — could reach the
 * dashboard and every admin page (only `auth` was enforced). Customers/delivery
 * persons use the mobile apps (Sanctum tokens), never the web panel.
 */
class EnsureUserIsStaff
{
    /**
     * Roles allowed into the web admin panel.
     *
     * @var list<string>
     */
    private const STAFF_ROLES = [
        'super_admin',
        'admin',
        'manager',
        'accountant',
        'gas_manager',
        'center_manager',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole(self::STAFF_ROLES)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(Response::HTTP_FORBIDDEN, __('auth.staff_only'));
        }

        return $next($request);
    }

    /**
     * The staff roles allowed into the panel (exposed for reuse, e.g. at login).
     *
     * @return list<string>
     */
    public static function staffRoles(): array
    {
        return self::STAFF_ROLES;
    }
}
