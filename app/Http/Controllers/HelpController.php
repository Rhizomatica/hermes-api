<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function showHelp()
    {
        $manual = [ 'Rhizo Hermes API' => 'V0.0.3 -  default page, help',
            'License and Copyrights' => 'gplv2, some rights reserved',
            'page 0' => 'TODO manual',
            'page 1' => 'list stations',
            'page 2' => 'get user ',
            'page 666' => 'run command',
            'd true' => 'debugger on HTML',
            '\--functions' => 'list of funcionalities',
            'exec get_nodename' => 'exec_get_nodename()',
            'exec is_running' => 'exec_isrunning()',
            'exec_erase_queue()' => 'not running now',
            'exec_get_systems()' => 'exec_get_systems()',
            'exec_get_spool_list()' => 'TODO exec_get_spool_list()',
            'exec_kill_job()' => 'TODO exec_get_spool_list()',
            'exec_decrypt()' => 'decrypt',
            'exec_restart_system()' => '',
            'exec_shutdown()' => '',
            'exec_viewlog()' => '',
            'exec_listfiles()' => 'TODO',
            'exec_viewjob()' => '',
            '' => '',
            '' => '',
        ];
/*

  $cfg = [
            'path_root' => $PATH,
            'path_upload' => $PATH.'uploads/',
            'path_files' => $PATH.'arquivos/',
       ];


    */
    return $manual;
    }
}

