<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
	public static function getClientSOAP()
	{
		return new \SoapClient(null, ['location' => env('HERMES_EMAILAPI_LOC'), 'uri'      => env('HERMES_EMAILAPI_URI'), 'trace' => 1, 'stream_context' => stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]), 'exceptions' => 1]);
	}
}
