<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WiFiController extends Controller
{

	public function getWiFiConfigurations()
	{
		$hostap_file = file_get_contents("/etc/hostapd/hostapd.conf", false);

		$parsed_cfg = explode("\n", $hostap_file);

		foreach ($parsed_cfg as $i) {
			if (strpos($i, "=") !== false) {
				list($name, $value) = explode("=", $i, 2);

				if ($name == "channel" || $name == "ssid" || $name == "wpa_passphrase")
					$wifi_settings[$name] = $value;
			}
		}

		if (isset($wifi_settings['channel']) && isset($wifi_settings['ssid']) && isset($wifi_settings['wpa_passphrase']))
			return response()->json($wifi_settings, 200);
		else
			return response()->json(['message' => 'Server error'], 500);
	}

	public function saveWiFiConfigurations(Request $request)
	{
		$this->validate($request, [
			'channel' => 'required|string',
			'ssid' => 'required|string',
			'wpa_passphrase' =>  'required|string'
		]);

		// copy("/etc/hostapd/hostapd.conf.head", "/etc/hostapd/hostapd.conf.head")
		// exec()

		// exec('sudo systemctl restart hostapd', $output, $return_value);


		//if ($return_value != '0') {
		return response()->json(['message' => 'Server error'], 500);
		//}

		//	return response()->json($saveWifiConfigCLI, 200);
	}
}
