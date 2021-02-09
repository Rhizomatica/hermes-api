<?php

namespace App\Http\Controllers;

function exec_nodename(){

    $command = 'cat /etc/uucp/config|grep nodename|cut -f 2 -d " "';
    $output = exec_cli($command);
    $output = explode("\n", $output)[0];

    return $output;
}

class SystemController extends Controller
{
    /**
     * Get Name station from uucp
     *
     * @return string
     */
    public function  getSysNodeName()
    {
       return  response(json_encode(exec_nodename()),200);
    }

    /**
     * Get system status
     *
     * @return Table
     */
    public function getSysStatus()
    {

        $sysname = explode("\n", exec_cli("uname -n"))[0];
        $piduu = exec_cli("pgrep -x uuardopd");
        $pidar  = exec_cli("pgrep -x ardop");
        $pidtst = explode("\n", exec_cli("echo teste"))[0];
        $ip = explode("\n", exec_cli('/sbin/ifconfig | sed -En \'s/127.0.0.1//;s/.*inet (addr:)?(([0-9]*\.){3}[0-9]*).*/\2/p\''))[0];
        // $ip = exec_cli('hostname -I');// doesnt work on arch
        $memory = explode(" ", exec_cli("free | grep Mem|cut -f 8,13,19,25,31,37 -d \" \""));

        $status = [
            'status' => true,
            'name' => $sysname,
            'nodename' => exec_nodename(),
            'piduu' => $piduu?$piduu:false,
            'pidar' => $pidar?$pidar:false,
            'pidtst' => $pidtst,
            'ipaddress' => $ip,
            'memory' => $memory
        ];
        return response(json_encode($status), 200);
    }

    /**
     * Get files from $path
     *
     * @return Table
     */
    public function getFiles($path)
    {
        if (!$path ){
            $command = "ls -la /etc/uucp";
        }
        $command = "ls -la " . $path;
        $output = exec_cli($command);
        $output =  explode("\n ", $output);
        return  $output;
    }

    /**
     * Get info if system is running
     *
     * @return Boolean
     */
    public function isRunning(){
        //TODO
        exec("pgrep -x uuardopd", $piduu);
        exec("pgrep -x ardop", $pidar);
        if(empty($piduu) || empty($pidar)){
            //Sistema com Problemas!;
            return false;
        } else {
            //Sistema Funcionando!;
            return true;
        }
    }

    /**
     * Get info if system is running TODO parametro grupo
     *
     * @return table
     */
    public function queueErase(){
        //$command = "sudo uustat -u www-data -K";
        $command = "uustat -u www-data -K";
        //TODO ? repeated?
        //$command = "sudo uustat -u uucp -K";
        $output = exec_cli($command);
        //$command = "sudo uustat -u root -K";
        $command = "uustat -u root -K";
        $output2 = exec_cli($command);
        //TODO
        return [$output,$output2] ;
    }

     /**
     * Get all possible Stations from uucp
     *
     * @return stations
     */
 
    public function getSysStations(){
        $command = "egrep -v '^\s*#' /etc/uucp/sys | grep alias | cut -f 2 -d \" \"";
        $output = exec_cli($command);
        $command2 = "egrep -v '^\s*#' /etc/uucp/sys | grep system | cut -f 2 -d \" \"";
        $output2 = exec_cli($command2);
        //$command3 = "egrep -v '^\s*#' /etc/uucp/sys | grep address | cut -f 2 -d \" \"";
        //$output3 = exec_cli($command3);

        $sysnames = explode("\n", $output);
        $sysnames2 = explode("\n", $output2);
        //$sysnames3 = explode("\n", $output3);
        $sysnameslist=[];

        for ($i = "0" ; $i < count($sysnames); $i++) {
            if(!empty($sysnames[$i])) {
                $sysnameslist[]  =  [
                    'id' => $i,
                    'name' => $sysnames[$i],
                    'alias' => $sysnames2[$i],
          //          'location' => $sysnames3[$i]
                ];
            }
        }

        return $sysnameslist;
    }



    //fila de transmissao
    //port spool_list
    public function sysGetSpoolList(){
        $command = "uustat -a| cut -f 2,7,8,9 -d \" \" | sed \"s/\/var\/www\/html\/uploads\///\"";
        //TODO fix path in sed $cfg['path_uploads'])
        //  $command = "uustat -a| cut -f 2,7,8,9 -d \" \" | sed \"s/\/var\/www\/html\/uploads\///\"";
        $output=exec_cli($command) or die;
        $output = explode("\n", $output);
        $spool=[];

        for ($i = "0" ; $i < count($output); $i++) {
            if(!empty($output[$i])) {
                $fields = explode(" ", $output[$i]);
                $spool[]  =  [
                    'id' => $i,
                    'dest' => $fields[0],
                    'file' => $fields[1],
                    'file' => $fields[2] . ' ' .  $fields[3] ,
                ];
            }
        }

        return response($spool, 200);
    }

    //DONE in FileController
    public function fileLoad(){
        $command = "uustat -a| cut -f 2,7,8,9 -d \" \" | sed \"s/\/var\/www\/html\/uploads\///\"";
            $output = exec_cli($command);
            //TODO return true or false?
            return $output;
    }

    //decrypt
    //TODO
    public function exec_decrypt(){
        //TODO path
        $path = $_POST['path'];
        $dec_subdir=dirname($path)."/dec/";
        mkdir($dec_subdir, 0777, TRUE);
        $outfile=$dec_subdir.basename($path,".gpg");
        //TODO
        $command = "decrypt.sh ".$path." ".$outfile." ".$_POST['password'];
        $output = exec_cli($command);

        if ($return_var == 0){
            //TODO this path can't be here
            $prefix = '/var/www/html/';

            if (substr($outfile, 0, strlen($prefix)) == $prefix) {
                $str = substr($outfile, strlen($prefix));
            }
            //correct password
            //TODO  // basename($str);
            return $str;
        } else {
            // wrong password
            unlink($outfile);
            return FALSE;
        }
    }

    //TODO
    public function jobKill($param){
        //TODO
        //$command = "kill_job.sh";
        $output=exec_cli($command) or die;
        return $output;
    }

    function jobList(){
        //TODO fix sed
        $command = "uustat -a| cut -f 2,7,8,9 -d \" \" | sed \"s/\/var\/www\/html\/uploads\///\"";
        $output = exec_cli($command);
        echo $output;
    }

    //TODO
    // Open a directory, and read its contents
    public function dirList(){

        if (is_dir($cfg['path_files'])){
            if ($dh = opendir($cfg['path_files'])){
                while (($file = readdir($dh)) !== false){
                    if ($file == '.' || $file == '..') {
                        continue;
                    }

                    //TODO to array
                    echo "Arquivo:" . $file . "<br />";
                }
                closedir($dh);
            }
        }
    }

    //port script restart_system.sh
    public function systemRestart() {
        $command = "sudo systemctl stop uuardopd";
        $output0 = exec_cli($command);

        $command = "sudo systemctl stop ardop";
        $output1 = exec_cli($command);

        //TODO sleep php
        $command = "sleep 1";
        $output2 = exec_cli($command);

        $command = "sudo systemctl start ardop";
        $output3 = exec_cli($command);

        $command = "sudo systemctl start uuardopd" ;
        $output4 = exec_cli($command);

        //TODO
        return json_encode([$output0,$output1,$output2,$output3,$output4,$output5]);
    }

    function sysDoShutdown(){
        $command = "sudo halt";
        exec_cli($command);
    }

    function sysGetLog(){
        $command = "uulog|tail -50";
        $output=exec_cli($command);
        $output = explode("\n",$output);
        return $output;
    }
}


//TODO
//alias.sh bash contents
/*
 * oline=$(grep -n $1 /etc/uucp/sys|cut -d ':' -f 1)
 *  linePlus=$((line+1))
 *  #echo $line
 *  name=$(head -$linePlus /etc/uucp/sys|tail -1|cut -d ' ' -f 2)
 *  echo -n $name
 */


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