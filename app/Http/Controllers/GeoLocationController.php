<?php

namespace App\Http\Controllers;

class GeoLocationController extends Controller
{
    /**
     *  Start GPS Calibration 
     *
     * @return Json GPS
     */
    public function startGPSCalibration()
    {
        try {
            $command = 'ubitx_client -c gps_calibrate';
            $output = exec_cli($command);

            if ($output == 'NO_GPS') {
                (new ErrorController)->saveError(get_class($this), 500, 'API Error: No GPS found for calibration: ' . $output);
                return response()->json(['message' => 'Server error'], 500);
            }

            if (!$output || $output == 'ERROR') {
                (new ErrorController)->saveError(get_class($this), 500, 'API Error: Fail on start GPS calibration: ' . $output);
                return response()->json(['message' => 'Server error'], 500);
            }

            return response()->json(['message' => 'GPS calibration sucessfully'], 200);
        } catch (\Throwable $th) {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: nternal Server Error: ' . $th);
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}
