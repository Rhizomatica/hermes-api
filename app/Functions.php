<?php


function exec_cli($command)
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