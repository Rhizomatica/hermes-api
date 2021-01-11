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
        $manual = [ 'Rhizo Hermes API' => 'V0.0.3 -  default page, help',
            'License and Copyrights' => 'gplv2, some rights reserved',
            '-----------------------' => '----------------------------------------',
            '/help' => 'TODO manual',
            '/sys/help' => 'TODO manual',
            '--------DATA---------------' => '----------------------------------------',
            '/user/' => 'user, get, delete ',

        ];
    return $manual;
    }


    public function showHelpSys()
    {
        $manual = [ 'Rhizo Hermes API' => 'V0.0.3 -  Sys page, help',
            'License and Copyrights' => 'gplv2, some rights reserved',
            '-------ATALHOS ----------------' => '----------------------------------------',
            'sys/help' => 'showHelpSys()',
            'sys/getnodename' => 'getnodename()',
            'sys/getstations' => 'TODO mockup getStations()',
            'sys/isrunning' => 'isrunning()',
            'sys/erasequeue' => 'TODO not running now',
            'sys/systems' => 'TODO get_systems()',
            'sys/spoollist' => 'TODO exec_get_spool_list()',
            'sys/kill_job' => 'TODO exec_get_spool_list()',
            'sys/decrypt' => 'TODO decrypt ',
            'sys/reboot' => 'TODO reboot',
            'sys/shutdown()' => 'TODO shutdown ',
            'sys/viewlog()' => 'TODO viewlog',
            'sys/listfiles()' => 'TODO listfiles',
            'sys/viewjob()' => 'TODO viewjob',
            '------ FUNÇOES -----------------' => '----------------------------------------',
            'exec_cli' => 'exec_cli($command)',

        ];
    return $manual;
    }
}

