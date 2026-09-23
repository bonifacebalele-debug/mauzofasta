<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;

/**
 * A user can belong to more than one business (business_users is
 * many-to-many) — this lets them pick which one is "current" for the
 * session. See App\Http\Middleware\ResolveCurrentBusiness.
 */
class SwitchBusinessController extends Controller
{
    public function __invoke(Request $request, Business $business)
    {
        $membership = $request->user()->businessUsers()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->first();

        abort_unless($membership, 403);

        $request->session()->put('current_business_id', $business->id);

        return redirect()->route('dashboard.index');
    }
}
