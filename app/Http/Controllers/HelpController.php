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
        $manual = [ 'Rhizo Hermes API' => 'V0.0.3 -  default page and help',
            'License and Copyrights' => 'gplv2, some rights reserved',
            '-----------------------' => '----------------------------------------',
            '/help' => 'TODO manual',
            '/sys/help' => 'TODO manual',
            '--------DATA---------------' => '----------------------------------------',
            '/user/id GET DELETE PUT' => 'users, get, delete and update',
            '/users get' => 'users, get, delete ',
            '/message/id GET DELETE PUT'=> 'messages, get, delete and update ',
            '/message/id POST' => 'Create message, return 200 - message',
            //'/message/render/id GET' => 'return 200 - generate message id',
            '/message/list get' => 'return 200 - all messages',
            '/file POST' => 'file, create',
            '/file/id GET DELETE PUT ' => 'file, get, delete and update'
        ];
    return $manual;
    }


    public function showHelpSys()
    {
        $manual = [ 'Rhizo Hermes API' => 'V0.0.3 -  Sys page, help',
            'License and Copyrights' => 'gplv2, some rights reserved',
            '--------------------------' => '----------------------------------------',
            'GET sys/help' => 'showHelpSys()',
            'GET sys/status' => 'sysGetStatus()',
            'GET sys/getnodename' => 'sysGetNodeName()',
            'GET sys/stations' => 'sysGetStations()',
            'GET sys/spoollist' => 'sysGetSpoolList()',
            'GET sys/shutdown' => 'cli: sudo halt',
            'GET sys/reboot' => 'TODO reboot',
            '----------' => '---',
            'GET sys/ls' => 'showFiles',
            'sys/erasequeue' => 'TODO not running now',
            'sys/kill_job' => 'TODO exec_get_spool_list()',
            'sys/decrypt' => 'TODO decrypt ',

            'GET sys/viewlog()' => ' sysGetLog()',
            'sys/listfiles()' => 'TODO listfiles',
            'sys/viewjob()' => 'TODO viewjob',
        ];
    return $manual;
    }

    public function showHelpUser()
    {
        $manual = [ 'Rhizo Hermes API' => 'V0.0.3 -  Sys page, help',
            'License and Copyrights' => 'gplv2, some rights reserved',
            '----------------------' => '----------------------------------------',
            'user POST' => 'UserController@create',
            'user/{id} GET' => 'UserController@showOneUser',
            'user/{id} PUT' => 'UserController@update',
            'user/{id} DELETE' => 'UserController@delete',
            'users GET' => 'UserController@showAll',
        ];
    return $manual;
    }

    public function showHelpMessage()
    {
        $manual = [ 'Rhizo Hermes API' => 'V0.0.3 -  Sys page, help',
            'License and Copyrights' => 'gplv2, some rights reserved',
            '----------------------' => '----------------------------------------',
            'message POST' => 'MessageController@create',
            'message/{id} GET' => 'MessageController@showOneUser',
            'message/{id} PUT' => 'MessageController@update',
            'message/{id} DELETE' => 'MessageController@delete',
            'messages GET' => 'MessageController@showAll',
        ];
    return $manual;
    }
    
    public function showHelpRadio()
    {
        $manual = [ 'Rhizo Hermes API' => 'V0.0.3 -  Sys page, help',
            'License and Copyrights' => 'gplv2, some rights reserved',
            '----------------------' => '----------------------------------------',
            'radio/help GET' => 'HelpController@ShowHelpRadio',
            'radio/status GET' =>  'RadioController@getRadioStatus',
            'radio/mode GET' => 'RadioController@getRadioMode',
            /* TODO
            $router->get('status',  ['uses' => 'RadioController@getRadioStatus']);
            $router->post('mode/{mode}',  ['uses' => 'RadioController@setRadioMode']);
            $router->get('freq',  ['uses' => 'RadioController@getRadioFreq']);
            $router->post('freq/{freq}',  ['uses' => 'RadioController@setRadioFreq']);
            $router->get('bfo',  ['uses' => 'RadioController@getRadioBfo']);
            $router->post('bfo/{freq}',  ['uses' => 'RadioController@setRadioBfo']);
            */
            

        ];
    return $manual;
    }

}

