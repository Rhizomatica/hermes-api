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

class RadioController extends Controller
{

    /**
     * Get Radio Status
     *
     * @return Json
     */
    public function getRadioStatus()
    {
        //TODO copied from system status
        $pidtst = explode("\n", exec_cli("echo test"))[0];
        $radioFreq = 7142;

        $status = [
            'freq' => "TODO freq: " . $pidtst,
            'mode' => "TODO SSB: USB or LSB : " . $pidtst,
            'bfo' => "TODO bfo: " . $pidtst,
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
        return response("getRadioMode TODO", 200);
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


    