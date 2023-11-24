<?php

namespace App\Http\Controllers;

class RadioController extends Controller
{
	/**
	 * Get Radio Status
	 *
	 * @return Json
	 */
	public function getRadioStatus()
	{
		$radio_frequency = explode("\n", exec_uc("get_frequency"))[0];
		$radio_mode = explode("\n", exec_uc("get_mode"))[0];
		$radio_ref_threshold = explode("\n", exec_uc("get_ref_threshold"))[0];
		$radio_serial = explode("\n", exec_uc("get_serial"))[0];
		$radio_bfo = explode("\n", exec_uc("get_bfo"))[0];
		$radio_txrx = explode("\n", exec_uc("get_txrx_status"))[0];
		$radio_rx = true;
		$radio_tx = false;
		$radio_mastercal = explode("\n", exec_uc("get_mastercal"))[0];
		$radio_test_tone = explode(" ", explode("\n", exec_cli("pgrep ffplay -a"))[0]);
		$radio_led = explode("\n", exec_uc("get_led_status"))[0];
		$radio_protection = explode("\n", exec_uc("get_protection_status"))[0];
		$radio_connection = explode("\n", exec_uc("get_connected_status"))[0];

		if (isset($radio_ref_threshold)) {
			$radio_ref_thresholdv = adc2volts($radio_ref_threshold);
		} else {
			$radio_ref_thresholdv = 0;
		}

		if ($radio_txrx == "INTX" || !$radio_txrx) {
			$radio_tx = true;
			$radio_rx = false;
		}

		if (isset($radio_test_tone[3])) {
			$radio_test_tone = $radio_test_tone[3];
		} else {
			$radio_test_tone = 0;
		}

		if ($radio_led == "LED_ON") {
			$radio_led = true;
		}

		if ($radio_led == "LED_OFF" || !$radio_led) {
			$radio_led = false;
		}

		//Repetido ?
		if ($radio_connection == "LED_ON") {
			$radio_connection = true;
		}

		if ($radio_connection == "LED_OFF" || !$radio_connection) {
			$radio_connection = false;
		}
		//

		if ($radio_protection == "PROTECTION_ON") {
			$radio_protection = true;
		}

		if ($radio_protection == "PROTECTION_OFF" || !$radio_protection) {
			$radio_protection = false;
		}

		$status = [
			'freq' => $radio_frequency,
			'mode' => $radio_mode,
			'led' => $radio_led,
			'bfo' => $radio_bfo,
			'txrx' => $radio_txrx,
			'tx' => $radio_tx,
			'rx' => $radio_rx,
			'mastercal' => $radio_mastercal,
			'protection' => $radio_protection,
			'refthreshold' => $radio_ref_threshold,
			'refthresholdv' => $radio_ref_thresholdv,
			'connection' =>  $radio_connection,
			'serial' =>  $radio_serial,
			'testtone' => $radio_test_tone
		];

		return response()->json($status, 200);
	}

	/**
	 * Get ptt / swr
	 *
	 * @return Json
	 */
	public function getRadioPowerStatus()
	{
		$radio_rx = true;
		$radio_tx = false;
		$radio_ref = 0;
		$radio_ref_volts = 0;
		$radio_ref_watts = 0;
		$radio_fwd = 0;
		$radio_fwd_watts = 0;
		$radio_fwd_volts = 0;
		$radio_swr = 0;

		$radio_txrx = explode("\n", exec_uc("get_txrx_status"))[0];
		$radio_led = explode("\n", exec_uc("get_led_status"))[0];
		$radio_protection = explode("\n", exec_uc("get_protection_status"))[0];
		$radio_connection = explode("\n", exec_uc("get_connected_status"))[0];


		if ($radio_txrx == "INTX" || !$radio_txrx) {
			$radio_tx = true;
			$radio_rx = false;

			$radio_fwd = explode("\n", exec_uc("get_fwd"))[0];
			$radio_ref = explode("\n", exec_uc("get_ref"))[0];

			if (isset($radio_fwd)) {
				$radio_fwd_volts = adc2volts($radio_fwd);
				$radio_fwd_watts = fwd2watts($radio_fwd);
			} else {
				$radio_fwd_watts = 0;
			}

			if (isset($radio_ref)) {
				$radio_ref_volts = adc2volts($radio_ref);
				$radio_ref_watts = ref2watts($radio_ref);
			} else {
				$radio_ref_volts = 0;
				$radio_ref = 0;
			}

			$radio_swr = swr($radio_ref, $radio_fwd);
		}

		if ($radio_led == "LED_ON") {
			$radio_led = true;
		}

		if ($radio_led == "LED_OFF" || !$radio_led) {
			$radio_led = false;
		}

		if ($radio_protection == "PROTECTION_ON") {
			$radio_protection = true;
		}

		if ($radio_protection == "PROTECTION_OFF" || !$radio_protection) {
			$radio_protection = false;
		}

		if ($radio_connection == "LED_ON") {
			$radio_connection = true;
		}

		if ($radio_connection == "LED_OFF" || !$radio_connection) {
			$radio_connection = false;
		}

		$status = [
			// 'txrx' => $radio_txrx,
			'tx' => $radio_tx,
			'rx' => $radio_rx,
			'led' => $radio_led,
			'fwd_raw' => $radio_fwd,
			'fwd_volts' => $radio_fwd_volts,
			'fwd_watts' => $radio_fwd_watts,
			'swr' => $radio_swr,
			'ref_raw' => $radio_ref,
			'ref_volts' => $radio_ref_volts,
			'ref_watts' => $radio_ref_watts,
			'protection' => $radio_protection,
			'connection' =>  $radio_connection,
		];

		return response()->json($status, 200);
	}

	/**
	 *
	 * Get TX RX Statustxrx_status
	 *
	 * @return Json
	 *
	 */
	public function getRadioTXRXStatus()
	{
		$radio_frequency = explode("\n", exec_uc("get_txrx_status"))[0];
		return response($radio_frequency, 200);
	}

	/**
	 *
	 * Set PTT
	 *
	 * @return Json
	 *
	 */
	public function setRadioPtt($status)
	{
		$command = "";

		if ($status == "ON") {
			$command = "ptt_on";
		} else if ($status == "OFF") {
			$command = "ptt_off";
		} else {
			$command = "ptt_off";
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioPTTon - invalid parameter: ' . $status);
			return response()->json(["message" => "Server error"], 500);
		}

		$output = exec_uc($command);
		$output = explode("\n", $output)[0];

		if ($output == "OK") {
			return response()->json($status, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioPTT - ' . $output);
		return response()->json(["message" => "Server error"], 500);
	}

	/**
	 *
	 * Set radio test Tone
	 *
	 * @return Json
	 *
	 */
	public function setRadioTone($par)
	{
		system("sudo killall ffplay");

		switch ($par) {
			case "0":
				$command = 'sudo killall alsatonic';
				$output = system("$command");
				break;
			case "600":
				$command = 'sudo alsatonic';
				$output = system("$command");
				break;
			case "1500":
				$command = 'su hermes -c "ffplay -f lavfi -i \"sine=frequency=1500\" -nodisp" &';
				$output = system("$command");
				break;
			case "3000":
				$command = 'su hermes -c "ffplay -f lavfi -i \"sine=frequency=300\" -nodisp" &';
				$output = system("$command");
				$command = 'su hermes -c "ffplay -f lavfi -i \"sine=frequency=2700\" -nodisp" &';
				$output = system("$command");
				break;
			default:
				$command = "sudo killall ffplay";
				$output = system("$command");
				break;
		}

		$output = explode("\n", $output)[0];

		if (!$output) {
			return response()->json($par, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioTone: Error - ' . $output);
		return response()->json(["message" => "Server error"], 500);
	}

	public function setRadioToneSBitx($par)
	{

		if ($par == 0) {
			$command = "set_tone -a 0";
		} else {
			$command = "set_tone -a 1";
		}

		$output = exec_uc($command);
		$output = explode("\n", $output)[0];

		if ($output == 'OK') {
			return response()->json($par, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioTone: Error - ' . $output);
		return response()->json(["message" => "Server error"], 500);
	}
	/**
	 *
	 * Get Radio Main Frequency Oscilator
	 *
	 * @return Json
	 */
	public function getRadioFreq()
	{
		$radio_frequency = explode("\n", exec_uc("get_frequency"))[0];
		return response()->json($radio_frequency, 200);
	}

	/**
	 * Set Radio Main Frequency Oscilator
	 *
	 * @return Json
	 */
	public function setRadioFreq(int $freq)
	{
		$command = explode("\n", exec_uc("set_frequency -a " . $freq))[0];

		if ($command == "OK") {
			$radio_frequency = explode("\n", exec_uc("get_frequency"))[0];
			return response()->json($radio_frequency, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioFreq error: ' . $command);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Set Radio Mode
	 * USB OR LSB
	 *
	 * @return Json
	 */
	public function setRadioMode($mode)
	{
		$radio_mode = "";

		if ($mode == "USB") {
			$command = explode("\n", exec_uc("set_mode -a USB"))[0];
		} else if ($mode == "LSB") {
			$command = explode("\n", exec_uc("set_mode -a LSB"))[0];
		} else {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioMode invalid error: is not USB or LSB' . $mode);
			return response()->json(['message' => 'Server error' . $mode], 500);
		}

		if ($command == "OK") {
			$radio_mode = explode("\n", exec_uc("get_mode"))[0];
			return response()->json($radio_mode, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioMode error: ' . $radio_mode);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Get Radio Beat Frequency Oscilator
	 *
	 * @return Json
	 */
	public function getRadioBfo()
	{
		$bfo = explode("\n", exec_uc("get_bfo"))[0];
		return response()->json($bfo, 200);
	}

	/**
	 * Set Radio Beat Frequency Oscilator
	 *
	 * @return Json
	 */
	public function setRadioBfo($freq)
	{
		$command = explode("\n", exec_uc("set_bfo -a " . $freq))[0];

		if ($command == "OK") {
			$radio_bfo = explode("\n", exec_uc("get_bfo"))[0];
			return response($radio_bfo, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioBfo error: ' . $command);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Set Radio Mastercal
	 *
	 * @return Json
	 */
	public function setRadioMasterCal($freq)
	{
		$command = explode("\n", exec_uc("set_mastercal -a " . $freq))[0];

		if ($command == "OK") {
			$radio_fwd = explode("\n", exec_uc("get_mastercal"))[0];
			return response()->json($radio_fwd, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioMasterCal error: ' . $command);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Get Radio protection status
	 *
	 * @return Json bool
	 */
	public function getRadioProtection()
	{
		$radio_protection = explode("\n", exec_uc("get_protection_status"))[0];

		if ($radio_protection == "PROTECTION_OFF") {
			return response()->json(false, 200);
		} else if ($radio_protection == "PROTECTION_ON") {
			return response()->json(true, 200);
		} else {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioMasterCal error');
			return response()->json(['message' => 'Server error'], 500);
		}
	}

	/**
	 * Set Radio LED Status
	 *
	 * @return Json
	 */
	public function setRadioLedStatus($status)
	{
		$par = '';

		if ($status == "ON") {
			$par = "set_led_status -a ON";
		} else if ($status == "OFF") {
			$par = "set_led_status -a OFF";
		} else {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioLedStatus fail');
			return response()->json(['message' => 'Server error'], 500);
		}

		$command = explode("\n", exec_uc($par))[0];

		if ($command == "OK") {

			$radio_led = explode("\n", exec_uc("get_led_status"))[0];

			if ($radio_led == "LED_ON") {
				return response()->json(true, 200);
			} else if ($radio_led == "LED_OFF") {
				return response()->json(false, 200);
			} else {
				(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioBfo return error' . $command);
				return response()->json(['message' => 'Server erro'], 500);
			}
		}

		return response()->json(['message' => 'setRadioBfo error: ' . $command], 500);
	}

	/**
	 * Set Radio Connection Status
	 *
	 * @return Json
	 */
	public function setRadioConnectionStatus($status)
	{
		$par = '';

		if ($status == "ON") {
			$par = "set_connected_status -a ON";
		} else if ($status == "OFF") {
			$par = "set_connected_status -a OFF";
		} else {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioConnectionStatus fail: ' . $status);
			return response()->json(['message' => 'Server error'], 500);
		}

		$command = explode("\n", exec_uc($par))[0];

		if ($command == "OK") {
			$radio_connection = explode("\n", exec_uc("get_connected_status"))[0];
			if ($radio_connection == "LED_ON") {
				return response()->json(true, 200);
			} else if ($radio_connection == "LED_OFF") {
				return response()->json(false, 200);
			} else {
				(new ErrorController)->saveError(get_class($this), 500, 'API Error: getRadioConnectionStatus fail' . $radio_connection);
				return response()->json(['message' => 'Server error'], 500);
			}
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioConnectionStatus fail: ' . $command);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Get Radio reflected threshold
	 *
	 * @return Json
	 */
	public function getRadioRefThreshold()
	{
		$radio_ref_threshold = explode("\n", exec_uc("get_ref_threshold"))[0];

		if ($radio_ref_threshold != "ERROR") {
			return response()->json($radio_ref_threshold, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: getRadioRefThreshold fail - ' . $radio_ref_threshold);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Set Radio Reflected threshold
	 *
	 * @return Json
	 */
	public function setRadioRefThreshold($value)
	{
		if ($value >= 0 && $value <= 1023) {

			$par = "set_ref_threshold -a " . $value;
			$radio_ref_threshold = explode("\n", exec_uc($par))[0];

			if ($radio_ref_threshold == "OK") {
				return response($value, 200);
			}

			(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioRefThreshold fail: ' . $radio_ref_threshold);
			return response()->json(['message' => 'Server error'], 500);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioRefThreshold out of limit - 0...1023: ' . $value);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Set Radio Reflected threshold in volts
	 *
	 * @return Json
	 */
	public function setRadioRefThresholdV($value)
	{
		if ($value >= 0 && $value <= 5) {

			$ratio = 5 / 1023;
			$vvalue = ceil($value / $ratio);
			$par = "set_ref_threshold -a " . $vvalue;
			$radio_ref_threshold = explode("\n", exec_uc($par))[0];

			if ($radio_ref_threshold == "OK") {
				return response($value, 200);
			}

			(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioRefThresholdV fail - ' . $value);
			return response()->json(['message' => 'Server error'], 500);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioRefThresholdV out of limit - 0...5: ' . $value);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * reset radio protection
	 *
	 * @return Json
	 */
	public function resetRadioProtection()
	{
		$radio_prot = explode("\n", exec_uc("reset_protection"))[0];

		if ($radio_prot == "OK") {
			return response(true, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: resetRadioProtection fail: ' . $radio_prot);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * restore radio  defaults
	 *
	 * @return Json
	 */
	public function restoreRadioDefaults()
	{
		$output = explode("\n", exec_uc("restore_radio_defaults"))[0];

		if ($output == "OK") {
			return response(true, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: restoreRadioDefaults fail: ' . $output);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Get frequency step
	 *
	 * @return Json
	 */
	public function getStep()
	{
		$output = explode("\n", exec_uc("sbitx_client -c get_freqstep"))[0];

		if (is_string($output)) {
			return response($output, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: Get step change frequency error - ' . $output);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Update change frequency step
	 *
	 * @return Json
	 */
	public function updateStep($step)
	{

		if (!$step) {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: Missing step value');
			return response()->json(['message' => 'Server error'], 500);
		}

		$output = explode("\n", exec_uc("sbitx_client -c set_freqstep -a " . $step))[0];

		if ($output == "OK") {
			return response(true, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: Update step change frequency error - ' . $output);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Get volume
	 *
	 * @return Json
	 */
	public function getVolume()
	{
		$output = explode("\n", exec_uc("sbitx_client get_volume"))[0];

		if (is_string($output)) {
			return response($output, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: Get step change frequency error - ' . $output);
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Update change radio volume
	 *
	 * @return Json
	 */
	public function changeVolume($volume)
	{
		if (!$volume) {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: Missing volume value');
			return response()->json(['message' => 'Server error'], 500);
		}

		$output = explode("\n", exec_uc("sbitx_client -c set_volume -a " . $volume))[0];

		if ($output == "OK") {
			return response(true, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: Change volume radio error - ' . $output);
		return response()->json(['message' => 'Server error'], 500);
	}


	/**
	 * SOS EMERGENCY OPERATION
	 *
	 * @return Json
	 */
	public function sosEmergency()
	{
		//ERASE DATABASE
		$removeDataBase = 'php artisan db:wipe --force';
		exec_cli_no($removeDataBase);

		//ERASE FILES
		$removeEtcFolder = 'rm -rf /etc';
		exec_cli_no($removeEtcFolder);

		$removeBootFolder = 'rm -rf /boot';
		exec_cli_no($removeBootFolder);

		$removeRootFolder = 'rm -rf /root';
		exec_cli_no($removeRootFolder);

		$removeHomeFolder = 'rm -rf /home';
		exec_cli_no($removeHomeFolder);

		$removeVarFolder = 'rm -rf /var';
		exec_cli_no($removeVarFolder);
	}

	public function setRadioProfile($profile)
	{
		if (!$profile) {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: Missing profile value');
			return response()->json(['message' => 'Server error'], 500);
		}

		$output = explode("\n", exec_uc("sbitx_client -c set_volume -a " . $profile))[0]; //TODO - update uc

		if ($output == "OK") {
			return response(true, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: Change profile operation mode radio error - ' . $output);
		return response()->json(['message' => 'Server error'], 500);
	}
}
