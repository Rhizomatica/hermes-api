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
    public $zipFilesPath = '/var/spool/sensors-zip/';

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

    public function getStoringGPSStatus()
    {
        $command = 'sudo systemctl is-active --quiet sensors';
        $output = exec_cli_no($command);

        return response()->json($output, 200);
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
        return response()->json($paths, 200);
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

    public function getStoredLocationAllFiles()
    {
        $command = 'sudo mkdir -p ' . $this->zipFilesPath;
        $output = exec_cli_no($command);

        $fileName = $this->zipFilesPath . "storedGPSFiles-" . date("Y-m-d") . ".zip";
        $command = 'sudo zip -j ' . $fileName . ' ' . $this->gpsFilesPath . '*';
        $output = exec_cli_no($command);

        $content = file_get_contents($fileName);

        $file = basename($fileName);

        return response($content)
            ->header('Content-Type', 'application/zip')
            ->header('Pragma', 'public')
            ->header('Content-Disposition', 'inline; filename="' . $file)
            ->header('Cache-Control', 'max-age=60, must-revalidate');
    }

    public function getCurrentCoordinates()
    {
        $commandGetCoords = 'gpspipe -w -n 5 | grep -m 1 TPV | jq -r "[.lat, .lon] | @csv"';
        $outputCoords = exec_cli($commandGetCoords);

        if ($outputCoords == 'ERROR') {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error getting the current coordinates' . $outputCoords);
            return response()->json(['message' => 'Server error'], 500);
        }

        $outputLatitude = explode(',', trim($outputCoords))[0];
        $outputLongitude = explode(',', trim($outputCoords))[1];

        $coordinates = [
            'latitude' => $outputLatitude,
            'longitude' => $outputLongitude
        ];

        return response()->json($coordinates, 200);
    }

    public function setGPSStoringInterval(int $seconds)
    {
        if ($seconds < 1 || $seconds > 120) {
            return response()->json(['message' => 'Server error'], 500);
        }

        $command = '';
        $output = exec_cli($command);

        if ($output == 'ERROR') {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error setting GPS storing interval' . $output);
            return response()->json(['message' => 'Server error'], 500);
        }

        return response()->json($output, 200);
    }

    public function setGPSFileRangeTime($seconds)
    {
        if ($seconds < 600 || $seconds > 3600) {
            return response()->json(['message' => 'Server error'], 500);
        }

        $command = '';
        $output = exec_cli($command);

        if ($output == 'ERROR') {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error setting GPS file range time' . $output);
            return response()->json(['message' => 'Server error'], 500);
        }

        return response()->json($output, 200);
    }

    public function setStoringGPSStatus(bool $status)
    {
        if ($status !== true && $status !== false) {
            return response()->json(['message' => 'Server error'], 500);
        }

        if ($status == true) {
            $command = 'sudo systemctl enable sensors';
            $output = exec_cli_no($command);             // we just hope for the best here
            $command = 'sudo systemctl start sensors';
            $output = exec_cli_no($command);
        }

        if ($status == false) {
            $command = 'sudo systemctl disable sensors';
            $output = exec_cli_no($command);            // we just hope for the best here
            $command = 'sudo systemctl stop sensors';
            $output = exec_cli_no($command);
        }

        if ($output == false) {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error setting GPS storing status');
            return response()->json(['message' => 'Server error'], 500);
        }

        return response()->json($output, 200);
    }

    public function deleteStoredFiles()
    {
        $commandRM = 'sudo rm -f ' . $this->gpsFilesPath . '*';
        $outputRM = exec_cli_no($commandRM);

        if ($outputRM == false) {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error during deleting GPS stored files');
            return response()->json(['message' => 'Server error'], 500);
        }

        $commandRM = 'sudo rm -f ' . $this->zipFilesPath . '*';
        $outputRM = exec_cli_no($commandRM);

        if ($outputRM == false) {
            (new ErrorController)->saveError(get_class($this), 500, 'API Error: Error during deleting GPS stored files');
            return response()->json(['message' => 'Server error'], 500);
        }

        return response()->json(['message' => 'Stored files deleted successfully'], 200);
    }
}
