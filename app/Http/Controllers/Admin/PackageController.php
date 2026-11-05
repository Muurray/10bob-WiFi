<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::latest()->get();

        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        Package::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'in:Hour,Hours,Day,Days'],
            'device_limit' => ['required', 'integer', 'in:1,2'],
        ]));

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created.');
    }
}