<?php

namespace App\Http\Controllers;

use App\Frequencies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrequenciesController extends Controller
{

	public function getFrequencies()
	{
		return response()->json(Frequencies::all());
	}

	public function getFrequency($id)
	{
		$frequency = Frequencies::firstWhere('id', $id);

		if (!$frequency) {
			return response()->json(['message' => 'API show frequency error, cant find'], 404);
		}

		return response()->json($frequency, 200);
	}

	public function getFrequencyByAlias($alias)
	{
		$frequency = Frequencies::firstWhere('alias', $alias);

		if (!$frequency) {
			return response()->json(['message' => 'API show frequency error, cant find'], 404);
		}

		return response()->json($frequency, 200);
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

		$frequency = Frequencies::findOrFail($id);

		if ($frequency) {
			$frequency->update($request->all());
			Log::info('update frequency' . $id);
			return response()->json($request, 200);
		}

		Log::warning('frequency cant find to update' . $id);
		return response()->json(['message' => 'cant find this frequency' . $id], 404);
	}
}
