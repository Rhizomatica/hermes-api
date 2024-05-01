<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GeoLocationController extends Controller
{
    /**
     *  Start GPS Calibration 
     *
     * @return Json GPS
     */

    public $gpsFilesPath = '/var/spool/sensors/';

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

    public function getStoredLocationFilesFromPath()
    {
        $paths = [];
        if (is_dir($this->gpsFilesPath)) {
            $scanpathvalues = scandir($this->gpsFilesPath);
            if (is_array($scanpathvalues) || is_object($scanpathvalues)) {
                foreach ($scanpathvalues as $name) {
                    if ($name !== '.' && $name !== '..' && !is_dir($this->gpsFilesPath . '/' . $name)) {
                        $paths[] = $name;
                    }
                }
            }
        }
        return response()->json(['message' => $paths], 200);
    }


    public function getStoredLocationFileByName(string $name)
    {
        if (!$name) {
            return "missing file name";
        }

        $file = basename($name);
        $file = $this->gpsFilesPath . $file;

        if (!file_exists($file)) {
            return 'file not found';
        }

        $headers = ['Content-Type' => 'text/csv'];

        return new BinaryFileResponse($file, 200, $headers);
    }
}
