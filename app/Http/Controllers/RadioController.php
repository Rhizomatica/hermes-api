<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

function exec_cli($command)
{
    ob_start();
    $ubitx_client = "/usr/bin/ubitx_client -c ";
    $command = $ubitx_client . $command;
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

    //$command = 'cat /etc/uucp/config|grep nodename|cut -f 2 -d " "';
    $output = exec_cli($command);
    $output = explode("\n", $output)[0];

    return $output;
}

//$username = env('HERMES_TOOL');


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
        //$pidtst = explode("\n", exec_cli(env('HERMES_TOOL') . " -c get_frequency"))[0];
        $radio_frequency= explode("\n", exec_cli("get_frequency"))[0];
        $radio_mode= explode("\n", exec_cli("get_mode"))[0];
        $radio_led= explode("\n", exec_cli("get_led_status"))[0];
        if ($radio_led== "LED_ON"){
            $radio_led=true;
        }
        else if($radio_led == "LED_OFF" || !$radio_led){
            $radio_led=false;
        }

        $radio_bfo= explode("\n", exec_cli("get_bfo"))[0];
        $radio_fwd= explode("\n", exec_cli("get_fwd"))[0];
        $radio_ref= explode("\n", exec_cli("get_ref"))[0];
        $radio_txrx= explode("\n", exec_cli("get_txrx_status"))[0];
        if ($radio_txrx == "INRX"){
            $radio_rx =true;
            $radio_tx =false;
        }
        else if($radio_txrx== "INTX" || !$radio_txrx){
            $radio_tx =true;
            $radio_rx =false;
        }
        $radio_mastercal= explode("\n", exec_cli("get_mastercal"))[0];
        $radio_protection= explode("\n", exec_cli("get_protection_status"))[0];
        if ($radio_protection == "PROTECTION_ON"){
            $radio_protection=true;
        }
        else if($radio_protection == "PROTECTION_OFF" || !$radio_protection){
            $radio_protection = false;
        }
        $radio_bypass= explode("\n", exec_cli("get_bypass_status"))[0];
        if ($radio_bypass== "BYPASS_ON"){
            $radio_bypass=true;
        }
        else if($radio_bypass == "BYPASS_OFF" || !$radio_bypass){
            $radio_bypass = false;
        }

        $status = [
            'freq' => $radio_frequency,
            'mode' => $radio_mode,
            'led' => $radio_led,
            'bfo' => $radio_bfo,
            'fwd' => $radio_fwd,
            'ref' => $radio_ref,
            'txrx' => $radio_txrx,
            'tx' => $radio_tx,
            'rx' => $radio_rx,
            'mastercal' => $radio_mastercal,
            'protection' => $radio_protection,
            'bypass' =>  $radio_bypass,
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
        $radio_frequency= explode("\n", exec_cli("get_txrx_status"))[0];
        return response($radio_frequency, 200);
    }

    /**
     * Set PTT
     *
     * @return Json
     */
    public function setRadioPttOn()
    {
        $output = exec_cli(ptt_on);
        $output = explode("\n", $output)[0];
        return response("TODO setRadioPTT: " , $output, 200);
    }

    public function setRadioPttOff()
    {
        $output = exec_cli(ptt_off);
        $output = explode("\n", $output)[0];
        return response("TODO setRadioPTT: " , $output, 200);
    }

    /**
     * Get Radio Main Frequency Oscilator
     *
     * @return Json
     */
    public function getRadioFreq()
    {
        $radio_frequency= explode("\n", exec_cli("get_frequency"))[0];
        return response($radio_frequency, 200);
    }

    /**
     * Set Radio Main Frequency Oscilator
     *
     * @return Json
     */
    public function setRadioFreq(int $freq)
    {
        $command = explode("\n", exec_cli("set_frequency -a " . $freq))[0];
        if ($command == "OK"){
            $radio_frequency = explode("\n", exec_cli("get_frequency"))[0];
            return response($radio_frequency, 200);
        }
        else {
            return response( "error: " . $command, 500);
            
        }
    }

    /**
     * Get Radio Mode
     *
     * @return Json
     */
    public function getRadioMode()
    {
        $radio_mode= explode("\n", exec_cli("get_mode"))[0];
        return response($radio_mode, 200);
    }

    /**
     * Set Radio Mode
     * USB OR LSB
     *
     * @return Json
     */
    public function setRadioMode($mode)
    {
        if($mode == "USB"){
            $command = explode("\n", exec_cli("set_mode -a USB"))[0];
        }
        elseif( $mode == "LSB"){
            $command= explode("\n", exec_cli("set_mode -a LSB"))[0];
        }
            return response($command, 200);
        /*else{
            return response("setRadioMode invalid error: is not USB or LSB" . $command, 500);
        }

        if ($command== "OK"){
            $radio_mode= explode("\n", exec_cli("get_mode"))[0];
            return response($radio_mode, 200);
        }
        else{
            return response("test error: " . $radio_mode, 500);

        }*/
    }

    /**
     * Get Radio Beat Frequency Oscilator
     *
     * @return Json
     */
    public function getRadioBfo()
    {
        $bfo= explode("\n", exec_cli("get_bfo"))[0];
        return response($bfo, 200);
    }

    /**
     * Set Radio Beat Frequency Oscilator
     *
     * @return Json
     */
    public function setRadioBfo($freq)
    {
        $command = explode("\n", exec_cli("set_bfo -a " . $freq))[0];
        if ($command == "OK"){
            $radio_bfo = explode("\n", exec_cli("get_bfo"))[0];
            return response($radio_bfo , 200);
        }
        else {
            return response( "error: " . $command, 500);
            
        }
    }

    /**
     * Get Radio Forward
     *
     * @return Json
     */
    public function getRadioFwd()
    {
        $bfo= explode("\n", exec_cli("get_fwd"))[0];
        return response($bfo, 200);
    }


    /**
     * Get Radio Ref
     *
     * @return Json
     */
    public function getRadioRef()
    {
        $radio_ref = explode("\n", exec_cli("get_ref"))[0];
        return response( $radio_ref, 200);
    }


    /**
     * Get Radio TXRX
     *
     * @return Json
     */
    public function getRadioTxrx()
    {
        $radio_txrx = explode("\n", exec_cli("get_txrx"))[0];
        return response( $radio_txrx, 200);
    }

    /**
     * Get Radio mastercal
     *
     * @return Json
     */
    public function getRadioMasterCal()
    {
        $radio_mastercal= explode("\n", exec_cli("get_mastercal"))[0];
        return response( $radio_mastercal, 200);
    }

    /**
     * Set Radio Mastercal
     *
     * @return Json
     */
    public function setRadioMasterCal($freq)
    {
        $command = explode("\n", exec_cli("set_mastercal -a " . $freq))[0];
        if ($command == "OK"){
            $radio_fwd= explode("\n", exec_cli("get_mastercal"))[0];
            return response($radio_fwd, 200);
        }
        else {
            return response( "error: " . $command, 500);
            
        }
    }

    /**
     * Get Radio protection status
     *
     * @return Json bool
     */
    public function getRadioProtection()
    {
        $radio_protection = explode("\n", exec_cli("get_protection_status"))[0];
        if ($radio_protection == "PROTECTION_OFF"){
            return response( false, 200);
        }
        else {
            return response( true, 200);

        }
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
        elseif ($status = "OFF"){
            $par = "set_led_status -a OFF";
        }
        else{
            return response("setRadioLedStatus fail", 500 );
        }

        $radio_led= exec_cli($par);
        return response("TODO setRadioLEDS : ", $radio_led, 200);
    }

    /**
     * Get Radio LED Status
     *
     * @return Json
     */
    public function getRadioLedStatus()
    {
        $radio_led= explode("\n", exec_cli("get_led_status"))[0];
        if($radio_led == "LED_ON"){
            return response( true, 200);
        }
        elseif($radio_led == "LED_OFF"){
            return response( false, 200);
        }
        else{
            return response( "error", $radio_led, 500);
        }
    }

    /**
     * Get Radio Bypass Status
     *
     * @return Json
     */
    public function getRadioBypassStatus()
    {
        $radio_bypass= explode("\n", exec_cli("get_bypass_status"))[0];
        if($radio_bypass== "BYPASS_ON"){
            return response( true, 200);
        }
        elseif($radio_bypass== "BYPASS_OFF"){
            return response( false, 200);
        }
        else{
            return response( "error", $radio_bypass, 500);
        }
    }

    /**
     * Set Radio Bypass Status
     *
     * @return Json
     */
    public function setRadioBypassStatus($status)
    {
        if ($status = "ON"){
            $par = "set_bypass_status -a ON";
        }
        elseif ($status = "ON"){
            $par = "set_bypass_status -a ON";
        }
        else{
            return response("setRadioBypassStatus fail", 500 );
        }
        $radio_bypass= explode("\n", exec_cli($par))[0];
        return response( $status, 200);
    }

    /**
     * Get Radio  serial
     *
     * @return Json
     */
    public function getRadioSerial()
    {
        $radio_serial = explode("\n", exec_cli("get_serial"))[0];
        if($radio_serial == "Serial"){
            return response( true, 200);
        }
        else{
            return response( "error:  " . $radio_serial, 500);
        }
    }

    /**
     * Set Radio Serial
     *
     * @return Json
     */
    public function setRadioSerial($serial)
    {
        $par = "set_serial " . $serial;
        $radio_bypass= explode("\n", exec_cli($par))[0];
        if($radio_bypass == "OK"){
            return response("setRadioSerial" . $serial, 200);
        }
        else{
            return response("setRadioSerial fail", 500 );
        }
    }
    
    /**
     * Get Radio  serial
     *
     * @return Json
     */
    public function resetRadioProtection()
    {
        $radio_serial = explode("\n", exec_cli("reset_protection"))[0];
        if($radio_serial == "OK"){
            return response( true, 200);
        }
        else{
            return response( "error: " . $radio_serial, 500);
        }
    }

}