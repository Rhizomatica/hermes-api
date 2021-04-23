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

$password = env('HERMES_TOOL') + " -c ";

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
     * Get TX RX Statustxrx_status
     *
     * @return Json
     */
    public function getRadioTXRXStatus()
    {
        $cmd = $caduceu . "-c get_txrx_status";
        return response("TODO getRadioMode ", 200);
    }

    /**
     * Set PTT
     *
     * @return Json
     */
    public function setRadioPTT($mode)
    {
        $paron=  "ptt_on";
        if ($mode){
            $par=  "ptt_off";
        }
        else{
            $par=  "ptt_on";
        }
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("TODO setRadioFreq: " , $output, 200);
    }

    /**
     * Get Radio Main Frequency Oscilator
     *
     * @return Json
     */
    public function getRadioFreq()
    {
        $par =  "get_frequency";
        $output = exec_cli($caduceu + $par);
        $output = explode("\n", $output)[0];
        return response("getRadioFreq: " , $output, 200);
    }

    /**
     * Set Radio Main Frequency Oscilator
     *
     * @return Json
     */
    public function setRadioFreq(int $freq)
    {
       $par =  "set_frequency -a " + freq;
        return response("TODO setRadioFreq: " . $mode, 200);
    }

    /**
     * Get Radio Mode
     *
     * @return Json
     */
    public function getRadioMode()
    {
        $par = "get_mode";
        return response("getRadioMode TODO", 200);
    }

    /**
     * Set Radio Mode
     * USB OR LSB
     *
     * @return Json
     */
    public function setRadioMode($mode)
    {
        $par = "set_mode " + $mode;
        if ($mode = "USB"){
            $par=  "-a USB";
        }
        elseif ($mode = "LSB"){
            $par=  "-a LSB";
        }
        else {
            return response("setRadioMode invalid mode: ", $mode, 500);
        }
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("TODO setRadioMode: " . $mode, 200);
    }

    /**
     * Get Radio Protection Status
     *
     * @return Json
     */
    public function getRadioProtectionStatus($freq)
    {
        $par = "get_protection_status";
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("getRadioProtectionStatus: ", $output, 200);
    }

    /**
     * Set Radio Master Calibration
     *
     * @return Json
     */
    public function setRadioMasterCal($freq)
    {
        $par = "set_mastercal" + $freq;
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("getRadioProtectionStatus: ", $output, 200);
    }

    /**
     * Get Radio Master Calibration
     *
     * @return Json
     */
    public function getRadioMasterCal()
    {
        $par = "get_mastercal";
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("getRadioProtectionStatus: ", $output, 200);
    }

    /**
     * Set Radio Beat Frequency Oscilator
     *
     * @return Json
     */
    public function setRadioBfo($freq)
    {
        $par = "set_bfo" + $freq;
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("TODO setRadioBfo: ", $output, 200);
    }

    /**
     * Get Radio Beat Frequency Oscilator
     *
     * @return Json
     */
    public function getRadioBfo($freq)
    {
        $par = "get_bfo";
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("TODO getRadioBfo: ", $output, 200);
    }

    /**
     * Get Radio Forward
     *
     * @return Json
     */
    public function getRadioFwd($freq)
    {
        $par = "get_fwd";
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("TODO getRadioBfo: ", $output, 200);
    }

    /**
     * Get Radio Ref
     *
     * @return Json
     */
    public function getRadioRef($freq)
    {
        $par = "get_fwd";
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("TODO getRadioBfo: ", $output, 200);
    }

    /**
     * Set Radio LED Status
     *
     * @return Json
     */
    public function setRadioLedStatus($status)
    {
        if ($status = "ON"){
            $par = "set_led_status -a ON";
        }
        elseif ($status = "ON"){
            $par = "set_led_status -a ON";
        }
        else{
            return response("setRadioLedStatus fail", 500 );
        }
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("TODO setRadioLEDS : ", $output, 200);
    }

    /**
     * Get Radio LED Status
     *
     * @return Json
     */
    public function getRadioLedStatus()
    {
        $par = "get_led_status";
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("TODO getRadioLedStatus: ", $output, 200);
    }

    /**
     * Set Radio Bypass Status
     *
     * @return Json
     */
    public function setRadioBypassStatus($status)
    {
        if ($status = "ON"){
            $par = "set_led_status -a ON";
        }
        elseif ($status = "ON"){
            $par = "set_led_status -a ON";
        }
        else{
            return response("setRadioBypassStatus fail", 500 );
        }
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("TODO setRadioBypassStatus: ", $output, 200);
    }

    /**
     * Get Radio Bypass Status
     *
     * @return Json
     */
    public function getRadioBypassStatus()
    {
        $par = "get_led_status";
        $cmd = $caduceu + $par;
        $output = exec_cli($cmd);
        $output = explode("\n", $output)[0];
        return response("TODO getRadioBypassStatus: ", $output, 200);
    }

    function rests_of_some_legacy_function(){
        $command = "uulog|tail -50";
        $output=exec_cli($command);
        $output = explode("\n",$output);
        return $output;
    }
}


    