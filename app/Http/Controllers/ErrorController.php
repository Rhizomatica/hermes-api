<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Error;
use Illuminate\Support\Facades\App;

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
		// $error = new Error;
		$error = App::call([new Error, 'generate']);
		// $log->user_id = 0;
		$error->controller = $controller;
		$error->error_code = $error_code;
		$error->error_message = $error_message;
		$error->stacktrace = $stacktrace;
		$error->station = env('HERMES_NAME');
		$error->save();

		Log::error($error_message);
	}
}
