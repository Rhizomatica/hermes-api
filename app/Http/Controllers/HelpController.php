<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */

	public function showHelpMain()
	{
		//TODO - Doc. Auto Generator
		$general = [
			'/ GET' => 'this help',
			'version GET' => 'HERMES API version',
			'login POST' => 'User authenticate HERMES'
		];

		$user = [
			'user GET' => 'Show all users',
			'user/{id} GET' => 'Show a user',
			'user POST' => 'Create User',
			'user/{id} PUT' => 'Update User',
			'user/{id} DELETE' => 'Delete a user'
		];

		$message = [
			'message/{id} GET' => 'Show a message',
			'messages/type/{type} GET' => 'Show all messages by type',
			'image/{id} GET' => 'Show image from message',
			'message POST' => 'Create message',
			'message/{id} DELETE' => 'Delete a message',
			'message/uncrypt/{id} POST' => 'Uncrypt message file'
		];

		$file = [
			'file/{file} GET' => 'download, decompress and decrypt a file',
			'file POST' => 'upload, compress and crypt a file',
			'file DELETE' => 'Delete file'
		];

		$system = [
			'sys/ GET' => 'Show system status',
			'sys/config GET' => 'Show system configuration',
			'sys/config POST' => 'Save system configuration',
			'sys/maillog GET' => 'Show system mail logs',
			'sys/stations GET' => 'Show system stations',
			'sys/status GET' => 'Show system status', 
			'sys/uuls GET' => 'UUCP list jobs',
			'sys/mail/{host}/{id}/{language} DELETE' => 'Kill mail job', //TODO - Improve desc.
			'sys/uuk/{host}/{id} DELETE' => 'Kill UUCP job',
			'uucall GET' => 'UUCP call', //TODO - CALLER?
			'uucall/{uuidhost} GET' => 'UUCP call for host', 
			'uucall/{uuidhost} GET' => 'UUCP call for host',
			'sys/uulog GET' => 'UUCP log',
			'sys/uudebug GET' => 'UUCP debug log',
			'sys/shutdown GET' => 'System shutdown', //TODO - review desc.
			'sys/reboot GET' => 'System reboot',
			'sys/reboot GET' => 'Show system language'
		];

		$caller = [ 
			'caller/ GET' => 'Show all schedules',
			'caller/ POST' => 'Create schedule',
			'caller/{id} PUT' => 'Update schedule',
			'caller/{id} GET' => 'Show schedule',
			'caller/{id} DELETE' => 'Delete schedule'
		];

		$radio = [
			'radio/status GET' =>  'get radio status',
			'radio/power GET' =>  'get radio power status',
			'radio/mode/{mode} POST' => 'set radio mode',
			'radio/freq GET' => 'get radio freq',
			'radio/freq/{freq (in hz)} POST' => 'set radio freq (in hz)',
			'radio/bfo GET' => 'get Radio Freq',
			'radio/bfo/{freq in hz} POST' => 'set radio bfo (in hz)',
			'radio/led/{status} POST' => 'set radio LED status',
			'radio/ptt/{status} POST' => 'Radio post test tone', //TODO - Check
			'radio/tone/{par} POST' => 'Radio set tone',
			'radio/mastercal{freq (in hz)} POST' => 'Save radio MasterCal ',
			'radio/protection GET' => 'get radio MasterCal',
			'radio/connection/{status} POST' => 'get radio ConnectionStatus',
			'radio/refthreshold GET' => 'get radio ref. threshold',
			'radio/refthreshold POST' => 'Set radio ref. threshold',
			'radio/refthresholdv POST' => 'Set radio ref. thresholdV',
			'radio/protection POST' => 'Reset radio protection',
			'radio/default POST' => 'Set radio default'
		];

		$geolocation = [
			'geolocation/calibration/ GET' => 'GPS calibration'
		];

		$frequency = [
			'frequency GET' => 'Show frequencies',
			'frequency/{id} GET' => 'Show frequency',
			'frequency/alias/{alias} GET' => 'Show frequency by alias',
			'frequency/{id} PUT' => 'Update frequency'
		];

		$help = (object) [
			'title' => 'Hermes API RESUME',
			'site' => 'https://hermes.radio',
			'url' => env('APP_URL') . '/api',
			'endpoints' => (object) [
				'general' => $general,
				'user' => $user,
				'message' => $message,
				'file' => $file,
				'system' => $system,
				'caller' => $caller,
				'radio' => $radio,
				'geolocation' => $geolocation,
				'frequency' => $frequency
			]
		];

		return json_encode($help);
	}
}