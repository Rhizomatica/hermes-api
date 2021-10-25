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

function tovolts($input){

	$fig = (int) str_pad('1', 3, '0');
	$tr = $input*5/1023;
	$output = (floor($tr*$fig)/$fig);
    return ($output);
}

