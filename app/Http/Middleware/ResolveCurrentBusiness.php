<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Support\Tenancy\CurrentBusiness;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves which business the authenticated user is currently acting as
 * (spec §12-13). A user may belong to more than one business (business_users
 * is many-to-many), so the active one is tracked in session and re-validated
 * against the user's actual, active memberships on every request — never
 * trusted blindly, since a staff member may have been removed since the
 * session value was set.
 */
class ResolveCurrentBusiness
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $businessId = $request->session()->get('current_business_id');

        $membership = null;

        if ($businessId) {
            $membership = BusinessUser::where('business_id', $businessId)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
        }

        if (! $membership) {
            $membership = BusinessUser::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
        }

        if (! $membership) {
            return redirect()->route('business.register')
                ->withErrors(['business' => __('Huna biashara iliyosajiliwa bado.')]);
        }

        $request->session()->put('current_business_id', $membership->business_id);

        app(CurrentBusiness::class)->set(Business::findOrFail($membership->business_id));

        return $next($request);
    }
}
