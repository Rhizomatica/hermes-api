<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\System;
use App\Message;


class SystemController extends Controller
{

	public function getSysConfig()
	{
		$system = System::first();
		$system->nodename = explode("\n", (string) exec_cli("cat /etc/uucp/config|grep nodename|cut -f 2 -d \" \""))[0];
		return response()->json($system, 200);
	}

	public function setSysConfig(Request $request)
	{
		if ($request->all()) {

			if (System::select()->update($request->all())) {
				return response()->json($request->all(), 200);
			}

			(new ErrorController)->saveError(static::class, 500, 'API Error: setSysConfig can not update');
			return response()->json(['message' => 'Server error'], 500);
		}

		(new ErrorController)->saveError(static::class, 500, 'API Error: setSysConfig does not have request data');
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * set gw schedule
	 *
	 * @return string
	 */
	public function setSysGwSched(Request $request)
	{
		if ($request->all()) {
			return response()->json(['message' => 'setSysGw TODO'], 200);
		}

		(new ErrorController)->saveError(static::class, 500, 'API Error: setSysConfig does not have request data');
		return response()->json(['message' => 'Server error'], 500);
	}

	/**
	 * Get system status
	 *
	 * @return Table
	 */
	public function getSysStatus()
	{
		$uname = explode("\n", (string) exec_cli("uname -n"))[0];
		$piduu = explode("\n", (string) exec_cli("ls  /lib/systemd/system/uucp.socket"))[0];
		$piduuardop = explode("\n", (string) exec_cli("pgrep -x uuardopd"))[0];
		$pidmodem = explode("\n", (string) exec_cli("pgrep -x VARA.exe"))[0];
		$pidradio = explode("\n", (string) exec_cli("pgrep -x bitx_controller"))[0];
		$nodename = explode("\n", (string) exec_cli("cat /etc/uucp/config|grep nodename|cut -f 2 -d \" \""))[0];
		$pidhmp = explode("\n", (string) exec_cli("pgrep -x iwatch"))[0];
		// $piddb = explode("\n", exec_cli("pgrep -x mariadbd"))[0];
		$pidpf = explode("\n", (string) exec_cli("pgrep -x master"))[0];
		// $pidvnc = explode("\n", exec_cli("pgrep -x Xtigervnc"))[0];
		$wifiessid = explode("\n", (string) exec_cli("cat /etc/hostapd/hostapd.conf | grep ssid | cut -c6-"))[0];
		// $wifich = explode("\n", exec_cli("cat /etc/hostapd/hostapd.conf | grep channel| cut -c9-"))[0];
		$ip = explode("\n", (string) exec_cli('/sbin/ifconfig | sed -En \'s/127.0.0.1//;s/.*inet (addr:)?(([0-9]*\.){3}[0-9]*).*/\2/p\''));
		array_pop($ip);
		$disk_free = explode("\n", (string) exec_cli("df  / | grep -v Filesystem | awk '{print $4}'"))[0];
		$interfaces = explode("\n", (string) exec_cli('ip r'));
		array_pop($interfaces);
		// $memory = explode(" ", exec_cli("free --mega| grep Mem | awk '{print ($2\" \"$3\" \"$4)}'"));
		// $phpmemory = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage() / 1024 / 1024, 2) . 'MB';

		$status = [
			'status' => $piduu &&  $piduuardop && $pidmodem &&  $pidradio && $pidhmp && $piduu && $pidpf,
			'uname' => $uname,
			'nodename' => $nodename,
			'name' => env('HERMES_NAME'),
			'network' => env('HERMES_NETWORK'),
			'domain' => env('HERMES_DOMAIN'),
			'gateway' => env('HERMES_GATEWAY'),
			'ip' => $ip,
			'interfaces' => $interfaces,
			'wifiessid' => $wifiessid ?: false,
			// 'wifich' => $wifich ? $wifich : false,
			'interfaces' => $interfaces,
			// 'piduu' => $piduu ? $piduu : false,
			'piduuardop' => $piduuardop ?: false,
			// 'pidmodem' => $pidmodem ? $pidmodem : false,
			// 'pidradio' => $pidradio ? $pidradio : false,
			// 'pidhmp' => $pidhmp ? $pidhmp : false,
			// 'piddb' => $piddb ? $piddb : false,
			// 'pidpf' => $pidpf ? $pidpf : false,
			// 'memtotal' => $memory[0] . "MB",
			// 'memused' => $memory[1] . "MB",
			// 'memfree' => explode("\n", $memory[2])[0] . "MB", //Wrong
			'diskfree' => $disk_free ?: false
		];

		return response()->json($status, 200);
	}

	/**
	 * Get all Stations from uucp
	 *
	 * @return stations
	 */
	public function getSysStations()
	{
		$command = "egrep -v '^\s*#' /etc/uucp/sys | grep system | cut -f 2 -d \" \"";
		$output = exec_cli($command);
		$sysnames = explode("\n", (string) $output);

		$command2 = "egrep -v '^\s*#' /etc/uucp/sys | grep alias | cut -f 2 -d \" \"";
		$output2 = exec_cli($command2);
		$sysnames2 = explode("\n", (string) $output2);

		$sysnameslist = [];

		for ($i = "0"; $i < count($sysnames); $i++) {
			if (!empty($sysnames[$i])) {
				// if (empty ($sysnames2[$i])){
				$sysnameslist[]  =  [
					'id' => $i,
					'name' => $sysnames[$i],
					'alias' => $sysnames2[$i],
				];
			}
		}

		return response()->json($sysnameslist, 200);
	}

	/**
	 * Get transmission spool
	 *
	 * @return Json
	 */
	public function sysGetSpoolList()
	{
		$command = 'uustat -a| grep -v uuadm | grep -v sudo | grep -v bash | grep -v "\-C"';
		$output = exec_cli($command);
		$output = explode("\n", (string) $output);
		$spool = [];

		for ($i = "0"; $i < count($output); $i++) {
			if (!empty($output[$i])) {
				$fields = explode(" ", $output[$i]);
				$type = $fields[6];
				// should we also treat rmail?

				if ($type == "crmail" || str_contains($type, ".hmp") || str_contains($type, "dec_sensors")) {
					$count = count($fields);
					$emails = [];
					$email_info = [];
					$size = 0;
					$uuid_host = explode(".", $fields[0])[0];
					$uuid = explode(".", $fields[0])[1];


					if ($type == "crmail") {
						// test if spool is a email
						// handle when is a multiple email

						if ($count > 11) {
							for ($j = 7; $j < $count - 3; $j++) {
								$emails[] = $fields[$j];
							}
							$size = $fields[$count - 2];
						}
						// handle when is a simple email
						else {
							$size = $fields[9];
							$emails[] = $fields[7];
						}

						$cfile_path = env('HERMES_UUCP') . '/' .  $uuid_host . '/C./C.' . $uuid;
						$dfile_id = explode(" ", (string) exec_cli('sudo cat ' . $cfile_path))[1];
						$dfile_path = env('HERMES_UUCP') . '/' . $uuid_host . '/D./' . $dfile_id;
						$dfile =  exec_cli('sudo xzcat ' . $dfile_path . '| head -1');
						$email_info['from'] = explode(" ", explode("\n", (string) $dfile)[0])[1];
						$email_info['from_date_week'] = explode(" ", explode("\n", (string) $dfile)[0])[3];
						$email_info['from_date_month'] = explode(" ", explode("\n", (string) $dfile)[0])[4];
						$email_info['from_date_day'] = explode(" ", explode("\n", (string) $dfile)[0])[5];
						$email_info['from_date_year'] = explode(" ", explode("\n", (string) $dfile)[0])[7];
						$email_info['from_date_time'] = explode(" ", explode("\n", (string) $dfile)[0])[6];
					}

					// HMP
					if (str_contains($type, ".hmp")) {
						$size = explode("(", $fields[7])[1];
						$emails = null;
					}

					if ($type == "dec_sensors") {
						$size = explode("(", $fields[11])[0];
						$emails = null;
						$messageID = null;
						$message = null;
					} else {
						$messageID = $this->getMessageIDFromUuidhost($fields[10]);
						$message = null;
					}

					if (intval($messageID)) {
						$message = $this->getMessageFromDataBase($messageID);
					}

					$spool[]  =  [
						'uuidhost' => $uuid_host,
						'uuiduucp' => $uuid,
						'dest' => $fields[1],
						'user' => $fields[2],
						'date' => $fields[3],
						'time' => $fields[4],
						'type' => $type == "crmail" ? "Mail" : "HMP",
						//'size' => $fields[5] == "Executing" ? $fields[9] : explode("(",$fields[7])[1],
						'size' => intval($size),
						'destpath' =>  $fields[6] == "crmail" ? null : $fields[10],
						'emails' =>  $emails,
						'emailinfo' =>  $email_info,
						'messageId' => $message != null ? $message->id : null,
						'messageName' => $message != null ? $message->name : null,
						'messageFile' => $message != null ? $message->file : null,
						'messageMimeType' => $message != null ? $message->mimetype : null,
						'messageSecure' => $message != null ? $message->secure : null
					];
				}
			}
		}

		if (sizeof($spool) >= 1) {
			return response()->json($spool, 200);
		}

		return response()->json(null, 200);
	}

	public function getMessageIDFromUuidhost($destpath)
	{

		if (!$destpath) {
			return null;
		}

		$delimiter1 = "_";
		$delimiter2 = ".";

		$parts = explode($delimiter1, (string) $destpath);
		$result = [];

		foreach ($parts as $part) {
			$subparts = explode($delimiter2, $part);
			$result = array_merge($result, $subparts);
		}

		if (sizeof($result) >= 2 && is_numeric($result[1])) {
			return intval($result[1]);
		}

		return null;
	}

	public function getMessageFromDataBase($messageID)
	{
		$message = Message::find($messageID);

		if (!$message) {
			return null;
		}

		return $message;
	}

	/**
	 * kill uucp mail jobs with mailkill returning  a email
	 *
	 * @return json message
	 */
	public function uucpKillMail($host, $id, $language)
	{
		$command = 'sudo mailkill.sh ' . $language . ' gui ' . $host . '.' . $id;
		ob_start();
		system($command, $return_var);
		$output = ob_get_contents();
		ob_end_clean();

		if ($return_var == 0) {
			return response()->json("uucp job killed: " . $host . '.' . $id, 200);
		}

		(new ErrorController)->saveError(static::class, 404, 'API Error: Job not found');
		return response()->json("Not found", 404);
	}

	/**
	 * kill uucp jobs
	 *
	 * @return json message
	 */
	public function uucpKillJob($host, $id)
	{
		$command = 'sudo uustat -k ' . $host . '.' . $id;
		$output = exec_cli($command) or die;
		return response()->json("uucp job killed: " . $host . '.' . $id, 200);
	}

	/**
	 * Force uucp common jobs
	 *
	 * @return json message
	 */
	public function uucpCall()
	{
		$command = 'sudo uucico -r1 ';
		$output = exec_cli($command);
		return response($output, 200);
	}

	/**
	 * Force uucp common job
	 *
	 * @return json message
	 */
	public function uucpCallForHost($uuidhost)
	{
		// $command = 'sudo uucico -r1 ' ;
		$command = 'sudo uucico -S ' . $uuidhost; //TODO - test
		$output = exec_cli($command);
		return response($output, 200);
	}

	/**
	 * system reboot
	 *
	 * @return json message
	 */
	public function sysReboot()
	{
		$command = "sudo reboot";
		$output = exec_cli($command);
		return json_encode("rebooted");
	}

	/**
	 * system shutdown
	 *
	 * @return json message
	 */
	public function sysShutdown()
	{
		// set led status OFF on cabinet
		exec_uc("set_led_status -a OFF -p 0");
		exec_uc("set_led_status -a OFF -p 1");
		sleep(1);

		// linux shutdown
		$command = "sudo halt";
		exec_cli($command);
		return json_encode("halted");
	}

	/**
	 * system logs email
	 *
	 * @return json maillog
	 */
	public function sysLogMail()
	{
		$command = "sudo tail /var/log/mail.log -n 1000| sort -n ";
		$output = exec_cli($command);
		$output = explode("\n", (string) $output);
		ob_clean();
		ob_start();

		return response()->json($output, 200);
	}

	/**
	 * system logs uucp
	 *
	 * @return json uucplog
	 */
	public function sysLogUucp()
	{
		$command = "sudo uulog -n 1000 | sort -n ";
		$output = exec_cli($command);
		$output = explode("\n", (string) $output);
		ob_clean();
		ob_start();

		return response()->json($output, 200);
	}

	/**
	 * system logs uucp Debug
	 *
	 * @return json uucpDebuglog
	 */
	public function sysDebUucp()
	{
		$command = "sudo uulog -D -n 1000 | sort -n ";
		$output = exec_cli($command);
		$output = explode("\n", (string) $output);
		ob_clean();
		ob_start();

		return response()->json($output, 200);
	}

	public function language()
	{
		return env('APP_LANGUAGE');
	}

	public function getSpoolStatistics()
	{
		$command = 'uustat -a| grep -v uuadm | grep -v sudo | grep -v bash | grep -v "\-C"';
		$output = exec_cli($command);
		$output = explode("\n", (string) $output);

		$totalCount = 0;
		$totalBytes = 0;

		$succeededCount = 0;
		$succeededBytes = 0;

		$retriesCount = 0;
		$retriesBytes = 0;


		for ($i = "0"; $i < count($output); $i++) {
			if (!empty($output[$i])) {
				$fields = explode(" ", $output[$i]);
				$type = $fields[6];

				$bytesPositionInOutput = $this->getBytesFieldPositionInOutputArray($type, $fields);

				if (!empty($fields[$bytesPositionInOutput])) {

					//SUM TOTALS
					$totalBytes += $fields[$bytesPositionInOutput]; //(4102 //TODO - remove "(" if HMP
					$totalCount += 1;
				}
				else {
					continue;
				}

				//TODO - Check possible status (Executing, Sending....)
				//TODO - CHECK HOW TO KONW IF IT'S SUCCEEDED

				//SUM SUCCEEDED
				//5 position status in output array
				if ($fields[5] == "DONE????????") {
					$succeededCount += 1;
					$succeededBytes += $fields[$bytesPositionInOutput]; //(4102 //TODO - remove "(" if HMP
				}

				//CHECK AND SUM RETRIES
				if ($fields[5] == "Executing" ||$fields[5] == "Sending") {

					//Check retries 
					//TODO - Check which status should verify the retries
					$retries = $this->checkRetriesByID($output, $fields[0]);
					$retriesCount += $retries[0];
					$retriesBytes += $retries[1]; //(4102 //TODO - remove "(" if HMP
					//******************
				}

				// $fields = [
				// 	"PU2UIT.NWdWupMAAlEy",
				// 	"PU2UIT",
				// 	"root",
				// 	"07-14",
				// 	"20:59",
				// 	"Executing",
				// 	"dec_sensors",
				// 	"-g",
				// 	"-e",
				// 	"test@domain.com",
				// 	"-f",
				// 	"root@estacao3.hermes.radio",
				// 	"(sending",
				// 	"99",
				// 	"bytes)"
				// ]
			}
		}


		$statistics[]  =  [
			'totalCount' => $totalCount,
			'totalBytes' => $totalBytes,
			'succeededCount' => $succeededCount,
			'succeededBytes' => $succeededBytes,
			'retriesCount' => $retriesCount,
			'retriesBytes' => $retriesBytes
		];

		return response()->json($statistics, 200);
	}

	public function getBytesFieldPositionInOutputArray($type, $fields){
		//CHECK KIND OF ITEM AND DEFINE ARRAY POSITION
		$bytesPositionInOutput = 13; //13 = Default

		// if($type == '???'){ //TODO verify difference for 11 (qual seria o 11??) GPS?
		// 	$bytesPositionInOutput = 11;
		// }

		if($type == '.hmp'){
			$bytesPositionInOutput = 7;
		}

		if ($type == "crmail") {
			// test if spool is a email
			// handle when is a multiple email

			$count = count($fields);

			//TODO - Verify
			if ($count > 11) {
				$bytesPositionInOutput = $fields[$count - 2];
			}
			// handle when is a simple email
			else {
				$bytesPositionInOutput = 9;
			}
		}

		return $bytesPositionInOutput;
	}

	public function checkRetriesByID($spoolList, $idOrigin)
	{

		$retriesCount = 0;
		$retriesBytes = 0;

		for ($i = 0; $i < $spoolList; $i++) {

			$id = explode(" ", (string) $spoolList[$i])[0];
			$bytes = explode(" ", (string) $spoolList[$i])[13]; //TODO - Confirmar coluna

			if ($id == $idOrigin) {
				$retriesCount += 1;
				$retriesBytes += $bytes;
			}
		}

		$retries = [
			$retriesCount,
			$retriesBytes
		];

		return $retries;
	}
}
