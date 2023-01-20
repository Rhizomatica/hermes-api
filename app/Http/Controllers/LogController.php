<?php

namespace App\Http\Controllers;

use App\Log;
use Illuminate\Http\Request;

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

        $client = $this->getClientSOAP();
		$session_id = $client->login(env('HERMES_EMAILAPI_USER'), env('HERMES_EMAILAPI_PASS'));

        $log = new Log();
		$log->controller = $controller;
		$log->error_code = $error_code;
		$log->error_message = $error_message;
		$log->stacktrace = $stacktrace;
		$log->user_id = $client; //TODO - Verificar usuario id da sessao
        $log->save();
    }

    //REPETIDO (Possivelmente nova classe)
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
}
