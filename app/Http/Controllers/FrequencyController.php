<?php

namespace App\Http\Controllers;

use App\Frequency;
use Log;
use Illuminate\Http\Request;


use function PHPUnit\Framework\isEmpty;

class FrequencyController extends Controller
{

	public function getAllFrequency()
	{
		return response()->json(Frequency::all());
	}

	public function getFrequency($id)
	{
		if (!$frequency = Frequency::firstWhere('id', $id)) {
			return response()->json(['message' => 'API show frequency error, cant find'], 404);
		} else {
			return response()->json($frequency, 200);
		}
	}

	public function updateFrequency($id, Request $request)
	{
		if (isEmpty($id)) {
			return response()->json(['message' => 'API update user error: cant update frequency'], 500);
		}

		$this->validate($request, [
			'frequency' => 'required|integer',
			'mode' => 'required|string',
			'nickname' => 'required|string',
			'enable' => 'required|boolean'
		]);

		if ($frequency = Frequency::findOrFail($id)) {
			$frequency->update($request->all());
			Log::info('update frequency' . $id);
			return response()->json($request, 200);
		} else {
			Log::warning('frequency cant find to update' . $id);
			return response()->json(['message' => 'cant find this frequency' . $id], 404);
		}
	}
}
