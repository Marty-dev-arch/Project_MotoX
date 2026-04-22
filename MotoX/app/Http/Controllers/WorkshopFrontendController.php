<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class WorkshopFrontendController extends Controller
{
    public function landing(): View
    {
        return view('pages.landing', [
            'pageTitle' => 'MotoX | Automotive Management',
        ]);
    }
}

