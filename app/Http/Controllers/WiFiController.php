<?php

namespace App\Http\Controllers;

use App\Frequencies;
use Illuminate\Http\Request;

class WiFiController extends Controller
{
	// https://www.thegeekdiary.com/how-to-set-a-custom-interface-name-with-networkmanager-in-centos-rhel-7/
	// https://unix.stackexchange.com/questions/682807/how-to-change-network-interface-name


	// https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/7/html/networking_guide/sec-configuring_ip_networking_with_nmcli

	public function getWifiList()
	{
		$wifiListCLI = 'nmcli -t -f active,ssid dev wifi';
		$wifiListCLI = explode("\n", exec_cli($wifiListCLI))[0];

		if ($wifiListCLI == 'ERROR') {
			return response()->json(['message' => 'Server error'], 500);
		}

		return response()->json($wifiListCLI, 200);
	}

	public function getConnectedtWiFi()
	{
		$wifiListCLI = "nmcli -t -f active,ssid dev wifi | egrep '^yes' | cut -d\' -f2";
		$wifiListCLI = explode("\n", exec_cli($wifiListCLI))[1]; //TODO - Review return

		if ($wifiListCLI == 'ERROR') {
			return response()->json(['message' => 'Server error'], 500);
		}

		if ($wifiListCLI == '') {
			return response()->json(['message' => 'Not found'], 404);
		}

		return response()->json($wifiListCLI, 200);
	}

	public function saveWiFiConfigurations(Request $request)
	{
		$this->validate($request, [
			'name' => 'required|string'
		]);

		$connectedWiFi = $this->getConnectedtWiFi();

		//TODO - Conferir rede (enp1s0)
		$changeNameCLI = 'nmcli c modify "' . $connectedWiFi . '" connection.id "' . $request->name . '" connection.interface-name enp1s0';
		$newWifiName = explode("\n", exec_cli($changeNameCLI))[0];

		if ($newWifiName == 'ERROR') { //TODO - Check error return
			return response()->json(['message' => 'Server error'], 500);
		}

		$saveWifiConfigCLI = 'nmcli con up ' . $request->name;
		$applyChanges = explode("\n", exec_cli($saveWifiConfigCLI))[0];

		//TODO - Salvar no banco?

		if ($applyChanges == 'ERROR') { //TODO - Check error return
			return response()->json(['message' => 'Server error'], 500);
		}

		return response()->json($saveWifiConfigCLI, 200); //TODO - review return
	}
}
