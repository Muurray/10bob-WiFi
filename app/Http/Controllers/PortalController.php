<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\MikrotikPortalLink;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function packages(Request $request, MikrotikPortalLink $portalLink): View
    {
        $packages = Package::query()->where('active', true)->orderBy('price')->get();
        $portalContext = $request->only(['ip', 'mac', 'link-login-only', 'link-orig']);
        $trialUrl = $portalLink->trialUrl(
            $portalContext['link-login-only'] ?? null,
            $portalContext['mac'] ?? null,
            $portalContext['link-orig'] ?? null,
        );

        return view('customer.index', compact('packages', 'portalContext', 'trialUrl'));
    }

    public function terms(): View
    {
        return view('customer.terms');
    }
}