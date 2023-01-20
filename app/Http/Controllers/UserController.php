<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;

// Dividindo as responsabilidades
// Removendo redundancias lógicas
// Removendo repetiçao de códigos
// Removendo processamentos desnecessários (priorização de execução)
// Facilitando validacoes
// Definindo Padrao de retorno para o usuário
// Planejando armazenamento de logs (tabela de log de erros?)

class UserController extends Controller
{

	use LogController;

	public function showAllUsers()
	{
		return response()->json(User::all());
	}

	public function showOneUser($id)
	{
		if (!$user = User::firstWhere('email', $id)) {
			LogController::saveLog('Error: API showoneuser error, cant find', 404);
			return response()->json(['data' => 'Error'], 404);
		} else {
			return response()->json(['data' => $user], 200);
		}
	}

	public function create(Request $request)
	{
		$client = $this->getClientSOAP();
		$session_id = $client->login(env('HERMES_EMAILAPI_USER'), env('HERMES_EMAILAPI_PASS'));

		if (!$session_id) {
			LogController::saveLog('Error: cant find user session', 504);
			return response()->json(['data' => 'Error'], 404);
		}

		if (!$this->verifyRequiredData($request)) {
			return response()->json('Error: cant update without required data form', 504);
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
				return response()->json(['message' => 'API create user error: cant create email'], 500);
			}

			$request['pass'] = $request['password'];
			$request['password'] = hash('sha256', $request['password']);
			$request['emailid'] = $mailuser_id;

			$user = User::create($request->all());

			if (!$user) {
				$client->logout($session_id);
				return response()->json(['message' => 'API create user error: cant create user'], 500);
			}

			// forward
			$ISPConfig = new ISPConfigController();
			$ISPConfig->updateForward($session_id, $client, $client_id, $request['email']);

			$client->logout($session_id);
			return response()->json(0, 201); //Created

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
			return response()->json('Error: cant find user session', 504);
		}
		//FIM

		if (!$this->verifyRequiredData($request)) {
			return response()->json('Error: cant update without required data form', 504);
		}

		// TODO - Enviar email na requisicao
		$user = User::firstWhere('email', $request['email']);

		if (!$user) {
			return response()->json('Error: mail id not found on database', 504);
		}

		if ($user->name == 'root') {
			return response()->json(['message' => 'API update user error: cant update root'], 500);
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
				return response()->json(['message' => 'API update user error: cant update'], 501);
			}
			//FIM

			//TODO - Verificar como vem a senha (pode dar problema se vier criptografada)
			$request['password'] = hash('sha256', $request['password']);
			$request->request->remove('email'); //Can't update email (remove)
			$user = $user->update($request->all());

			if (!$user) {
				return response()->json(['message' => 'API update error: ispconfig updated but not local database'], 501);
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
			return response()->json('Error: cant find user session', 504);
		}

		$user = User::firstWhere('email', $mail);

		if (!$user || $user->name == 'root') {
			return response()->json(['message' => 'API delete user error: cant delete USER'], 500); //TODO - Verificar retorno
		}

		try {
			// Parameters
			$affected_rows = $client->mail_user_delete($session_id, $id);

			if ($affected_rows <= 0) {
				return response()->json(['message' => 'API user delete error - cant remove email from server'], 405);
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
			return response()->json(['message' => 'API user login - lack parameters'], 412);
		}
		
		$user = User::firstWhere('email', $request->email);
		
		if (!$user) {
			return response()->json(['message' => 'API user login - wrong user'], 404);
		}

		if ($user['password'] !== hash('sha256', $request->password)) { //sucessfull login
			return response()->json(['message' => 'API user login - wrong password'], 420);
		}

		unset($user['password']);
		unset($user['recoverphrase']);
		unset($user['recoveranswer']);
		unset($user['created_at']);
		unset($user['updated_at']);

		return response()->json($user, 200);
	}

	public function getClientSOAP()
	{
		return new \SoapClient(null, array(
			'location' => env('HERMES_EMAILAPI_LOC'),
			'uri'      => env('HERMES_EMAILAPI_URI'),
			'trace' => 1,
			'stream_context' => stream_context_create(array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false))),
			'exceptions' => 1
		));
	}

	public function verifyRequiredData($request)
	{
		if ($request['email'] && $request['name'] && $request['password'] && $request['phone']) {
			return true;
		}

		return false;
	}
}
