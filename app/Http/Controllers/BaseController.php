<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * Abort if the current user lacks ALL of the given permissions.
     * Accepts a single permission string (e.g. "employees.view") or an array.
     */
    protected function authorizePermission(string|array $permission): void
    {
        $user = request()->user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $required = is_array($permission) ? $permission : [$permission];
        foreach ($required as $perm) {
            if ($user->can($perm)) {
                return; // authorized by at least one permission
            }
        }

        abort(403, 'Forbidden');
    }
}
