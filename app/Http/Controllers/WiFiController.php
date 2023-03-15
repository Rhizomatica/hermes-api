<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class WiFiController extends Controller
{

	public function getWiFiConfigurations()
	{
		$hostap_file = file_get_contents("/etc/hostapd/hostapd.conf", false);

		$parsed_cfg = explode("\n", $hostap_file);

		foreach ($parsed_cfg as $i)
		{
			if (strpos($i, "=") !== false)
			{
				list($name,$value) = explode("=", $i, 2);

				if( $name == "channel" || $name == "ssid" || $name == "wpa_passphrase" )
					$wifi_settings[$name] = $value;
			}
		}

		if (isset($wifi_settings['channel']) && isset($wifi_settings['ssid']) && isset($wifi_settings['wpa_passphrase']))
			return response()->json($wifi_settings, 200);
		else
			return response()->json(['message' => 'Server error'], 500);}
	}

	public function saveWiFiConfigurations(Request $request)
	{
		$this->validate($request, [
			'channel' => 'required|string',
			'ssid' => 'required|string',
			'wpa_passphrase' =>  'required|string'
		]);

		if (exec_cli_no("sudo cp /etc/hostapd/hostapd.conf.head /etc/hostapd/hostapd.conf") == false)
			return response()->json(['message' => 'Server error'], 500);

		exec_cli("sudo sh -c \"echo channel={$channel} >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo ssid={$ssid} >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo wpa_passphrase={$wpa_passphrase} >> /etc/hostapd/hostapd.conf\"");

		if (exec_cli_no('sudo systemctl restart hostapd') == false)
			return response()->json(['message' => 'Server error'], 500);

		return response(true, 200);
	}
}
