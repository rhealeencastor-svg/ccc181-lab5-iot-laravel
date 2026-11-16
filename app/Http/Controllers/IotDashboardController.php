<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;
use Illuminate\Contracts\View\View;

class IotDashboardController extends Controller
{
    public function __invoke(): View
    {
        $readings = SensorReading::latest('id')->limit(20)->get();

        return view('iot-dashboard', [
            'latest' => $readings->first(),
            'readings' => $readings,
            'displayTimezone' => config('iot.display_timezone', 'Asia/Manila'),
        ]);
    }
}
