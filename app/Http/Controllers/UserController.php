<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ErrorController;
use SoapFault;

class UserController extends Controller
{
	public $controller = 'UserController';

	public function showAllUsers()
	{
		return response()->json(User::all());
	}

	public function showOneUser($id)
	{
		if (!$user = User::firstWhere('email', $id)) {
			(new ErrorController)->saveError(get_class($this), 404, 'Error: API showoneuser error, cant find');
			return response()->json(['message' => 'Not found'], 404);
		} else {
			return response()->json(['message' => $user], 200);
		}
	}

	public function create(Request $request)
	{
		$client = $this->getClientSOAP();
		$session_id = $client->login(env('HERMES_EMAILAPI_USER'), env('HERMES_EMAILAPI_PASS'));

		if (!$session_id) {
			$client->logout($session_id);
			(new ErrorController)->saveError(get_class($this), 404, 'Error: cant find user session');
			return response()->json(['message' => 'Not found'], 404);
		}

		if (!$this->verifyRequiredData($request)) {
			$client->logout($session_id);
			(new ErrorController)->saveError(get_class($this), 404, 'Error: cant update without required data form');
			return response()->json(['message' => 'Not found'], 404);
		}

		try {
			$client_id = 1;
			$request['email'] = strtolower($request['email']);

			$params = array( //TODO - Review params
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
			$mailuser_id = $client->mail_user_add($session_id, $client_id, $params);

			if (!$mailuser_id) {
				$client->logout($session_id);
				(new ErrorController)->saveError(get_class($this), 500, 'API create user error: cant create email');
				return response()->json(['message' => 'Server error'], 500);
			}

			$request['pass'] = $request['password'];
			$request['password'] = hash('sha256', $request['password']);
			$request['emailid'] = $mailuser_id;

			$user = User::create($request->all());

			if (!$user) {
				$client->logout($session_id);
				(new ErrorController)->saveError(get_class($this), 500, 'API create user error: cant create user');
				return response()->json(['message' => 'Server error'], 500);
			}

			// forward
			$ISPConfig = new ISPConfigController();
			$ISPConfig->updateForward($session_id, $client, $client_id, $request['email']);

			$client->logout($session_id);
			return response()->json(['data' => 'success'], 201);
			// return response()->json(0, 201); //Created

		} catch (SoapFault $e) {
			echo $client->__getLastResponse();
			die('SOAP Error: ' . $e->getMessage());
		}
	}

	public function update($id, Request $request)
	{
		//TODO - Talvez vire um metodo - INICIO
		$client = $this->getClientSOAP();
		$session_id = $client->login(env('HERMES_EMAILAPI_USER'), env('HERMES_EMAILAPI_PASS'));

		if (!$session_id) {
			(new ErrorController)->saveError(get_class($this), 504, 'Error: cant find user session');
			return response()->json(['message' => 'Server error'], 504);
		}
		//FIM

		if (!$this->verifyRequiredData($request)) {
			(new ErrorController)->saveError(get_class($this), 504, 'Error: cant update without required data form');
			return response()->json(['message' => 'Server error'], 504);
		}

		// TODO - Enviar email na requisicao
		$user = User::firstWhere('email', $request['email']);

		if (!$user) {
			(new ErrorController)->saveError(get_class($this), 504, 'Error: mail id not found on database');
			return response()->json(['message' => 'Server error'], 504);
		}

		if ($user->name == 'root') {
			(new ErrorController)->saveError(get_class($this), 500, 'API update user error: cant update root');
			return response()->json(['message' => 'Server error'], 500);
		}

		try {
			// Get the email user record
			//TODO - Talvez vira um metodo (UPDATE ISPCONFIG) - INICIO
			$mail_user_record = $client->mail_user_get($session_id, $id);
			$mail_user_record['name'] = $request['name'];
			$mail_user_record['password'] = $request['password'];
			$mail_user_record['phone'] = $request['phone'];

			$client_id = 1; //TODO - PQ ISSO?

			//Update the email record
			$affected_rows = $client->mail_user_update($session_id, $client_id, $id, $mail_user_record);
			$client->logout($session_id);

			if ($affected_rows <= 0) {
				(new ErrorController)->saveError(get_class($this), 501, 'API update user error: cant update');
				return response()->json(['message' => 'Server error'], 501);
			}
			//FIM

			$request['password'] = hash('sha256', $request['password']);
			$request->request->remove('email'); //Can't update email (remove)
			// unset($request['email']); //Se nao funcionar o anterior
			$user = $user->update($request->all());

			if (!$user) {
				(new ErrorController)->saveError(get_class($this), 501, 'API update error: ispconfig updated but not local database');
				return response()->json(['message' => 'Server error'], 501);
			}

			return response()->json($user, 200);
		} catch (SoapFault $e) {
			echo $client->__getLastResponse();
			die('SOAP Error: ' . $e->getMessage());
		}
	} // end of update function

	public function delete($id, $mail)
	{
		$client = $this->getClientSOAP();
		$session_id = $client->login(env('HERMES_EMAILAPI_USER'), env('HERMES_EMAILAPI_PASS'));

		if (!$session_id) {
			(new ErrorController)->saveError(get_class($this), 504, 'Error: cant find user session');
			return response()->json('Not found', 504);
		}

		$user = User::firstWhere('email', $mail);

		if (!$user || $user->name == 'root') {
			(new ErrorController)->saveError(get_class($this), 500, 'API delete user error: cant delete USER');
			return response()->json(['message' => 'Server error'], 500);
		}

		try {
			// Parameters
			$affected_rows = $client->mail_user_delete($session_id, $id);

			if ($affected_rows <= 0) {
				(new ErrorController)->saveError(get_class($this), 405, 'API user delete error - cant remove email from server');
				return response()->json(['message' => 'Not found'], 405);
			}

			$ISPConfig = new ISPConfigController();
			$ISPConfig->removeForward($session_id, $client, $mail);
			$client->logout($session_id);

			$user->delete();
			return response()->json(0, 200);
		} catch (SoapFault $e) {
			echo $client->__getLastResponse();
			die('SOAP Error: ' . $e->getMessage());
		}
	}


	/**
	 * login
	 * parameter: $request with email and password
	 * @return Json
	 */
	public function login(Request $request)
	{
		if (!$request->email) {
			(new ErrorController)->saveError(get_class($this), 412, 'API user login - lack parameters');
			return response()->json(['message' => 'Server Error'], 412);
		}

		$user = User::firstWhere('email', $request->email);

		if (!$user) {
			(new ErrorController)->saveError(get_class($this), 404, 'API user login - wrong user');
			return response()->json(['message' => 'Not found'], 404);
		}

		if ($user['password'] !== hash('sha256', $request->password)) { //sucessfull login
			(new ErrorController)->saveError(get_class($this), 420, 'API user login - wrong password');
			return response()->json(['message' => 'Server error'], 420);
		}

		unset($user['password']);
		// unset($user['recoverphrase']); //TODO - Remove field
		// unset($user['recoveranswer']); //TODO - Remove field
		unset($user['created_at']);
		unset($user['updated_at']);

		return response()->json($user, 200);
	}


	public function verifyRequiredData($request)
	{
		$validated = $this->validate($request, [
			'email' => 'required|unique:user',
			'name' => 'required|max:255',
			'password' => 'required|max:255',
			'phone' => 'nullable|max:12'
		]);

		if (!$validated) {
			(new ErrorController)->saveError(get_class($this), 404, $validated);
			return false;
		}

		return true;
	}
}
