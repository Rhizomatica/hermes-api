<?php

namespace App\Http\Controllers;

// use Log;
use App\Caller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallerController extends Controller
{
	/**
	 *  Show all 
	 *  parameter: schedules 
	 *
	 * @return Json
	 */
	public function showAll()
	{
		return response()->json(Caller::all());
	}

	/**
	 *  show a schedule
	 *  parameter: schedule id
	 *
	 * @return Json
	 */
	public function showSched($id)
	{
		return response()->json(Caller::find($id));
	}

	/**
	 * createSched- createSched
	 * parameter: http request
	 *
	 * @return Json
	 */
	public function createSched(Request $request)
	{
		//https://laravel.com/docs/9.x/validation
		//Create Form requests to data validade and autorizing	...

		$this->validate($request, [
			'title' => 'required|unique:caller',
			'stations' => 'required|array',
			'starttime' => 'required|date_format:H:i:s|before:stoptime',
			'stoptime' => 'required|date_format:H:i:s|after:starttime',
			'enable' => 'required|boolean'
		]);

		$schedule = Caller::create($request->all());

		if (!$schedule) {
			(new LogController)->saveLog('CallerController', 404, 'caller: cant\'t create a schedule: ');
			return response()->json(['data' => 'error'], 500);
		} else {
			return response()->json($schedule, 200);
		}
	}

	/**
	 * updateScheduler
	 * parameter: id and http request
	 *
	 * @return Json
	 */
	public function updateSched($id, Request $request)
	{
		$this->validate($request, [
			'title' => 'required',
			'stations' => 'required|array',
			'starttime' => 'required|date_format:H:i:s|before:stoptime',
			'stoptime' => 'required|date_format:H:i:s|after:starttime',
			'enable' => 'required|boolean'
		]);

		$schedule = Caller::findOrFail($id);

		if ($schedule) {
			$schedule->update($request->all());
			Log::info('update schedule' . $id);
			return response()->json($request, 200);
		} else {
			Log::warning('schedule cant find to update' . $id);
			return response()->json(['message' => 'cant find  schedule' . $id], 404);
		}
	}

	/**
	 * deleteSched - deleteScheduler
	 * parameter: scheduler id
	 * @return Json
	 */
	public function deleteSched($id)
	{
		$schedule = Caller::findOrFail($id);

		if ($schedule) {
			$schedule->delete();
			Log::info('delete schedule ' . $id);
			return response()->json(['message' => 'Delete sucessfully schedule: ' . $id], 200);
		}

		Log::warning('schedule cant find to delete' . $id);
		return response()->json(['message' => 'cant find  schedule' . $id], 404);
	}
}
