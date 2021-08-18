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
        $manual = [ 'Rhizo Hermes API' => 'V0.8 -  default page and help',
            '--------USERS---------------' => '----------------------------------------',
            'user/id GET DELETE PUT' => 'users, get, delete and update',
            'users get' => 'users, get, delete ',
            '--------MESSAGES------------' => '----------------------------------------',
            'message/id GET DELETE PUT'=> 'messages, get, delete and update ',
            'message/id POST' => 'Create message, return 200 - message',
            //'/message/render/id GET' => 'return 200 - generate message id',
            'message/list get' => 'return 200 - all messages',
            'file POST' => 'file, create',
            'file/id GET DELETE PUT ' => 'file, get, delete and update',
            '--------SYS---------------' => '----------------------------------------',
            'GET sys/help' => 'showHelpSys()',
            'GET sys/status' => 'sysGetStatus()',
            'GET sys/getnodename' => 'sysGetNodeName()',
            'GET sys/stations' => 'sysGetStations()',
            'GET sys/spoollist' => 'sysGetSpoolList()',
            'GET sys/shutdown' => 'cli: sudo halt',
            'GET sys/reboot' => 'reboot',
            'GET sys/ls' => 'showFiles',
            'sys/erasequeue' => 'TODO not running now',
            'sys/decrypt' => 'TODO decrypt ',
            'GET sys/viewlog()' => ' sysGetLog()',
            'sys/listfiles()' => 'TODO listfiles',
            'sys/viewjob()' => 'TODO viewjob',
            '--------SYS UUCP---------' => '----------------------------------------',
            'POST sys/uur' => 'UUCP rejuvenate ID',
            'POST sys/uuk' => 'UUCP kill ID',
            'POST sys/uuka' => 'UUCP killall jobs',
            'POST sys/uuls' => 'UUCP list jobs',
            '--------USERS------------' => '-----------------------------------',
            'user POST' => 'UserController@create',
            'user/{id} GET' => 'UserController@showOneUser',
            'user/{id} PUT' => 'UserController@update',
            'user/{id} DELETE' => 'UserController@delete',
            'users GET' => 'UserController@showAll',
            '--------MESSAGE----------' => '---------------------------------',
            'message POST' => 'MessageController@create',
            'message/{id} GET' => 'MessageController@showOneUser',
            'message/{id} PUT' => 'MessageController@update',
            'message/{id} DELETE' => 'MessageController@delete',
            'messages GET' => 'MessageController@showAll',
            '--------RADIO------------' => '-----------------------------------',
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
