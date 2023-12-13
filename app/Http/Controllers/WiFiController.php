<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WiFiController extends Controller
{

	public function getWiFiConfigurations()
	{
		$hostap_file = file_get_contents("/etc/hostapd/hostapd.conf", false);

		if (!$hostap_file) {
			(new ErrorController)->saveError(get_class($this), 500, 'Error: WiFi settings are unavailable');
			return response()->json(['message' => 'Server error'], 500);
		}

		$parsed_cfg = explode("\n", $hostap_file);

		foreach ($parsed_cfg as $i) {
			if (strpos($i, "=") !== false) {
				list($name, $value) = explode("=", $i, 2);

				if ($name == "channel" || $name == "ssid" || $name == "wpa_passphrase" || $name == 'macaddr_acl')
					$wifi_settings[$name] = $value;
			}
		}

		//get mac address list
		if (!file_exists("/etc/hostapd/accept")) {
			exec_cli("sudo touch /etc/hostapd/accept");
		}

		$accept_mac_file = file_get_contents("/etc/hostapd/accept", false);
		$mac_list = explode("\n", $accept_mac_file);
		$wifi_settings['accept_mac_file'] = $mac_list;

		if (isset($wifi_settings['channel']) && isset($wifi_settings['ssid']) && isset($wifi_settings['wpa_passphrase']) && isset($wifi_settings['macaddr_acl']))
			return response()->json($wifi_settings, 200);

		(new ErrorController)->saveError(get_class($this), 500, 'Error: WiFi settings are unavailable');
		return response()->json(['message' => 'Server error'], 500);
	}

	public function saveWiFiConfigurations(Request $request)
	{
		$this->validate($request, [
			'channel' => 'required|string',
			'ssid' => 'required|string',
			'wpa_passphrase' =>  'required|string'
		]);

		exec_cli_no("sudo cp /etc/hostapd/hostapd.conf.head /etc/hostapd/hostapd.conf");
		exec_cli("sudo sh -c \"echo channel={$request->channel} >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo ssid={$request->ssid} >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo wpa_passphrase={$request->wpa_passphrase} >> /etc/hostapd/hostapd.conf\"");

		// $wifiRestart = 
		exec_cli_no("sudo systemctl restart hostapd");

		// if ($wifiRestart == false) {
		// 	(new ErrorController)->saveError(get_class($this), 500, 'Error: could not restart WiFi device');
		// 	return response()->json(['message' => 'Server error'], 500);
		// }

		return response(true, 200);
	}

	public function macFilter(Request $request)
	{
		$this->validate($request, [
			'macFilter' => 'required|string'
		]);

		exec_cli_no("sudo cp /etc/hostapd/hostapd.conf.head /etc/hostapd/hostapd.conf");
		exec_cli("sudo sh -c \"echo macaddr_acl={$request->macFilter} >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo accept_mac_file=/etc/hostapd/accept >> /etc/hostapd/hostapd.conf\"");

		return response(true, 200);
	}

	public function macAddressList(Request $request)
	{
		$this->validate($request, [
			'macAddressList' => 'required|string'
		]);

		exec_cli("sudo sh -c \"echo {$request->macAddressList} >> /etc/hostapd/accept\"");
		exec_cli_no("sudo systemctl restart hostapd");

		return response(true, 200);
	}
}
