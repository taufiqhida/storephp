<?php

namespace App\Http\Middleware;

use App\Models\StoreSetting;
use Closure;
use Illuminate\Http\Request;

class CheckSiteMode
{
    public function handle(Request $request, Closure $next)
    {
        $setting = StoreSetting::current();

        if ($setting->site_mode === 'maintenance') {
            return redirect()->route('maintenance');
        }

        if ($setting->site_mode === 'coming_soon') {
            return redirect()->route('coming-soon');
        }

        return $next($request);
    }
}
