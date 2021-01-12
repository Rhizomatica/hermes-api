<?php

namespace App\Http\Controllers;

class SystemController extends Controller
{
    /**
     * Get Name station from uucp
     *
     * @return string
     */
    public function getNodename()
    {
        $command = "egrep -v '^\s*#' /etc/uucp/config | grep nodename | cut -f 2";
        //return $command;
        return exec_cli($command);
    }

    public function getFiles()
    {
        $command = "ls -la /etc/uucp";
        $output = exec_cli($command);
        $output =  explode("\n ", $output);
        
        return  $output;
    }

    /**
     * Get all possible Stations from uucp
     *
     * @return void
     */
    public function getStations()
    {
        $stations_sample= [
            ["id" => 1, "name" => "rio", "location" => "para"],
            ["id" => 2, "name" => "praia", "location" => "bahia"],
            ["id" => 3, "name" => "floresta", "location" => "amazonas"],
            ["id" => 4, "name" => "central", "location" => "brasilia"],
            ["id" => 5, "name" => "una", "location" => "altamira"],
            ["id" => 6, "name" => "barca", "location" => "alter do chao"],
            ["id" => 7, "name" => "mobata", "location" => "altamira"],
            ["id" => 8, "name" => "bacuri", "location" => "altamira"],
            ["id" => 9, "name" => "xantana", "location" => "alter do chao"],
            ["id" => 10, "name" => "niobia", "location" => "brasilia"],
            ["id" => 11, "name" => "barra morta", "location" => "teste"],
            ["id" => 12, "name" => "nativa", "location" => "alter"],
            ["id" => 13, "name" => "maraca", "location" => "xingú"],
            ["id" => 14, "name" => "tornado", "location" => "belo monte"]
        ];
        return $stations_sample;
    }

    /**
     * Get info if system is running
     *
     * @return table
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
     * old script get_systems
     *
     * @return table
     */


    function exec_get_systems(){
        //TODO
        $command = "egrep -v '^\s*#' /etc/uucp/sys | grep alias | cut -f 2 -d \" \"";
        $output = exec_cli($command);
        $sysnames = explode("\n", $output);
        //TODO
        $sysnameslist=[];

        for ($i = "0" ; $i < count($sysnames); $i++) {
            if(!empty($sysnames[$i])) {
                //echo $sysnames[$i];
                //TODO adicionar elemento
                array_push($a, sysnames[$i]);
            }
        }
        return [ $sysnames,$sysnameslist ];
    }

    public function fileLoad(){
        $command = "uustat -a| cut -f 2,7,8,9 -d \" \" | sed \"s/\/var\/www\/html\/uploads\///\"";
            $output = exec_cli($command);
            //TODO return true or false?
            return $output;
    }

    //fila de transmissao
    //port spool_list
    public function getSpoolList(){
        $command = "uustat -a| cut -f 2,7,8,9 -d \" \" | sed \"s/\/var\/www\/html\/uploads\///\"";
        //TODO fix path in sed $cfg['path_uploads'])
        //  $command = "uustat -a| cut -f 2,7,8,9 -d \" \" | sed \"s/\/var\/www\/html\/uploads\///\"";
        $output=exec_cli($command) or die;
        return($output);
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
        return [$output0,$output1,$output2,$output3,$output4,$output5];
    }

    function systemShutdown(){
        $command = "sudo halt";
        exec_cli($command);
    }

    function systemLog(){
        $command = "uulog|tail";
        $output=exec_cli($command);
        return $output;
    }

    //TODO json clue
    function json(){
        $data = explode("\n",$out);
        json_encode($data);
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