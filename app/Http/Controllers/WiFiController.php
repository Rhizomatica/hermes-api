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

		exec_cli("sudo truncate -s 0 /etc/hostapd/hostapd.conf");
		exec_cli_no("sudo cp /etc/hostapd/hostapd.conf.head /etc/hostapd/hostapd.conf");
		exec_cli("sudo sh -c \"echo channel={$request->channel} >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo ssid={$request->ssid} >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo wpa_passphrase={$request->wpa_passphrase} >> /etc/hostapd/hostapd.conf\"");

		exec_cli_no("sudo systemctl restart hostapd");

		return response(true, 200);
	}

	public function macFilter(Request $request)
	{
		$this->validate($request, [
			'macFilter' => 'required|string'
		]);

		$hostap_file = file_get_contents("/etc/hostapd/hostapd.conf", false);
		$parsed_cfg = explode("\n", $hostap_file);

		foreach ($parsed_cfg as $i) {
			if (strpos($i, "=") !== false) {
				list($name, $value) = explode("=", $i, 2);

				if ($name == "channel" || $name == "ssid" || $name == "wpa_passphrase")
					$wifi_settings[$name] = $value;
			}
		}

		exec_cli("sudo truncate -s 0 /etc/hostapd/hostapd.conf");
		exec_cli_no("sudo cp /etc/hostapd/hostapd.conf.head /etc/hostapd/hostapd.conf");
		exec_cli("sudo sh -c \"echo channel=$wifi_settings[channel] >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo ssid=$wifi_settings[ssid] >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo wpa_passphrase=$wifi_settings[wpa_passphrase] >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo macaddr_acl={$request->macFilter} >> /etc/hostapd/hostapd.conf\"");
		exec_cli("sudo sh -c \"echo accept_mac_file=/etc/hostapd/accept >> /etc/hostapd/hostapd.conf\"");

		return response(true, 200);
	}

	public function macAddress(Request $request)
	{
		$this->validate($request, [
			'macAddress' => 'required|string'
		]);

		exec_cli("sudo sh -c \"echo {$request->macAddress} >> /etc/hostapd/accept\"");
		exec_cli_no("sudo systemctl restart hostapd");

		return response(true, 200);
	}

	public function deleteMacAddress($address)
	{
		if (!$address) {
			return response()->json(['message' => 'Missing address to remove'], 500);
		}

		$accept_file = file_get_contents("/etc/hostapd/accept", false);
		$parsed_accept_file = explode("\n", $accept_file);

		exec_cli("sudo truncate -s 0 /etc/hostapd/accept");

		foreach ($parsed_accept_file as $i) {
			if ($i !== $address && $i !== '') {
				exec_cli("sudo sh -c \"echo {$i} >> /etc/hostapd/accept\"");
			}
		}

		exec_cli_no("sudo systemctl restart hostapd");

		return response(true, 200);
	}
}
