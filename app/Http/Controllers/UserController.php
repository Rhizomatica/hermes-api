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
			(new ErrorController)->saveError(get_class($this), 404, 'Could not find user');
			return response()->json(['message' => 'Not found'], 404);
		} else {
			return response()->json(['message' => $user], 200);
		}
	}

	public function create(Request $request)
	{
		$request['email'] = strtolower($request['email']);

		$user = User::firstWhere('email', $request['email']);
		if ($user) {
			(new ErrorController)->saveError(get_class($this), 409, 'User already exists');
			return response()->json(['message' => 'Server error'], 409);
		}

		$pass = $request['password'];
		$email = $request['email'] . '@' . env('HERMES_DOMAIN');

		$request['password'] = hash('sha256', $request['password']);

		$user = User::create($request->all());

		if (!$user) {
			(new ErrorController)->saveError(get_class($this), 500, 'Could not create user');
			return response()->json(['message' => 'Server error'], 500);
		}

		exec_cli_no("sudo email_create_user {$email} {$pass}");

		return response()->json(['data' => 'success'], 201);
	}

	public function update($id, Request $request)
	{
		$user = User::firstWhere('id', $id);

		if (!$user) {
			(new ErrorController)->saveError(get_class($this), 404, 'User id not found on database');
			return response()->json(['message' => 'Server error'], 404);
		}

		$email = $request['email'] . '@' . env('HERMES_DOMAIN');
		//Can't update email (remove)
		$request->request->remove('email');

		if ($request['password']) {
			$password = $request['password'];
			$request['password'] = hash('sha256', $password);

			exec_cli_no("sudo email_update_user {$email} {$password}");
		}

		$user = $user->update($request->all());

		if (!$user) {
			(new ErrorController)->saveError(get_class($this), 500, 'User could not be updated');
			return response()->json(['message' => 'Server error'], 500);
		}

		return response()->json($user, 200);
	} // end of update function

	public function delete($id, $mail)
	{
		// why here is not using the id?
		$user = User::firstWhere('email', $mail);

		// separate here, if the user is root, dont error out, just dont delete it, and the UI should show a graceful message
		if (!$user || $user->email == 'root') {
			(new ErrorController)->saveError(get_class($this), 403, 'API cant delete USER root');
			return response()->json(['message' => 'Server error'], 403);
		}

		$user->delete();

		$email = $mail . '@' . env('HERMES_DOMAIN');

		exec_cli_no("sudo email_delete_user {$email}");

		return response()->json(0, 200);
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
			return response()->json(['message' => 'Server error'], 500);
		}

		if ($user['password'] !== hash('sha256', $request->password)) {
			return response()->json(['message' => 'Server error'], 500);
		}

		unset($user['password']);
		unset($user['created_at']);
		unset($user['updated_at']);

		return response()->json($user, 200);
	}


	public function verifyRequiredData($request)
	{

		$validated = $this->validate($request, [
			'admin' => 'required',
			'email' => 'required|unique:users',
			'name' => 'required|max:255',
			'password' => 'required|max:255',
			'phone' => 'nullable|max:12',
			'location' => 'nullable|max:32'
		]);

		if (!$validated) {
			(new ErrorController)->saveError(get_class($this), 412, $validated);
			return false;
		}

		return true;
	}
}
