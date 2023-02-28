<?php

namespace App\Http\Controllers;

use App\Frequencies;
use Illuminate\Http\Request;

class WiFiController extends Controller
{
	// https://www.thegeekdiary.com/how-to-set-a-custom-interface-name-with-networkmanager-in-centos-rhel-7/
	// https://unix.stackexchange.com/questions/682807/how-to-change-network-interface-name
	

	// https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/7/html/networking_guide/sec-configuring_ip_networking_with_nmcli
	public function getWiFiConfigurations()
	{
		return response()->json(null, 200);
	}

	public function saveWiFiConfigurations(Request $request)
	{
		$wiFiConfigurations = null;
		$changeNameCLI = 'nmcli c modify "My Connection" connection.id "My favorite connection" connection.interface-name enp1s0';

		$restartWifiCLI = 'shutdown -r now';

		//To apply changes after a modified connection using nmcli, activate again the connection by entering this command:

		$saveWifiConfigCLI ='nmcli con up con-name';


		return response()->json($wiFiConfigurations, 200);
	}
}
