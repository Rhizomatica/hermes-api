<?php

use App\Http\Controllers\ErrorController;


function exec_cli($command)
{
	ob_start();
	system($command, $return_var);
	$output = ob_get_contents();
	ob_end_clean();

	return ($output);
}

function exec_cli_no($command)
{
	ob_start();
	system($command, $return_var);
	$output = ob_get_contents();
	ob_end_clean();
	if ($return_var != 0) {
		return false;
	} else {
		return true;
	}
}

//TODO - Unused
// function exec_cli2($command)
// {
// 	ob_start();
// 	$output = system($command, $return_var);
// 	if ($return_var != 0) {
// 		$output = ob_get_contents();
// 		ob_end_clean();
// 		return ($output);
// 	} else {
// 		return false;
// 	}
// }

// Append one line to a root-owned file, exactly as given: quoted for the
// "sudo sh -c" (which the scoped sudoers policy allows) and for the shell it
// runs, and without line breaks, so a value cannot add lines of its own.
function append_root_line($file, $line)
{
	$line = str_replace(["\r", "\n"], '', (string) $line);
	$inner = 'echo ' . escapeshellarg($line) . ' >> ' . escapeshellarg($file);
	return exec_cli('sudo sh -c ' . escapeshellarg($inner));
}

function exec_uc($command)
{
	// the radio client's commands are words and numbers ("set_frequency -a
	// 7100000 -p 0"); their values come from the request, and anything else
	// would reach the shell
	if (!preg_match('/^[A-Za-z0-9_.:+ -]+$/', (string) $command)) {
		(new ErrorController)->saveError($_SERVER['PHP_SELF'], 500, 'API Error: refused radio command ' . $command);
		return 500;
	}

	ob_start();
	$ubitx_client = env('HERMES_TOOL') . " -c ";
	$command = $ubitx_client . $command;
	system($command, $return_var);
	$output = ob_get_contents();
	ob_end_clean();

	if ($output == "WRONG_COMMAND") {
		(new ErrorController)->saveError($_SERVER['PHP_SELF'], 500, 'API Error: UBITX Client error - ' . $output);
		return 500;
	}

	return ($output);
}

//TODO - Unused
// function exec_ucr($command)
// {
// 	ob_start();
// 	$ubitx_client = "/usr/bin/ubitx_client -c ";
// 	$command = $ubitx_client . $command;
// 	system($command, $return_var);
// 	$output = ob_get_contents();
// 	ob_end_clean();
// 	if ($return_var != 0) {
// 		return false;
// 	} else {
// 		return true;
// 	}
// }

// TODO - Unused
// function exec_nodename()
// {

// 	$command = 'cat /etc/uucp/config|grep nodename|cut -f 2 -d " "';
// 	$output = exec_uc($command);
// 	$output = explode("\n", $output)[0];

// 	return $output;
// }

function swr($raw_ref, $raw_fwd)
{
	if ($raw_ref <= 0 || $raw_fwd <= 0) {
		return 0;
	} else {
		$swr = 8.513756 * ($raw_ref / $raw_fwd) + 0.5080228;
		if ($swr < 1) {
			$swr = 1;
		}
		return (round($swr, 5));
	}
}

function fwd2watts($rawadc)
{
	if ($rawadc <= 0) {
		return 0;
	} else {
		$x = 0.004882813 * $rawadc;
		$fwd = -1.616282 + 1.221939 * $x + 0.4510454 * $x ^ 2;
		if ($fwd < 0) $fwd = 0;
		return (round($fwd, 4));
	}
}

function ref2watts($rawadc)
{
	if ($rawadc <= 0) {
		return 0;
	} else {
		$x = 0.004882813 * $rawadc;
		$ref = 3.264422 * $x - 0.7132102;
		if ($ref < 0) $ref = 0;
		return (round($ref, 4));
	}
}

function adc2volts($rawadc)
{
	if ($rawadc <= 0) {
		return 0;
	} else {
		$volts = 0.004882813 * $rawadc;
		return (round($volts, 4));
	}
}
