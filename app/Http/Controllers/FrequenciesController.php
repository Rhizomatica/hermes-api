<?php

namespace App\Http\Controllers;

use App\Frequencies;
use Illuminate\Http\Request;

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
			(new ErrorController)->saveError(get_class($this), 404, 'API Error: User not find');
			return response()->json(['message' => 'Not found'], 404);
		}

		return response()->json($frequency, 200);
	}

	public function getFrequencyByAlias($alias)
	{
		$frequency = Frequencies::firstWhere('alias', $alias);

		if (!$frequency) {
			(new ErrorController)->saveError(get_class($this), 404, 'API Error: Frequency not found');
			return response()->json(['message' => 'Not found'], 404);
		}

		return response()->json($frequency, 200);
	}

	public function updateFrequency($id, Request $request)
	{
		if (empty($id)) {
			(new ErrorController)->saveError(get_class($this), 404, 'API Error: missing parameter (id)');
			return response()->json(['message' => 'Missing parameter'], 404);
		}

		$this->validate($request, [
			'frequency' => 'required|integer',
			'mode' => 'required|string',
			'enable' => 'required|boolean'
		]);

		$frequency = Frequencies::findOrFail($id);

		if ($frequency) {
			$frequency->update($request->all());
			return response()->json($request, 200);
		}

		(new ErrorController)->saveError(get_class($this), 404, 'API Error: frequency not found');
		return response()->json(['message' => 'Not Found' . $id], 404);
	}
}
