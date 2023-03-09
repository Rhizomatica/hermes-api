<?php

namespace App\Http\Controllers;

use App\Frequencies;
use Illuminate\Http\Request;

class WiFiController extends Controller
{

	public function getWifiConfigurations()
	{
		$wifiConfigCLI = "";
		$wifiConfig = explode("\n", exec_cli($wifiConfigCLI))[0];

		if ($wifiConfig != '0') {
			return response()->json(['message' => 'Server error'], 500);
		}
		
		return response()->json($wifiConfig, 200);
	}

	public function saveWiFiConfigurations(Request $request)
	{
		$this->validate($request, [
			'ssid' => 'required|string',
			'password' =>  'required|string',
			'chanel' => 'required|integer'
		]);

		$saveWifiConfigCLI = '' . $request->ssid;
		
		$wifiConfig = explode("\n", exec_cli($saveWifiConfigCLI))[0];

		if ($wifiConfig != '0') {
			return response()->json(['message' => 'Server error'], 500);
		}

		$restartWifi = '';
		$applyChanges = explode("\n", exec_cli($restartWifi))[0];

		if ($applyChanges != '0') {
			return response()->json(['message' => 'Server error'], 500);
		}

		return response()->json($saveWifiConfigCLI, 200); 
	}
}
