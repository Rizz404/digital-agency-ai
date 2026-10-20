<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentAgency;
use App\Http\Controllers\Controller;
use App\Modules\Operations\Queries\DashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesCurrentAgency;

    public function index(Request $request, DashboardMetrics $metrics): View
    {
        $agency = $this->currentAgency($request);

        return view('admin.dashboard', [
            'agency' => $agency,
            'stats' => $metrics->counts($agency),
            'recentConsultations' => $metrics->recentConsultations($agency),
            'openHandovers' => $metrics->latestOpenHandovers($agency),
        ]);
    }
}
