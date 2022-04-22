<?php

function exec_cli($command)
{
    ob_start();
    system($command , $return_var);
    $output = ob_get_contents();
    ob_end_clean();

    return ($output);
}

function exec_cli_no($command)
{
    ob_start();
    system($command , $return_var);
    $output = ob_get_contents();
    ob_end_clean();
	if ($return_var != 0) {
		return false;
	}
	else{
    	return true;
	}
}

function exec_cli2($command)
{
    ob_start();
	$output = system($command , $return_var);
	if ($return_var != 0) {
    		$output = ob_get_contents();
    		ob_end_clean();
    		return ($output);
	}
	else {
		return false;
	}
}

function exec_uc($command)
{
    ob_start();
    $ubitx_client = "/usr/bin/ubitx_client -c ";
    $command = $ubitx_client . $command;
    system($command , $return_var);
    $output = ob_get_contents();
    ob_end_clean();
    return ($output);
}

function exec_ucr($command)
{
    ob_start();
    $ubitx_client = "/usr/bin/ubitx_client -c ";
    $command = $ubitx_client . $command;
    system($command , $return_var);
    $output = ob_get_contents();
    ob_end_clean();
	if ($return_var != 0) {
		return false;
	}
	else{
    	return true;
	}
}

function exec_nodename(){

    $command = 'cat /etc/uucp/config|grep nodename|cut -f 2 -d " "';
    $output = exec_uc($command);
    $output = explode("\n", $output)[0];

    return $output;
}

function swr($raw_ref, $raw_fwd){
	if ($raw_ref <= 0 || $raw_fwd <= 0) {
		return 0;
	}
	else {
		$swr = 8.513756 * ( $raw_ref / $raw_fwd ) + 0.5080228;
		if($swr<1){
			$swr = 1;
		}
		return ($swr);
	}
}

function fwd2watts($rawadc){
	if ($rawadc <= 0) {
		return 0;
	}
	else{
		$x = 0.004882813 * $rawadc;
		$fwd = -1.616282 + 1.221939 * $x + 0.4510454 * $x^2;
		if ($fwd < 0) $fwd = 0; 
		return (round($fwd, 4));
	}
}

function ref2watts($rawadc){
	if ($rawadc <= 0) {
		return 0;
	}
	else{
		$x = 0.004882813 * $rawadc;
		$ref = 3.264422 * $x - 0.7132102;
		return (round($ref, 4));
	}
}

function adc2volts($rawadc){
	if ($rawadc <= 0) {
		return 0;
	}
	else{
		$volts = 0.004882813 * $rawadc;
		return (round($volts, 4));
	}
}

// function adc2volts($rawadc){
// 	$fig = (int) str_pad('1', 3, '0');
// 	$tr = $rawadc*5/1023;
// 	$output = (floor($tr*$fig)/$fig);
//     return ($output);
// }
