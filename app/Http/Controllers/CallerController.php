<?php

namespace App\Http\Controllers;

use App\Caller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

//TODO - RENAME TO SCHEDULE OR KEEP CALLER NOUN
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
		$this->validate($request, [
			'title' => 'required|unique:caller',
			'stations' => 'required|array',
			'starttime' => 'required|date_format:H:i:s|before:stoptime',
			'stoptime' => 'required|date_format:H:i:s|after:starttime',
			'enable' => 'required|boolean'
		]);

		$schedule = Caller::create($request->all());

		if (!$schedule) {
			(new ErrorController)->saveError(get_class($this), 500, 'API Error: can not create a schedule');
			return response()->json(['message' => 'API Server error'], 500);
		}

		return response()->json($request->all(), 200);
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
		}

		(new ErrorController)->saveError(get_class($this), 500, 'API Error: can not find schedule to update');
		return response()->json(['message' => 'Not found'], 404);
	}

	/**
	 * deleteSched - deleteScheduler
	 * parameter: scheduler id
	 * @return Json
	 */
	public function deleteSched($id)
	{
		Caller::findOrFail($id)->delete();
		return response()->json(['message' => 'Delete sucessfully' . $id], 200);
	}
}
