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
        $general = [
            '/ GET' => 'Show this help',
            '/version GET' => 'Show HERMES API version',
            '/login POST' => 'Authenticate user',
            '/unpack/{arg} GET' => 'Unpack inbox message',
        ];

        $user = [
            '/user GET' => 'Show all users',
            '/user/{id} GET' => 'Show a user',
            '/user POST' => 'Create user',
            '/user/{id} POST' => 'Update user',
            '/user/{id}/{mail} DELETE' => 'Delete a user by id and mail',
        ];

        $message = [
            '/message/{id} GET' => 'Show a message',
            '/message/type/{type} GET' => 'Show all messages by type',
            '/message/image/{id} GET' => 'Show image from message',
            '/message POST' => 'Create message',
            '/message/{id} DELETE' => 'Delete a message',
            '/message/uncrypt/{id} POST' => 'Uncrypt message file'
        ];

        $file = [
            '/file/{file} GET' => 'Download, decompress and decrypt a file',
            '/file POST' => 'Upload, compress and crypt a file',
            '/file DELETE' => 'Delete lost files'
        ];

        $system = [
            '/sys/ GET' => 'Show system status',
            '/sys/config GET' => 'Show system configuration',
            '/sys/config POST' => 'Save system configuration',
            '/sys/maillog GET' => 'Show system mail logs',
            '/sys/stations GET' => 'Show system stations',
            '/sys/status GET' => 'Show system status',
            '/sys/uuls GET' => 'UUCP list jobs',
            '/sys/mail/{host}/{id}/{language} DELETE' => 'Kill mail job',
            '/sys/uuk/{host}/{id} DELETE' => 'Kill UUCP job',
            '/sys/uucall GET' => 'UUCP call',
            '/sys/uucall/{uuidhost} GET' => 'UUCP call for host',
            '/sys/uulog GET' => 'UUCP log',
            '/sys/uudebug GET' => 'UUCP debug log',
            '/sys/shutdown GET' => 'System shutdown',
            '/sys/reboot GET' => 'System reboot',
            '/sys/language GET' => 'Get system language',
            '/sys/statistic GET' => 'Get spool statistics',
            '/sys/stop POST' => 'Stop transmission',
        ];

        $caller = [
            '/caller/ GET' => 'Show all schedules',
            '/caller/ POST' => 'Create schedule',
            '/caller/{id} PUT' => 'Update schedule',
            '/caller/{id} GET' => 'Show schedule',
            '/caller/{id} DELETE' => 'Delete schedule'
        ];

        $radio = [
            '/radio/{profile} GET' => 'Get radio status',
            '/radio/power/{profile} GET' => 'Get radio power status',
            '/radio/mode/{mode}/{profile} POST' => 'Set radio mode',
            '/radio/freq/{profile} GET' => 'Get radio frequency',
            '/radio/freq/{freq}/{profile} POST' => 'Set radio frequency',
            '/radio/bfo/{profile} GET' => 'Get radio BFO',
            '/radio/bfo/{freq}/{profile} POST' => 'Set radio BFO',
            '/radio/led/{status}/{profile} POST' => 'Set radio LED status',
            '/radio/ptt/{status}/{profile} POST' => 'Set radio PTT',
            '/radio/tone/{par} POST' => 'Set radio tone',
            '/radio/tone/sbitx/{par}/{profile} POST' => 'Set radio SBitx tone',
            '/radio/mastercal/{freq}/{profile} POST' => 'Save radio MasterCal',
            '/radio/protection/{profile} GET' => 'Get radio protection',
            '/radio/connection/{status}/{profile} POST' => 'Set radio connection status',
            '/radio/refthreshold/{profile} GET' => 'Get radio reference threshold',
            '/radio/refthreshold/{value}/{profile} POST' => 'Set radio reference threshold',
            '/radio/refthresholdv/{value}/{profile} POST' => 'Set radio reference threshold V',
            '/radio/protection/{profile} POST' => 'Reset radio protection',
            '/radio/default/{profile} POST' => 'Restore radio defaults',
            '/radio/step GET' => 'Get radio step',
            '/radio/step/{step} POST' => 'Update radio step',
            '/radio/volume GET' => 'Get radio volume',
            '/radio/volume/{volume} POST' => 'Change radio volume',
            '/radio/erasesdcard GET' => 'Erase SD card',
            '/radio/profile/{profile} POST' => 'Set radio profile',
            '/radio/voice/timeout POST' => 'Restart voice timeout',
            '/radio/voice/timeout/config GET' => 'Get voice timeout config',
            '/radio/voice/timeout/config/{seconds} POST' => 'Set voice timeout config',
            '/radio/bitrate GET' => 'Get radio bitrate',
            '/radio/snr GET' => 'Get radio SNR'
        ];

        $geolocation = [
            '/geolocation/calibration GET' => 'Start GPS calibration',
            '/geolocation/status GET' => 'Get GPS storing status',
            '/geolocation/status/{status} POST' => 'Set GPS storing status',
            '/geolocation/files GET' => 'Get stored location files from path',
            '/geolocation/files/all GET' => 'Get all stored location files',
            '/geolocation/file/{name} GET' => 'Get stored location file by name',
            '/geolocation/coordinates GET' => 'Get current coordinates',
            '/geolocation/interval GET' => 'Get GPS storing interval',
            '/geolocation/interval/{seconds} POST' => 'Set GPS storing interval',
            '/geolocation/email GET' => 'Get GPS email',
            '/geolocation/email/{email} POST' => 'Set GPS email',
            '/geolocation/filetime GET' => 'Get GPS file range time',
            '/geolocation/filetime/{seconds} POST' => 'Set GPS file range time',
            '/geolocation/delete DELETE' => 'Delete stored files',
            '/geolocation/sos GET' => 'SOS emergency'
        ];

        $frequency = [
            '/frequency GET' => 'Show frequencies',
            '/frequency/{id} GET' => 'Show frequency',
            '/frequency/alias/{alias} GET' => 'Show frequency by alias',
            '/frequency/{id} PUT' => 'Update frequency'
        ];

        $customerrors = [
            '/customerrors GET' => 'Get custom errors',
            '/customerrors DELETE' => 'Delete all custom errors',
            '/customerrors/{id} DELETE' => 'Delete custom error by id',
            '/customerrors POST' => 'Save a custom error'
        ];

        $wifi = [
            '/wifi GET' => 'Get WiFi configurations',
            '/wifi POST' => 'Save WiFi configurations',
            '/wifi/mac/filter POST' => 'Set WiFi MAC filter',
            '/wifi/mac/address POST' => 'Add WiFi MAC address',
            '/wifi/mac/address/{address} DELETE' => 'Delete WiFi MAC address'
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
            'frequency' => $frequency,
            'customerrors' => $customerrors,
            'wifi' => $wifi
			]
		];

		return json_encode($help);
    }
}