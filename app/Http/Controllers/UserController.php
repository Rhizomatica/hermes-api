<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

	public function showAllUsers()
	{
		return response()->json(User::all());
	}

	public function showOneUser($id)
	{
		 if (! $user = User::firstWhere('email', $id)) {
			 return response()->json(['message' => 'API showoneuser error, cant find'], 404);
		 }
		 else{
			return response()->json($user, 200);
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
			if ( $session_id = $client->login($username, $password)) {
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
					'quota' => 0,
					'cc' => '',
					'homedir' => '/var/vmail',
					'autoresponder' => 'n',
					'autoresponder_start_date' => '',
					'autoresponder_end_date' => '',
					'autoresponder_text' => NULL,
					'autoresponder_subject' => 'Out of village reply',
					'move_junk' => 'n',
					'custom_mailfilter' => 'spam',
					'postfix' => 'y',
					'access' => 'y',
					'disableimap' => 'n',
					'disablepop3' => 'n',
					'disabledeliver' => 'n',
					'disablesmtp' => 'n',
					'dbispconfig' => 1,
					'mail_user' => 0,
					'purge_trash_days' => 100,
					'purge_junk_days' => 100
				);

				//* Call the SOAP method
				if (! $mailuser_id = $client->mail_user_add($session_id, $client_id, $params)) {
					return response()->json(['message' => 'API create user error: cant create email'], 500);
				}
				$request['password'] = hash('sha256', $request['password']);
				$request['emailid'] = $mailuser_id;

				if (! $user = User::create($request->all())) {
					return response()->json(['message' => 'API create user error: cant create user'], 500);
				}
				$command = "uux -j -r '" . env('HERMES_ROUTE') . "!uuadm -a -m "  . $request['email'] . "@" . env('HERMES_DOMAIN') . " -n " . $request['name'] . "'" ;

				if (! $output = exec_cli($command)) {
					$client->logout($session_id);
					return response()->json(['message' => 'API create user error: cant advise to central'], 500);
				}
				else {
					//returns uucp job id
					$output = explode("\n", $output)[0];
					$client->logout($session_id);
					return response()->json($output, 201); //Created
				}
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
			return response()->json(['message' => 'API update user error: cant update root'], 500);
		 }

		if( $request['email']){
			return response()->json(['message' => 'API update user error: cant change an existing login email'], 500);
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
				if ($affected_rows <= 0) {
					return response()->json(['message' => 'API update user error: cant update'], 501);
				}
				if (!  $user = User::firstWhere('email', $login)) {
					return response()->json('Error: mail id not found on database', 504);
				}
				if ($request['password']){
					$request['password'] = hash('sha256', $request['password']);
				}

				if (! User::where('email', $login)->update($request->all())) {
					return response()->json(['message' => 'API update error: ispconfig updated but not local database'], 501);
				}
				$user = User::firstWhere('email', $login);
				return response()->json( $user, 200);
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
			if ( $session_id = $client->login($username, $password)) {
				// Parameters
				$affected_rows = $client->mail_user_delete($session_id, $id);

				$client->logout($session_id);
				if ($affected_rows <= 0) {
					return response()->json(['message' => 'API user delete error - cant remove email from server'], 405);
				}
				if (! User::firstWhere('email', $mail)) {
					return response()->json(['message' => 'API user delete error - cant find user'], 404);
				}
				if (!User::where('email', $mail)->delete()) {
					return response()->json(['message' => 'API user delete error '], 500);
				}
				$command = "uux -j -r '" . env('HERMES_ROUTE') . "!uuadm -d -m "  . $mail . '@' . env('HERMES_DOMAIN') .  "'" ;
				if ($output = exec_cli($command)) {
					return response()->json(['message' => 'API user delete error on uux'], 300);
				}
				//returns uucp job id
				$uucp_job_id= explode("\n", $output)[0];

				$command = "uux -j -r . env('HERMES_ROUTE') . '!uuadm -d -m "  . $id . '@' . env('HERMES_DOMAIN') .  "'" ;
				if (!$output = exec_cli($command) ){
					//returns uucp job id
					$output = explode("\n", $output)[0];
					return response()->json('uucp' . $command . ' - output: ' .  $output, 203); //deleted
				}
				else {
					return response()->json(['message' => 'API user create but fail to advise central'], 300);
				}
				return response()->json($uucp_job_id , 200);
			}
		} catch (SoapFault $e) {
			echo $client->__getLastResponse();
			die('SOAP Error: '.$e->getMessage());
		}
	}

	 /**
	 * login
	 * parameter: $request with email and password
	 * @return Json
	 */
	public function login(Request $request)
	{
		$user = new User;
		if (! $request->email) {
			return response()->json(['message' => 'API user login - lack parameters'], 412);
		}
		if (! $user = User::firstWhere('email', $request->email)) {
			return response()->json(['message' => 'API user login - wrong user'], 404);
		}
		if ($user['password'] !== hash('sha256', $request->password)){ //sucessfull login
			return response()->json(['message' => 'API user login - wrong password'], 420);
		}
		else{
			unset($user['password']);
			unset($user['recoverphrase']);
			unset($user['recoveranswer']);
			unset($user['created_at']);
			unset($user['updated_at']);
			return response()->json($user, 200);
		}
	}

	 /**
	 * recover password 
	 * parameter: $request with email and recoveranswer 
	 * @return Json
	 */
	
	public function recoverPassword(Request $request)
	{
		$user = new User;
		if (! $request->email) {
			return response()->json(['message' => 'API user recover - lack parameters'], 412);
		}
		if (! $user = User::firstWhere('email', $request->email)) {
			return response()->json(['message' => 'API user recover - wrong user'], 404);
		}

		if ($user['recoveranswer'] != $request->recoveranswer){ //sucessfull
			return response()->json(['message' => 'API user recover - wrong answer'], 420);
		}
		else{

			// unset($user['password']);
			// unset($user['recoverphrase']);
			// unset($user['recoveranswer']);
			// unset($user['created_at']);
			// unset($user['updated_at']);
			return response()->json($user, 200);
		}
	}

	 /**
	 * recover password 
	 * parameter: $request with email and recoveranswer 
	 * @return Json
	 */
	public function updateFwd(Request $request)
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
			if ( $session_id = $client->login($username, $password)) {
				//Logged successfull. Session ID:'.$session_id.'<br />';
				if ( ! isset($request['id'])) {
					return response()->json(['message' => 'API fwd update - lack parameters'], 412);
				}
				$client_id = 1;
				$forwarding_id = $request['id'];
				$mail_forward = $client->mail_forward_get($session_id, $forwarding_id);

				if (isset($request['add'])) {
					$find =  strpos($mail_forward['destination'], $request['email']);
					if ($find !== false)  {
						return response()->json(['message' => 'API fwd update - email already exists on forward'], 412);
					}
					$mail_forward['destination'] .= ', ' .  $request['email'];
				}
				elseif (isset($request['del'])){
					$find =  strpos($mail_forward['destination'], $request['email']);
					if ($find === false)  {
						return response()->json(['message' => 'API fwd update - email not found in forward'], 412);
					}
					$destination = explode(', ', $mail_forward['destination']);
					$new_destination = [];
					//remove
					foreach ( $destination as $key => $value) {
						if ($value != $request['email']) {
							array_push($new_destination, $value) ;
						}
					}
					$mail_forward['destination'] = '';
					for($i = 0; $i < count($new_destination); $i++) {
						if (count($new_destination)-1 == $i) {
							$mail_forward['destination'] .= $new_destination[$i];
						}
						else{
							$mail_forward['destination'] .= $new_destination[$i] . ', ';
						}
					}
				}
				$affected_rows = $client->mail_forward_update($session_id, $client_id, $forwarding_id, $mail_forward);
				$client->logout($session_id);
				return($mail_forward );
			}
		}
		catch (SoapFault $e) {
			echo $client->__getLastResponse();
			die('SOAP Error: '.$e->getMessage());
		}
	}

}