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
        $manual = [ 'Hermes API RESUME' => 'V1.0 -  default page and help',
            '---------------------------' => '----------------------------------------',
            '--------SITE---------------' => 'https://hermes.radio',
            '---------------------------' => '----------------------------------------',
            '--------SYS----------------' => '----------------------------------------',
            'sys/help GET' => 'showHelpSys()',
            'sys/status GET' => 'sysGetStatus()',
            'sys/getnodename GET' => 'sysGetNodeName()',
            'sys/stations GET' => 'sysGetStations()',
            'sys/spoollist GET' => 'sysGetSpoolList()',
            'sys/restart GET' => 'reboot',
            'sys/reboot GET' => 'reboot',
            'sys/shutdown GET' => 'cli: sudo halt',
            'sys/viewlog()' => ' sysGetLog()',
            '--------SYS-UUCP-----------' => '----------------------------------------',
            'sys/uur/id POST' => 'UUCP rejuvenate ID',
            'sys/uuk/id POST' => 'UUCP kill ID',
            'sys/uuka POST' => 'UUCP killall jobs',
            'sys/uuls GET' => 'UUCP list jobs',
            'sys/uulog GET' => 'UUCP log',
            'sys/uudebug GET' => 'UUCP debug log',
            '--------USERS--------------' => '----------------------------------------',
            'user POST' => 'UserController@create',
            'user/{id} GET' => 'UserController@showOneUser',
            'user/{id} PUT' => 'UserController@update',
            'user/{id} DELETE' => 'UserController@delete',
            'users GET' => 'UserController@showAll',
            '--------MESSAGES-----------' => '----------------------------------------',
            'message POST' => 'MessageController@create',
            'message/{id} GET' => 'MessageController@showOneUser',
            'message/{id} PUT' => 'MessageController@update',
            'message/{id} DELETE' => 'MessageController@delete',
            'messages GET' => 'MessageController@showAll',
            '--------FILES--------------' => '----------------------------------------',
            'file POST' => 'MessageController@uploadFile',
            'file/{id} GET' => 'MessageController@downloadFile',
            'file/{id} DELETE' => 'MessageController@delete',
            '--------RADIO--------------' => '----------------------------------------',
            'radio/help GET' => 'HelpController@ShowHelpRadio',
            'radio/status GET' =>  'RadioController@getRadioStatus',
            'radio/mode GET' => 'RadioController@getRadioMode',
            'radio/mode/{mode} POST' => 'RadioController@setRadioMode',
            'radio/freq GET' => 'RadioController@getRadioFreq',
            'radio/freq/{freq in hz} POST' => 'RadioController@setRadioFreq',
            'radio/bfo GET' => 'RadioController@getRadioFreq',
            'radio/bfo/{freq in hz} POST' => 'RadioController@setRadioBfo',
            'radio/fwd GET' => 'RadioController@getRadioFwd',
            'radio/fwd/{freq in hz} POST' => 'RadioController@setRadioFwd',
            'radio/led GET' => 'RadioController@getRadioLedStatus',
            'radio/led/{status} POST' => 'RadioController@setRadioLedStatus',
            'radio/ref GET' => 'RadioController@getRadioRef',
            'radio/txrx GET' => 'RadioController@getRadioTxrx',
            'radio/mastercal GET' => 'RadioController@getRadioMasterCal',
            'radio/mastercal POST' => 'RadioController@getRadioMasterCal',
            'radio/protection GET' => 'RadioController@getRadioMasterCal',
            'radio/bypass GET' => 'RadioController@getRadioBypassStatus',
            'radio/bypass/{status} POST' => 'RadioController@getRadioBypassStatus'
        ];
    return $manual;
    }

}
