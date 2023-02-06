<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Error;

class ErrorController extends Controller
{
	public function getErrors()
	{
		return response()->json(Error::all());
	}

	// public function getErrorByUser($id)
	// {
	// 	if (!$error = Error::firstWhere('user_id', $id)) {
	// 		return response()->json(['message' => 'Error'], 404);
	// 	} else {
	// 		return response()->json(['message' => $error], 200);
	// 	}
	// }

	public function saveError($controller, $error_code, $error_message, $stacktrace = null)
	{
		$log = new Error();
		// $log->user_id = 0;
		$log->controller = $controller;
		$log->error_code = $error_code;
		$log->error_message = $error_message;
		$log->stacktrace = $stacktrace;
		$log->station = env('HERMES_NAME');
		$log->save();

		Log::error($error_message);
	}
}
