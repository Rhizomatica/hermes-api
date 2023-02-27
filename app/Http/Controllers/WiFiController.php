<?php

namespace App\Http\Controllers;

use App\Frequencies;
use Illuminate\Http\Request;

class WiFiController extends Controller
{

	public function getWiFiConfigurations()
	{
		return response()->json(null, 200);
	}

	public function saveWiFiConfigurations(Request $request)
	{
		$wiFiConfigurations = null;

		return response()->json($wiFiConfigurations, 200);
	}
}
