<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\View\View;

class StoreProfileController extends Controller
{
    public function index(): View
    {
        $setting = StoreSetting::current();

        return view('public.profile', compact('setting'));
    }
}
