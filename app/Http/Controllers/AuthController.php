<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WarehouseMaster;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('index');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'admin_name' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cocokkan admin_name dengan password yang diarahkan ke kolom admin_password
        if (Auth::attempt([
            'admin_name' => $credentials['admin_name'], 
            'password' => $credentials['password'] // Laravel otomatis menghash & mencocokkan dengan kolom password default model
        ])) {
            $request->session()->regenerate();
            
            $admin = Auth::user();

            // Sesuai sistem baru, ambil default warehouse dari helper role
            $warehouseId = $admin->getDefaultWarehouseId() ?? ($admin->warehouse_id ?? 1);

            session([
                'admin_id' => $admin->id,
                'region_alias' => optional($admin->region)->region_alias ?? 'JKT',
                'warehouse_id' => $warehouseId,
            ]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'admin_name' => 'Nama admin atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}