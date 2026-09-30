<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('shared.dashboard.index', [
            'user' => $user,
            'roleLabel' => $user->role->label(),
            'hasSignature' => $user->signatures()->where('is_active', true)->exists(),
        ]);
    }
}
