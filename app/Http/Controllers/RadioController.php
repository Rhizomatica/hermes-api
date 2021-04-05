<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

function exec_cli($command = "ls -l")
{
    ob_start();
    system($command , $return_var);
    $output = ob_get_contents();
    ob_end_clean();

    //or die;
    /*if ($exploder==true){
            return (explode("\n", $output));
            }*/

    return ($output);
}

function exec_nodename(){

    $command = 'cat /etc/uucp/config|grep nodename|cut -f 2 -d " "';
    $output = exec_cli($command);
    $output = explode("\n", $output)[0];

    return $output;
}

class SystemController extends Controller
{

    /**
     * Get Radio Status
     *
     * @return Json
     */
    public function getRadioStatus()
    {
        //TODO copied from system status
        $sysname = explode("\n", exec_cli("caduceu -n"))[0];
        $piduu = explode("\n", exec_cli("pgrep -x uuardopd"))[0];
        $pidmodem  = explode("\n", exec_cli("pgrep -x VARA"))[0];
        $piddb = explode("\n", exec_cli("pgrep -x mariadbd"))[0];
        $pidir = explode("\n", exec_cli("pgrep -x iredadmin"))[0];
        $pidpf = explode("\n", exec_cli("pgrep -x postfix"))[0];
        $pidtst = explode("\n", exec_cli("echo test"))[0];
        $ip = explode("\n", exec_cli('/sbin/ifconfig | sed -En \'s/127.0.0.1//;s/.*inet (addr:)?(([0-9]*\.){3}[0-9]*).*/\2/p\''))[0];
        // $ip = exec_cli('hostname -I');// doesnt work on arch
        $memory = explode(" ", exec_cli("free | grep Mem|cut -f 8,13,19,25,31,37 -d \" \""));
        $phpmemory = ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
        $status = [
            'status' => $piduu && $pidmodem && $pidir && $pidpf,
            'name' => $sysname,
            'nodename' => exec_nodename(),
            'piduu' => $piduu?$piduu:false,
            'piddb' => $piddb?$piddb:false,
            'pidmodem' => $pidmodem?$pidmodem:false,
            'pidtst' => $pidtst,
            'ipaddress' => $ip,
            'memory' => $memory,
            'phpmemory' => $phpmemory
        ];
        return response($status, 200);
    }

    /**
     * Get Radio Mode
     *
     * @return Json
     */
    public function getRadioMode()
    {
        return response("teste", 200);
    }

    /**
     * Set Radio Mode
     *
     * @return Json
     */
    public function setRadioMode($mode)
    {
        return response("TODO setRadioMode: " . $mode, 200);
    }

    /**
     * Get Radio Main Frequency Oscilator
     *
     * @return Json
     */
    public function getRadioFreq()
    {
        return response("getRadioFreq", 200);
    }

    /**
     * Set Radio Main Frequency Oscilator
     *
     * @return Json
     */
    public function setRadioFreq($freq)
    {
        return response("TODO setRadioFreq: " . $mode, 200);
    }


/**
     * Get Radio Beat Frequency Oscilator
     *
     * @return Json
     */
    public function getRadioBfo($freq)
    {
        return response("getRadioBfo", 200);
    }

    /**
     * Set Radio Mode
     *
     * @return Json
     */
    public function setRadioBfo($freq)
    {
        return response("TODO setRadioBfo: " . $mode, 200);
    }



    function rests_of_some_legacy_function(){
        $command = "uulog|tail -50";
        $output=exec_cli($command);
        $output = explode("\n",$output);
        return $output;
    }
}


    