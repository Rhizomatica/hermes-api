<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

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

        $content = file_get_contents($file);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Pragma', 'public')
            ->header('Content-Disposition', 'inline; filename="' . $name)
            ->header('Cache-Control', 'max-age=60, must-revalidate');
    }

    public function getCurrentCoordinates()
    {
        $commandGetLatitude = 'gpspipe -w -n 4  | jq -r .lat | grep "[[:digit:]]" | tail -1';
        $outputLatitude = exec_cli($commandGetLatitude);

        if ($outputLatitude == 'ERROR') {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error getting the current coordinates' . $outputLatitude);
            return response()->json(['message' => 'Server error'], 500);
        }

        $commandGetLongitude = 'gpspipe -w -n 4  | jq -r .lon | grep "[[:digit:]]" | tail -1';
        $outputLongitude = exec_cli($commandGetLongitude);

        if ($outputLongitude == 'ERROR') {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error getting the current coordinates' . $outputLatitude);
            return response()->json(['message' => 'Server error'], 500);
        }

        $coordinates = [
            'latitude' => $outputLatitude,
            'longitude' => $outputLongitude
        ];

        return response()->json(['message' => $coordinates], 200);
    }

    public function setGPSStoringInterval(int $seconds)
    {
        if($seconds < 1 || $seconds > 120){
            return response()->json(['message' => 'Server error'], 500);
        }

        $command = '';
        $output = exec_cli($command);

        if ($output == 'ERROR') {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error setting GPS storing interval' . $output);
            return response()->json(['message' => 'Server error'], 500);
        }

        return response()->json(['message' => $output], 200);
    }

    public function setGPSFileRangeTime($seconds)
    {
        if($seconds < 600 || $seconds > 3600){
            return response()->json(['message' => 'Server error'], 500);
        }

        $command = '';
        $output = exec_cli($command);

        if ($output == 'ERROR') {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error setting GPS file range time' . $output);
            return response()->json(['message' => 'Server error'], 500);
        }

        return response()->json(['message' => $output], 200);
    }

    public function setStoringGPSStatus(bool $status)
    {
        if($status !== true && $status !== false){
            return response()->json(['message' => 'Server error'], 500);
        }

        if ($status == true)
            $command = 'systemctl enable sensors';
        if ($status == false)
            $command = 'systemctl disable sensors';

        $output = exec_cli($command);

        if ($output == 'ERROR') {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error setting GPS storing status' . $output);
            return response()->json(['message' => 'Server error'], 500);
        }

        return response()->json(['message' => $output], 200);
    }

    public function deleteStoredFiles()
    {
        $command = 'clean captured GPS files';
        $output = exec_cli_no($command);

        if ($output == 'ERROR') {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error during deleting GPS stored files' . $output);
            return response()->json(['message' => 'Server error'], 500);
        }

        $commandRM = 'rm -f' . $this->gpsFilesPath . '*';
        $outputRM = exec_cli_no($commandRM);

        if ($outputRM == 'ERROR') {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error during deleting GPS stored files' . $output);
            return response()->json(['message' => 'Server error'], 500);
        }

        return response()->json(['message' => 'Stored files deleted successfully'], 200);
    }
}
