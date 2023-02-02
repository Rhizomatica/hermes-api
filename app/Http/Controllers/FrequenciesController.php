<?php

namespace App\Http\Controllers;

use App\Frequencies;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class FrequenciesController extends Controller
{

	public function getFrequencies()
	{
		return response()->json(Frequencies::all());
	}

	public function getFrequency($id)
	{
		if (!$frequency = Frequencies::firstWhere('id', $id)) {
			return response()->json(['message' => 'API show frequency error, cant find'], 404);
		} else {
			return response()->json($frequency, 200);
		}
	}

	public function getFrequencyByAlias($alias)
	{
		if (!$frequency = Frequencies::firstWhere('alias', $alias)) {
			return response()->json(['message' => 'API show frequency error, cant find'], 404);
		} else {
			return response()->json($frequency, 200);
		}
	}

	public function updateFrequency($id, Request $request)
	{
		if (empty($id)) {
			return response()->json(['message' => 'API update frequency error: cant update frequency'], 500);
		}

		$this->validate($request, [
			'frequency' => 'required|integer',
			'mode' => 'required|string',
			'enable' => 'required|boolean'
		]);

		if ($frequency = Frequencies::findOrFail($id)) {
			$frequency->update($request->all());
			Log::info('update frequency' . $id);
			return response()->json($request, 200);
		} else {
			Log::warning('frequency cant find to update' . $id);
			return response()->json(['message' => 'cant find this frequency' . $id], 404);
		}
	}
}
