<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Illuminate\Http\Request;

use App\System;


class SystemController extends Controller
{

    public function getSysConfig()
    {
		$system = System::first();
		$system->nodename = explode("\n", exec_cli("cat /etc/uucp/config|grep nodename|cut -f 2 -d \" \""))[0];
        return response()->json($system,200);
    }

    public function setSysConfig(Request $request)
    {
        if ($request->all()){
              if (System::select()->update($request->all())){
                return response()->json($request->all() , 200);
            }
            else {
				return response()->json(['message' => 'setSysConfig can not update' . $user], 500);
            }
        }
        else {
			return response()->json(['message' => 'setSysConfig does not have request data'], 500);
        }
    }

    /**
     * returns if station is a gateway
     *
     * @return string
     */
    public function getSysGw()
    {
		$gateway = env('HERMES_GATEWAY') ;
        return response()->json($gateway,200);
    }

    /**
     * set gw schedule
     *
     * @return string
     */
    public function setSysGwSched(Request $request)
    {
        if ($request->all()){
			return response()->json(['message' => 'setSysGw TODO'], 200);
        }
        else {
			return response()->json(['message' => 'setSysConfig does not have request data'], 500);
        }
    }

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
        $uname = explode("\n", exec_cli("uname -n"))[0];
        $piduu = explode("\n", exec_cli("ls  /lib/systemd/system/uucp.socket"))[0];
        $piduuardop = explode("\n", exec_cli("pgrep -x uuardopd"))[0];
        $pidmodem = explode("\n", exec_cli("pgrep -x VARA.exe"))[0];
        $pidradio = explode("\n", exec_cli("pgrep -x ubitx_controlle"))[0];
		$nodename = explode("\n", exec_cli("cat /etc/uucp/config|grep nodename|cut -f 2 -d \" \""))[0];
        $pidhmp = explode("\n", exec_cli("pgrep -x iwatch"))[0];
        $piddb = explode("\n", exec_cli("pgrep -x mariadbd"))[0];
        $pidpf = explode("\n", exec_cli("pgrep -x master"))[0];
		$pidvnc = explode("\n", exec_cli("pgrep -x Xtigervnc"))[0];
        $wifiessid = explode("\n", exec_cli("cat /etc/hostapd/hostapd.conf | grep ssid | cut -c6-"))[0];
        $wifich= explode("\n", exec_cli("cat /etc/hostapd/hostapd.conf | grep channel| cut -c9-"))[0];
        $ip = explode("\n", exec_cli('/sbin/ifconfig | sed -En \'s/127.0.0.1//;s/.*inet (addr:)?(([0-9]*\.){3}[0-9]*).*/\2/p\''));
		array_pop($ip);
		$disk_free = explode("\n", exec_cli("df  / | grep -v Filesystem | awk '{print $4}'"))[0];
        $interfaces= explode("\n", exec_cli('ip r'));
		array_pop($interfaces);
        $memory = explode(" ", exec_cli("free --mega| grep Mem | awk '{print ($2\" \"$3\" \"$4)}'"));

        $phpmemory = ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
        $status = [
            'status' => $piduu &&  $piduuardop && $pidmodem &&  $pidradio && $pidhmp && $piduu && $pidpf,
            'uname' => $uname,
            'nodename' => $nodename,
			'name' => env('HERMES_NAME'),
			'network' => env('HERMES_NETWORK'),
			'domain' => env('HERMES_DOMAIN'),
			'gateway' => env('HERMES_GATEWAY'),
            'ip' => $ip,
            'interfaces' => $interfaces,
            'wifiessid' => $wifiessid?$wifiessid:false,
            'wifich' => $wifich?$wifich:false,
            'interfaces' => $interfaces,
            'piduu' => $piduu?$piduu:false,
            'piduuardop' => $piduuardop?$piduuardop:false,
            'pidmodem' => $pidmodem?$pidmodem:false,
            'pidradio' => $pidradio?$pidradio:false,
            'pidhmp' => $pidhmp?$pidhmp:false,
            'piddb' => $piddb?$piddb:false,
            'pidpf' => $pidpf?$pidpf:false,
 			'memtotal' => $memory[0]."MB",
            'memused' => $memory[1]."MB",
            'memfree' => explode("\n", $memory[2])[0]."MB",
			'diskfree' => $disk_free?$disk_free:false,
        ];
        return response()->json($status, 200);
    }

    /**
     * Get sensors
     *
     * @return Table
     */
    public function getSensors()
    {
		$command = "sensors -ju | jq -cM";
		$output = exec_cli($command);
		//$output = str_replace("\r\n","",$output);
        $output = @json_decode($output);
        return response()->json($output, 200);
	}


    /**
     * Get mail use on disk
     *
     * @return Table
     */
    public function getMailDiskUsage()
    {
		//print($users);
		$output = exec_cli("echo btrfs filesystem du -s --human-readable /var/vmail/" . env("HERMES_DOMAIN") . "/* | grep -v Total> /tmp/du.sh");
		if (!$output){
			$output = exec_cli("chmod +x /tmp/du.sh");
			if (!$output){
				$disk_free = explode("\n ", exec_cli("sudo /tmp/du.sh"));
        			return response()->json($disk_free, 200);
			}
		}
		return responde()->json("error",500);
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
     * Get all Stations from uucp
     *
     * @return stations
     */
    public function getSysStations(){
        $command = "egrep -v '^\s*#' /etc/uucp/sys | grep system | cut -f 2 -d \" \"";
        $output = exec_cli($command);
        $sysnames = explode("\n", $output);

        $command2 = "egrep -v '^\s*#' /etc/uucp/sys | grep alias | cut -f 2 -d \" \"";
        $output2 = exec_cli($command2);
        $sysnames2 = explode("\n", $output2);

        $sysnameslist=[];

        for ($i = "0" ; $i < count($sysnames); $i++) {
            if(!empty($sysnames[$i])) {
				// if (empty ($sysnames2[$i])){
                $sysnameslist[]  =  [
                    'id' => $i,
                    'name' => $sysnames[$i],
                    'alias' => $sysnames2[$i],
                ];
            }
        }

        return response()->json($sysnameslist, 200);
    }

    /**
     * Get transmission spool 
     * 
     * @return Json
     */
    public function sysGetSpoolList(){
        $command = "uustat -a| grep -v uuadm";
        $output=exec_cli($command) ;
        $output = explode("\n", $output);
        $spool=[];

        for ($i = "0" ; $i < count($output); $i++) {
            if(!empty($output[$i])) {
                $fields = explode(" ", $output[$i]);
				if ( $fields[6] != "uuadmin"){
					$uuid =  explode(".",$fields[0]);
                	$spool[]  =  [
                    	//  '#' => $i,
						'uuidhost' => $uuid[0],
						'uuiduucp' => $uuid[1], 
                    	'dest' => $fields[1],
                    	'user' => $fields[2],
                    	'date' => $fields[3],
                    	'time' => $fields[4],
						'type' => $fields[5] == "Executing" ? "Mail" : "HMP", 
						'size' => $fields[5] == "Executing" ? $fields[9] : explode("(",$fields[7])[1],
						'destpath' =>  $fields[5] == "Executing" ? null :  $fields[10] ,
                	];
				}
            }
        }

		if (sizeof($spool) >= 1){
			return response()->json($spool, 200);
		}
		else{
			return response()->json(null, 200);
		}
    }

    public function uucpRejuvenateJob($id){
        $command = 'sudo uustat -r ' . $id; 
        $output=exec_cli($command) or die;
        return response($output, 200);
    }

    public function uucpKillMail($host, $id){
		$command = 'sudo mailkill.sh es gui ' . $host . '.' . $id; 
    	ob_start();
    	system($command , $return_var);
    	$output = ob_get_contents();
    	ob_end_clean();
		if ($return_var == 0) {
			return response()->json("uucp job killed: " . $host . '.' .$id, 200);
		}
		else{
			return response()->json("No job found", 404);
		}
   	}

    public function uucpKillJob($host, $id){
		$command = 'sudo uustat -k ' . $host . '.' . $id; 
        $output=exec_cli($command) or die;
		return response()->json("uucp job killed: " . $host . '.' .$id, 200);
   	}


    public function uucpKillJobs(){
        $command = 'sudo uustat -Ka '; 
        $output=exec_cli($command) or die;
        return response($output, 200);
    }

    public function uucpCall(){
        $command = 'sudo uucico -r1 ' ; 
        $output=exec_cli($command);
        return response($output, 200);
    }

    //port script restart_system.sh
    public function sysRestart() {
        $command = "sudo systemctl stop uuardopd";
        $output0 = exec_cli($command);

        $command = "sudo systemctl stop ardop";
        $output1 = exec_cli($command);

        $command = "sleep 1";
        $output2 = exec_cli($command);

        $command = "sudo systemctl start ardop";
        $output3 = exec_cli($command);

        $command = "sudo systemctl start uuardopd" ;
        $output4 = exec_cli($command);

        return response()->json([$output0,$output1,$output2,$output3,$output4,$output5],200);
    }

    function sysShutdown(){
		// set led status OFF on cabinet
        exec_uc("set_led_status -a OFF");
		sleep(1);

		// linux shutdown
        $command = "sudo halt";
        exec_cli($command);
        return json_encode("halted");
    }

    function sysReboot(){
        $command = "sudo reboot";
        $output = exec_cli($command);
        return json_encode("rebooted");
    }

    function sysRestore(){
        $command = "echo test running on php restore";
        $output = exec_cli($command);
       return $output;
        return json_encode($output);
    }

	//TODO check if all syslog is ok to frontend

    function sysLogMail(){
        $command = "sudo tail /var/log/mail.log -n 100000| sort -n ";
        $output=exec_cli($command);
        $output = explode("\n",$output);

        $log=[];

        for ($i = "0" ; $i < count($output); $i++) {
            if(!empty($output[$i])) {
                $log[]  =  [
                    'line' => $i,
                    'content' => $output[$i],
                ];
            }
        }
        return response()->json($log,200);
    }

    function sysLogUucp(){
        $command = "sudo uulog -n 100000 | sort -n ";
        $output=exec_cli($command);
        $output = explode("\n",$output);

        $log=[];

        for ($i = "0" ; $i < count($output); $i++) {
            if(!empty($output[$i])) {
                $log[]  =  [
                    'line' => $i,
                    'content' => $output[$i],
                ];
            }
        }
        return response()->json($log,200);
    }
    function sysDebUucp(){
        $command = "sudo uulog -d -n 100000 | sort -n ";
        $output=exec_cli($command);
        $output = explode("\n",$output);
        $log=[];

        for ($i = "0" ; $i < count($output); $i++) {
            if(!empty($output[$i])) {
                $log[]  =  [
                    'line' => $i,
                    'content' => $output[$i],
                ];
            }
        }
        return response()->json($log,200);
    }
}
