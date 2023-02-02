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

            if($output == 'NO_GPS')
                return response()->json(['message' => 'No GPS found for calibration: ' . $output], 500);

            if(!$output || $output == 'ERROR')
                return response()->json(['message' => 'Fail on start GPS calibration: ' . $output], 500);
    
            return response()->json(['message' => 'GPS Calibration successful'], 200);
            
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Internal Server Error:' . $th], 500);
        }
      
    }
}