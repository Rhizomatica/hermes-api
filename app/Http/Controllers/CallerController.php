<?php

namespace App\Http\Controllers;

use Log;
use App\Caller;
use Illuminate\Http\Request;

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

	if ( ! $request->station || ! $request->starttime || ! $request->stoptime || ! $request->enable || ! $request->delay){
        	return response()->json(['message' => 'caller: require all fields ' ], 500);
	}

	$schedule = Caller::create($request->all());
        if (! $schedule ){
        	return response()->json(['message' => 'caller: cant\'t create a schedule: ' ], 500);
	}
        else {
		//Log::info('creating schedule ' . $schedule);
        	return response()->json( $request->all() , 200);
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
        if($schedule = Sched::findOrFail($id)){
        	$schedule->update($request->all());
		Log::info('update schedule' . id);
        	return response()->json($user, 200);
        }
        else{
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
        Caller::findOrFail($id)->delete();
	Log::info('delete schedule ' . $id);
        return response()->json(['message' => 'Delete sucessfully schedule: ' . $id], 200);
    }

    /**
     * set enable  
     * parameter: schedule id
     *
     * @return Json
     */
    public function setEnable($id)
   {
		// return response()->json(['unhide' . $id . 'Sucessfully'], 200);
    }

}

        // log::info('hide schedule ' . $id);
