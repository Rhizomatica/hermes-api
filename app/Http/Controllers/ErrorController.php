<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\CustomError;

class ErrorController extends Controller
{
	public function getCustomErrors()
	{
		return response()->json(CustomError::all());
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
		$customError = new CustomError;
		// $log->user_id = 0;
		$customError->controller = $controller;
		$customError->error_code = $error_code;
		$customError->error_message = $error_message;
		$customError->stacktrace = $stacktrace;
		$customError->station = env('HERMES_NAME');
		$customError->save();

		Log::error($error_message);
	}

	public function deleteCustomError($id)
	{
		if (!$id) {
			CustomError::truncate();
			return response()->json(['data' => 'Success'], 200);
		}

		$error = CustomError::findOrFail($id);

		if (!$error->id) {
			$this->saveError('ErrorController', 404, 'Custom error not found');
			return response()->json(['data' => 'Not found'], 404);
		}

		$error->delete();

		return response()->json(['data' => 'Success'], 200);
	}

}
