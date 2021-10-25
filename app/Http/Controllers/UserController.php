<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;


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

class UserController extends Controller
{

    public function showAllUsers()
    {
        return response()->json(User::all());
    }

    public function showOneUser($id)
    {
         if( $user = User::firstWhere('email', $id)){
            return response()->json($user, 200);
         }
         else
         {
            return response()->json('Error: cant find user ', 500);
         }
    }

    public function create(Request $request)
    {
        //var_dump($request->all);
        $username = env('HERMES_EMAILAPI_USER');
        $password = env('HERMES_EMAILAPI_PASS');
        $soap_location = env('HERMES_EMAILAPI_LOC');
        $soap_uri = env('HERMES_EMAILAPI_URI');

        $client = new \SoapClient(null, array('location' => $soap_location,
                'uri'      => $soap_uri,
                'trace' => 1,
                'stream_context'=> stream_context_create(array('ssl'=> array('verify_peer'=>false,'verify_peer_name'=>false))),
                'exceptions' => 1));
        try {
            if($session_id = $client->login($username, $password)) {
                //Logged successfull. Session ID:'.$session_id.'<br />';
                //, 'phone', 'site', 'location', 'password', 'recoverphrase', 'recoveranswer', 'updated_at', 'created_at', 'admin'
                //* Set the function parameters.
                $client_id = 1;
				$request['email'] = strtolower($request['email']);
                $params = array(
                    'server_id' => 1,
                    'email' =>  $request['email'] . '@' . env('HERMES_DOMAIN'),
                    'login' => $request['email'],
                    'password' => $request['password'],
                    'name' => $request['name'],
                    'uid' => 5000,
                    'gid' => 5000,
                    'maildir' => '/var/vmail/' . $request['email'],
                    'quota' => 5242880,
                    'cc' => '',
                    'homedir' => '/var/vmail',
                    'autoresponder' => 'n',
                    'autoresponder_start_date' => '',
                    'autoresponder_end_date' => '',
                    'autoresponder_text' => 'hallo',
                    'autoresponder_subject' => 'Out of office reply',
                    'move_junk' => 'n',
                    'custom_mailfilter' => 'spam',
                    'postfix' => 'n',
                    'access' => 'n',
                    'disableimap' => 'n',
                    'disablepop3' => 'n',
                    'disabledeliver' => 'n',
                    'disablesmtp' => 'n',
                    'dbispconfig' => 1,
                    'mail_user' => 0,
                    'purge_trash_days' => 100,
                    'purge_junk_days' => 100
                );
                if($mailuser_id = $client->mail_user_add($session_id, $client_id, $params)){
                    $request['password'] = hash('sha256', $request['password']);
                    $request['emailid'] = $mailuser_id;

                    if($user = User::create($request->all())){
                		$command = "uux -j '" . env('HERMES_ROUTE') . "!uuadm -a -m "  . $request['email'] . "@" . env('HERMES_DOMAIN') . " -n " . $request['name'] . "'" ;

                		if ($output = exec_cli($command) ){
							//returns uucp job id
							$output = explode("\n", $output)[0];
                        	return response()->json($output, 201); //Created
						}
						else {
                    		return response('Hermes create user: create user table and ispconfig but Error on uucp to advise: ' . $output . $command, 300);
						}

                    } else{
                        return response()->json('create email but couldnt create user', 500); 
                    }
                } else{
                        return response()->json('can\´t create email', 500); 

                }
                //$mailuser_id = $client->mail_user_add($session_id, $client_id, $params);
                $client->logout($session_id);
            }
        }
        catch (SoapFault $e) {
            echo $client->__getLastResponse();
            die('SOAP Error: '.$e->getMessage());
        }
    }


    public function update($id, Request $request)
    {
		
		//some tests
        if($id == 'root'){
            return response()->json('Error: cant update root', 504);
         }

		if( $request['email']){
			return response()->json('Error: cant change an existing login email', 504);
		}

		if (! $request[ 'name'] && ! $request[ 'password'] && ! $request[ 'phone']  && ! $request[ 'site'] && ! $request[ 'location'] && ! $request[ 'recoverphrase'] && ! $request[ 'recoveranswer'] && ! $request[ 'admin'] ){
			return response()->json('Error: cant update without data form ', 504);
		}
        $username = env('HERMES_EMAILAPI_USER');
        $password = env('HERMES_EMAILAPI_PASS');
        $soap_location = env('HERMES_EMAILAPI_LOC');
        $soap_uri = env('HERMES_EMAILAPI_URI');

        $client = new \SoapClient(null, array('location' => $soap_location,
                'uri'      => $soap_uri,
                'trace' => 1,
                'stream_context'=> stream_context_create(array('ssl'=> array('verify_peer'=>false,'verify_peer_name'=>false))),
                'exceptions' => 1));

        try {
            if($session_id = $client->login($username, $password)) {

                // Get the email user record
                $mail_user_record = $client->mail_user_get($session_id, $id);

				if ($request['name']){
                  	$mail_user_record['name'] = $request['name'];
				}

				if ($request['password']){
                  	$mail_user_record['password'] = $request['password'];
				}

				if ($request['phone']){
                  	$mail_user_record['phone'] = $request['phone'];
				}


                $client_id = 1;
				//Update the email record
				$affected_rows = $client->mail_user_update($session_id, $client_id, $id, $mail_user_record);

				//disconnect SOAP
                $client->logout($session_id);
				$login = $mail_user_record['login'];
                if ( $affected_rows > 0) {
                    if(  $user = User::firstWhere('email', $login)){
						if ($request['password']){
                        	$request['password'] = hash('sha256', $request['password']);
						}

                        if (User::where('email', $login)->update($request->all())){
                    		$user = User::firstWhere('email', $login);
                            return response()->json( $user, 200);
                        }
                        else {
                            return response()->json('Update ISPCONFIG but error when update local database', 500);
                        }
                    }
                    else {
                        return response()->json('Error: mail id not found on database', 504);
                    }
                }
                else{
                    return response()->json('can\'t update ', 501);
                }
            }
            else {
                return response()->json('can\'t find', 502);
            }
        } catch (SoapFault $e) {
            echo $client->__getLastResponse();
            die('SOAP Error: '.$e->getMessage());
        }
    }


    public function delete($id,$mail)
    {
        if($id == 'root'){
            return response()->json('Error: cant delete system user root', 504);
        }
        $username = env('HERMES_EMAILAPI_USER');
        $password = env('HERMES_EMAILAPI_PASS');
        $soap_location = env('HERMES_EMAILAPI_LOC');
        $soap_uri = env('HERMES_EMAILAPI_URI');
        $client = new \SoapClient(null, array('location' => $soap_location,
                'uri'      => $soap_uri,
                'trace' => 1,
                'stream_context'=> stream_context_create(array('ssl'=> array('verify_peer'=>false,'verify_peer_name'=>false))),
                'exceptions' => 1));
        try {
            if($session_id = $client->login($username, $password)) {
                // Parameters
                $mailuser_id = 1;
                $affected_rows = $client->mail_user_delete($session_id, $mailuser_id);

                $command = "uux -j  . env('HERMES_ROUTE') . '!uuadm -d -m "  . $id . '@' . env('HERMES_DOMAIN') .  "'" ;
                if (!$output = exec_cli($command) ){
					//returns uucp job id
					$output = explode("\n", $output)[0];
                   	return response()->json($output, 203); //deleted
				}
				else {
              		return response('Hermes delete user: create user table and ispconfig but Error on uucp to advise: ' . $output . $command, 300);
				}
                $client->logout($session_id);
				if ($affected_rows > 0){
                	if( User::firstWhere('email', $mail)){
                    	if (User::where('email', $mail)->delete()){
                			$command = "uux -j '" . env('HERMES_ROUTE') . "!uuadm -d -m "  . $mail . '@' . env('HERMES_DOMAIN') .  "'" ;
                			if ($output = exec_cli($command) ){
								//returns uucp job id
								$uucp_job_id= explode("\n", $output)[0];
                        		return response()->json($uucp_job_id , 200);
							}
							else {
              					return response('Hermes delete user: create user and email. but has trouble to advise to central by uucp: ' . $output . $command, 300);
							}
                    	}
                    	else {
                        	return response()->json('can\'t delete', 500);
                    	}
                	}
                	else {
                    	return response()->json('can\'t find', 404);
                	}
				}
				else {
                    	return response()->json('can\'t remove email from server', 404);
				}

            }
            else{
                return response()->json('Error: cant login on ISP' + $id, 504);
            }
        } catch (SoapFault $e) {
            echo $client->__getLastResponse();
            die('SOAP Error: '.$e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $user = new User;
        if ($request->email){
            if ($user = User::firstWhere('email', $request->email)){
                if ($user['password'] == hash('sha256', $request->password)){ //sucessfull login
                    unset($user['password']);
                    unset($user['recoverphrase']);
                    unset($user['recoveranswer']);
                    unset($user['created_at']);
                    unset($user['updated_at']);
                    return response()->json($user, 200);
                }
                else{//fail
                    return response()->json('wrong password', 500);
                }
            }
            else{ //fail
                return response()->json('wrong user', 404);
            }
        }
        else //fail
        {
            return response()->json('lack parameters', 500);
        }
    }
}