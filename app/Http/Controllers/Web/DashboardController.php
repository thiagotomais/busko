<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the dashboard home page
     */
    public function index(): View
    {
        $user = auth()->user();
        
        $stats = [
            'drivers' => Driver::count(),
            'guardians' => Guardian::count(),
            'users' => User::count(),
        ];

        return view('dashboard.index', compact('user', 'stats'));
    }

    /**
     * Show drivers list
     */
    public function drivers(): View
    {
        $drivers = Driver::with(['user', 'address', 'tenant'])->paginate(15);
        return view('dashboard.drivers.index', compact('drivers'));
    }

    /**
     * Show driver details
     */
    public function driverShow(Driver $driver): View
    {
        $driver->load(['user', 'address', 'tenant', 'guardians']);
        return view('dashboard.drivers.show', compact('driver'));
    }

    /**
     * Show guardians list
     */
    public function guardians(): View
    {
        $guardians = Guardian::with(['user', 'address', 'tenant'])->paginate(15);
        return view('dashboard.guardians.index', compact('guardians'));
    }

    /**
     * Show guardian details
     */
    public function guardianShow(Guardian $guardian): View
    {
        $guardian->load(['user', 'address', 'tenant', 'drivers']);
        return view('dashboard.guardians.show', compact('guardian'));
    }
}
