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
			'/version GET' => 'HERMES API version',
			'/login POST' => 'User authenticate HERMES'
		];

		$user = [
			'/ GET' => 'this help',
			'/version GET' => 'HERMES API version',
			'/login POST' => 'User authenticate HERMES'
		];

		$manual = (object) [
			'title' => 'Hermes API RESUME',
			'site' => 'https://hermes.radio',
			'url' => env('APP_URL').'/api',
			'endpoints' => (object) [
				'general' => $general,
				'user' => $user,
				// 'file' => $file,
				// 'system' => $system,
				// 'caller' => $caller,
				// 'radio' => $radio,
				// 'geolocation' => $geolocation,
				// 'frequency' => $frequency
			]
		];

		return json_encode($manual);
	}
}




		// 'sys/status GET' => 'system status',
		// 	'sys/getnodename GET' => 'node name ( radio CALLSIGN)',
		// 	'sys/stations GET' => 'show available stations',
		// 	'sys/maillog GET' => 'get mail log',
		// 	'sys/restart GET' => 'restart',
		// 	'sys/reboot GET' => 'reboot',
		// 	'sys/sensors GET' => 'sensors',
		// 	'sys/shutdown GET' => 'cli: shutdown',
		// 	'--------SYS-UUCP-----------' => '----------------------------------------',
		// 	'sys/uuk/id POST' => 'UUCP kill ID',
		// 	'sys/uuka POST' => 'UUCP killall jobs',
		// 	'sys/uuls GET' => 'UUCP list jobs',
		// 	'sys/uulog GET' => 'UUCP log',
		// 	'sys/uudebug GET' => 'UUCP debug log',
		// 	'--------USERS--------------' => '----------------------------------------',
		// 	'user POST' => 'Create user and email',
		// 	'user/{id} GET' => 'Show a user',
		// 	'user/{id} PUT' => 'Update User',
		// 	'user/{id} DELETE' => 'Delete a user',
		// 	'users GET' => 'Show all users',
		// 	'--------MESSAGES-----------' => '----------------------------------------',
		// 	'message POST' => 'Create message',
		// 	'message/{id} GET' => 'Show a message',
		// 	'message/{id} PUT' => 'Update a message',
		// 	'message/{id} DELETE' => 'Delete a message',
		// 	'messages GET' => 'Show all messages',
		// 	'--------INBOX--------------' => '----------------------------------------',
		// 	'inbox/{id} GET' => 'Show a message',
		// 	'inbox GET' => 'Show all messages',
		// 	'unpack/{id} PUT' => 'unpack message',
		// 	'--------FILES--------------' => '----------------------------------------',
		// 	'file POST' => 'upload, compress and crypt a  file',
		// 	'file/{id} GET' => 'download, decompress and decrypt a file',
		// 	'--------RADIO--------------' => '----------------------------------------',
		// 	'radio/status GET' =>  'get radio status',
		// 	'radio/mode GET' => 'get radio mode',
		// 	'radio/mode/{mode} POST' => 'set radio mode',
		// 	'radio/freq GET' => 'get radio freq',
		// 	'radio/freq/{freq in hz} POST' => 'set radio freq',
		// 	'radio/bfo GET' => 'get Radio Freq',
		// 	'radio/bfo/{freq in hz} POST' => 'set radio bfo',
		// 	'radio/fwd GET' => 'get radio fwd',
		// 	'radio/fwd/{freq in hz} POST' => 'set radio fwd',
		// 	'radio/led GET' => 'get radio LED Status',
		// 	'radio/led/{status} POST' => 'set radio LED status',
		// 	'radio/ref GET' => 'get radio ref',
		// 	'radio/txrx GET' => 'get radioTxrx',
		// 	'radio/mastercal GET' => 'get radio MasterCal',
		// 	'radio/mastercal POST' => 'get radio MasterCal',
		// 	'radio/protection GET' => 'get radio MasterCal',
		// 	'radio/connection GET' => 'get radio ConnectionStatus',
		// 	'radio/connection/{status} POST' => 'get radio ConnectionStatus'