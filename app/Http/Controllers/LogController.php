<?php

namespace App\Http\Controllers;

use App\Log;
// use App\User;

class LogController extends Controller
{
    public function getLogs()
    {
		return response()->json(Log::all());
    }

    public function getLogByUser($id)
	{
		if (!$log = Log::firstWhere('user_id', $id)) {
			return response()->json(['data' => 'Error'], 404);
		} else {
			return response()->json(['data' => $log], 200);
		}
	}

    public function saveLog($controller, $error_code, $error_message, $stacktrace = null)
    {
        $client = (new UserController)->getClientSOAP(); //REVER

		var_dump($stacktrace);
		var_dump($client);
		die();

        // $log = new Log();
		// $log->controller = $controller;
		// $log->error_code = $error_code;
		// $log->error_message = $error_message;
		// $log->stacktrace = $stacktrace;
		// $log->user_id = $client->name; //TODO - Verificar usuario id da sessao
		// $log->station_id = $client->station; //TODO - Verificar usuario id da sessao
        // $log->save();
    }

	// public function getUser(){
	// 	$user = User::firstWhere('email', $id);
	// }

    //REPETIDO (Possivelmente nova classe)
    // public static function getClientSOAP()
	// {
	// 	return new \SoapClient(null, array(
	// 		'location' => env('HERMES_EMAILAPI_LOC'),
	// 		'uri'      => env('HERMES_EMAILAPI_URI'),
	// 		'trace' => 1,
	// 		'stream_context' => stream_context_create(array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false))),
	// 		'exceptions' => 1
	// 	));
	// }
}
