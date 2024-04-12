<?php

namespace App\Http\Controllers;

use Hamcrest\Type\IsInteger;

class RadioController extends Controller
{
	/**
	 * Get Radio Status
	 *
	 * @return Json
	 */
	public function getRadioStatus($profile)
	{
		if ($profile === null) {
			$profile = 0;
		}

		$radio_frequency = explode("\n", exec_uc("get_frequency -p " . $profile))[0];
		$radio_mode = explode("\n", exec_uc("get_mode -p " . $profile))[0];
		$radio_ref_threshold = explode("\n", exec_uc("get_ref_threshold -p " . $profile))[0];
		$radio_serial = explode("\n", exec_uc("get_serial -p " . $profile))[0];
		$radio_bfo = explode("\n", exec_uc("get_bfo -p " . $profile))[0];
		$radio_txrx = explode("\n", exec_uc("get_txrx_status -p " . $profile))[0];
		$radio_rx = true; //TODO - Verify!! Same for both profiles?
		$radio_tx = false;
		$radio_mastercal = explode("\n", exec_uc("get_mastercal -p " . $profile))[0];
		$radio_test_tone = explode(" ", explode("\n", exec_cli("pgrep ffplay -a -p " . $profile))[0]);
		$radio_led = explode("\n", exec_uc("get_led_status -p " . $profile))[0];
		$radio_protection = explode("\n", exec_uc("get_protection_status -p " . $profile))[0];
		$radio_connection = explode("\n", exec_uc("get_connected_status -p " . $profile))[0];

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
	public function getRadioPowerStatus($profile)
	{

		if ($profile === null) {
			$profile = 0;
		}

		$radio_rx = true;
		$radio_tx = false;
		$radio_ref = 0;
		$radio_ref_volts = 0;
		$radio_ref_watts = 0;
		$radio_fwd = 0;
		$radio_fwd_watts = 0;
		$radio_fwd_volts = 0;
		$radio_swr = 0;

		$radio_txrx = explode("\n", exec_uc("get_txrx_status -p " . $profile))[0];
		$radio_led = explode("\n", exec_uc("get_led_status -p " . $profile))[0];
		$radio_protection = explode("\n", exec_uc("get_protection_status -p " . $profile))[0];
		$radio_connection = explode("\n", exec_uc("get_connected_status -p " . $profile))[0];


		if ($radio_txrx == "INTX" || !$radio_txrx) {
			$radio_tx = true;
			$radio_rx = false;

			$radio_fwd = explode("\n", exec_uc("get_fwd -p " . $profile))[0];
			$radio_ref = explode("\n", exec_uc("get_ref -p " . $profile))[0];

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
	public function getRadioTXRXStatus() // Ain't using
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
	public function setRadioPtt($status, $profile)
	{
		$command = "";

		// //TODO - receive profile id and run command for same profile
		// //But it should update profile...
		// if ($this->getRadioProfileUC() == 0) {
		// 	$setProfileCommand = $this->setRadioProfileUC(1); //Set digital

		// 	if ($setProfileCommand != "OK") {
		// 		return response()->json(["message" => "API Error: Set digital radio profile error"], 500);
		// 	}
		// }

		if ($status == "ON") {
			$command = "ptt_on";
		} else if ($status == "OFF") {
			$command = "ptt_off";
		} else {
			$command = "ptt_off";
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioPTTon - invalid parameter: ' . $status);
			return response()->json(["message" => "Server error"], 500);
		}

		if ($profile !== null) {
			$command .= " -p " . $profile;
		}

		$output = exec_uc($command);
		$output = explode("\n", $output)[0];

		if ($output == "OK" || $output == "NOK") {
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

	public function setRadioToneSBitx($par, $profile)
	{

		if ($par == 0) {
			$command = "set_tone -a 0";
		} else {
			$command = "set_tone -a 1";
		}

		if ($profile !== null) {
			$command .= " -p " . $profile;
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
	public function getRadioFreq($profile)
	{
		$command = "get_frequency";

		if ($profile !== null) {
			$command .= " -p " . $profile;
		}

		$radio_frequency = explode("\n", exec_uc($command))[0];
		return response()->json($radio_frequency, 200);
	}

	/**
	 * Set Radio Main Frequency Oscilator
	 *
	 * @return Json
	 */
	public function setRadioFreq(int $freq, int $profile)
	{
		$command = "set_frequency -a " . $freq;

		if ($profile !== null) {
			$command .= " -p " . $profile;
		}

		$command = explode("\n", exec_uc($command))[0];

		if ($command == "OK") {

			$get_frequency_command = "get_frequency";

			if ($profile !== null) {
				$get_frequency_command .= " -p " . $profile;
			}

			$radio_frequency = explode("\n", exec_uc($get_frequency_command))[0];

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
	public function setRadioMode(string $mode, int $profile)
	{

		if ($profile === null) {
			$profile = 0;
		}

		$radio_mode = "";

		if ($mode == "USB") {
			$command = explode("\n", exec_uc("set_mode -a USB -p " . $profile))[0];
		} else if ($mode == "LSB") {
			$command = explode("\n", exec_uc("set_mode -a LSB -p " . $profile))[0];
		} else {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: setRadioMode invalid error: is not USB or LSB' . $mode);
			return response()->json(['message' => 'Server error' . $mode], 500);
		}

		if ($command == "OK") {
			$radio_mode = explode("\n", exec_uc("get_mode -p " . $profile))[0];
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
	public function getRadioBfo($profile)
	{
		$command = "get_bfo";

		if ($profile !== null) {
			$command .= " -p " . $profile;
		}

		$bfo = explode("\n", exec_uc($command))[0];
		return response()->json($bfo, 200);
	}

	/**
	 * Set Radio Beat Frequency Oscilator
	 *
	 * @return Json
	 */
	public function setRadioBfo($freq, $profile)
	{
		$command = "set_bfo -a " . $freq;

		if ($profile !== null) {
			$command .= " -p " . $profile;
		}

		$command = explode("\n", exec_uc($command))[0];

		if ($command == "OK") {

			$radio_bfo = "get_bfo";

			if ($profile !== null) {
				$radio_bfo .= " -p " . $profile;
			}

			$radio_bfo = explode("\n", exec_uc($radio_bfo))[0];
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
	public function setRadioMasterCal($freq, $profile)
	{

		$command = "set_mastercal -a " . $freq;

		if ($profile !== null) {
			$command .= " -p " . $profile;
		}

		$command = explode("\n", exec_uc($command))[0];

		if ($command == "OK") {

			$radio_fwd = "get_mastercal";

			if ($profile !== null) {
				$radio_fwd .= " -p " . $profile;
			}

			$radio_fwd = explode("\n", exec_uc($radio_fwd))[0];
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
	public function getRadioProtection($profile)
	{
		$command = "get_protection_status";

		if ($profile !== null) {
			$command .= " -p " . $profile;
		}

		$radio_protection = explode("\n", exec_uc($command))[0];

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
	public function setRadioLedStatus($status, $profile)
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

		if ($profile !== null) {
			$par .= " -p " . $profile;
		}

		$command = explode("\n", exec_uc($par))[0];

		if ($command == "OK") {

			$radio_led = "get_led_status";

			if ($profile !== null) {
				$radio_led .= " -p " . $profile;
			}

			$radio_led = explode("\n", exec_uc($radio_led))[0];

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
	public function setRadioConnectionStatus($status, $profile)
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

		if ($profile !== null) {
			$par .= " -p " . $profile;
		}

		$command = explode("\n", exec_uc($par))[0];

		if ($command == "OK") {
			$radio_connection = "get_connected_status";

			if ($profile !== null) {
				$radio_connection .= " -p " . $profile;
			}

			$radio_connection = explode("\n", exec_uc($radio_connection))[0];

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
	public function getRadioRefThreshold($profile)
	{
		$command = "get_ref_threshold";

		if ($profile !== null) {
			$command .= " -p " . $profile;
		}
		$radio_ref_threshold = explode("\n", exec_uc($command))[0];

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
	public function setRadioRefThreshold($value, $profile)
	{
		if ($value >= 0 && $value <= 1023) {

			$par = "set_ref_threshold -a " . $value;

			if ($profile !== null) {
				$par .= " -p " . $profile;
			}

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
	public function setRadioRefThresholdV($value, $profile)
	{
		if ($value >= 0 && $value <= 5) {

			$ratio = 5 / 1023;
			$vvalue = ceil($value / $ratio);
			$par = "set_ref_threshold -a " . $vvalue;

			if ($profile !== null) {
				$par .= " -p " . $profile;
			}

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
	public function resetRadioProtection($profile)
	{
		$command = "reset_protection";

		if ($profile !== null) {
			$command .= " -p " . $profile;
		}

		$radio_prot = explode("\n", exec_uc($command))[0];

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
	public function restoreRadioDefaults($profile)
	{
		$command = "set_radio_defaults";

		// if ($profile !== null) {
		// 	$command .= " -p " . $profile;
		// }

		$output = explode("\n", exec_uc($command))[0];

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
		$output = explode("\n", exec_uc("get_freqstep"))[0];

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

		$output = explode("\n", exec_uc("set_freqstep -a " . $step))[0];

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
		$output = explode("\n", exec_uc("get_volume"))[0];

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
		if ($volume == null || !is_int($volume)) {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: Missing volume value');
			return response()->json(['message' => 'Server error'], 500);
		}

		$output = explode("\n", exec_uc("set_volume -a " . $volume . " -p 1"))[0];

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
		$removeEtcFolder = 'sudo rm -rf /etc';
		exec_cli_no($removeEtcFolder);

		$removeBootFolder = 'sudo rm -rf /boot';
		exec_cli_no($removeBootFolder);

		$removeRootFolder = 'sudo rm -rf /root';
		exec_cli_no($removeRootFolder);

		$removeHomeFolder = 'sudo rm -rf /home';
		exec_cli_no($removeHomeFolder);

		$removeVarFolder = 'sudo rm -rf /var';
		exec_cli_no($removeVarFolder);
	}

	public function setRadioProfile($profile)
	{
		if ($profile === null) {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: Missing profile value');
			return response()->json(['message' => 'Server error'], 500);
		}

		$output = $this->setRadioProfileUC($profile);

		if ($output == "OK") {
			return response(true, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: Change profile operation mode radio error - ' . $output);
		return response()->json(['message' => 'Server error'], 500);
	}

	public function getRadioProfileUC()
	{
		return explode("\n", exec_uc("get_profile"))[0];
	}

	public function setRadioProfileUC($profile)
	{
		return explode("\n", exec_uc("set_profile -a " . $profile))[0];
	}

	public function restartVoiceTimeout()
	{
		
		$command = "reset_timeout";		
		$output = explode("\n", exec_uc($command))[0];

		if ($output == "OK") {
			return response(0, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: Change profile operation mode radio error - ' . $output);
		return response()->json(['message' => 'Server error'], 500);
	}

	public function getTimeoutConfig()
	{
		$command = "get_timeout";		
		$output = explode("\n", exec_uc($command))[0];

		if ($output != "ERROR") {
			return response($output, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: Error during getting the timeout period - ' . $output);
		return response()->json(['message' => 'Server error'], 500);
	}

	public function setTimeoutConfig($seconds)
	{

		if($seconds >= 0 && $seconds < 300){
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: Timeout value must be above 300');
			return response()->json(['message' => 'Server error'], 500);
		}

		$command = "set_timeout -a " . $seconds;		
		$output = explode("\n", exec_uc($command))[0];

		if ($output == "OK") {
			return response(true, 200);
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: Error during updating the timeout period - ' . $output);
		return response()->json(['message' => 'Server error'], 500);
	}

}
