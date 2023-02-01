<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
	/**
	 * uploadFile
	 * parameter: http request
	 * @return Json
	 */
	public function uploadFile(Request $request)
	{
		$validated = $this->validate($request, [
			//'fileup' => 'required|file|max:' . env('HERMES_MAX_FILE') . '| mimes:jpg,png,ogg,mp3,mp4,wav',
			'fileup' => 'required|file',
			'pass' => 'min:4|max:20'
		]);

		if (!$validated) {
			return response()->json(['message' => "The attached file don't match with the rules: "/* .validated.error */], 500);
		}

		$timestamp = time();
		// get the file and info
		$file = $request->file('fileup');
		$filename = $file->getClientOriginalName();
		$mimetype = $file->getMimeType();

		// set internal path
		$origpath = 'tmp/' . $timestamp;

		// set external path
		$path = '';
		$imageout = '.vvc';
		// $audioout = '.lpcnet';
		$audioout = '.nesc';

		$gpgout = '.gpg';

		// get contents of the file on request
		if ($contents = file_get_contents($request->fileup)) {
			// Write a new file in tmp with a timestamp with contents
			if (Storage::disk('local')->put($origpath, $contents)) {
				$path = Storage::disk('local')->path($origpath);

				// check for file types
				// compress image
				if (preg_match("/\bimage\b/i", $mimetype)) {
					$command = 'compress_image.sh ' . $path . ' ' . $path . $imageout;
					$output = exec_cli($command);
					$filesize = explode(":", explode("\n", $output)[5])[1];

					// delete original file
					if (!Storage::disk('local')->delete($origpath)) {
						return response()->json(['message' => 'API: fileup error on delete original image file ' .  $path], 500);
					}
					$origpath = $origpath . $imageout;
					$path = Storage::disk('local')->path($origpath);
				}
				// compress audio - script supports  wav mp3 or aac
				elseif (preg_match("/\baudio\b/i", $mimetype)) {
					$command = 'compress_audio.sh ' . $path . ' ' . $path . $audioout;
					$output = exec_cli($command);
					$path = Storage::disk('local')->path($origpath);

					$filesize = explode(":", explode("\n", $output)[0])[1];

					// delete original file
					if (Storage::disk('local')->delete($origpath)) {
						$origpath = $origpath . $audioout;
						$path = Storage::disk('local')->path($origpath);
					} else {
						return response()->json(['message' => 'API: fileup error on delete original audio file ' .  $path], 500);
					}
				}
			} else {
				return response()->json(['message' => 'API: fileup error on write file' . 'tmp/' . $timestamp], 500);
			}
		} else {
			return response()->json(['message' => 'API: fileup error fileup is a file?'], 500);
		}

		// secure the file
		if ($request->pass &&  $request->pass != 'undefined') {
			$command = 'gpg -o ' . $path . '.gpg -c  --cipher-algo AES256 --symmetric --batch --passphrase "' . $request->pass . '"  --yes ' . $path;
			if ($output = exec_cli_no($command)) {
				if (!Storage::disk('local')->delete($origpath)) {
					return response()->json(['message' => 'API: fileup error on delete original file ' .  $path], 500);
				}
				$origpath = $origpath . $gpgout;
			} else {
				return response()->json(['message' => 'API: fileup error on encrypt the file: '], 500);
			}
			$secure = true;
		} else {
			$secure = 'none';
		}

		// Test and create uploads  folder if it doesn't exists
		if (!Storage::disk('local')->exists('uploads')) {
			if (!Storage::disk('local')->makeDirectory('uploads')) {
				return response()->json(['message' => 'API: Error, can\'t find or create uploads dir'], 500);
			}
		}

		// move the file
		$internalfilename = explode('/', $origpath)[1];
		$uploadpath = 'uploads/' . $internalfilename;
		if (Storage::disk('local')->move($origpath, $uploadpath)) {
			$filesize = Storage::disk('local')->size($uploadpath);
			$path = Storage::disk('local')->path($uploadpath);
		} else {
			return response()->json(['message' => 'API: fileup error, couldnt complete and move the file: '], 500);
		}

		// Log fileup
		Log::info('API file upload: ' . $filename . ' - ' . $internalfilename);

		//finish and show return status
		return response()->json([
			'message' => 'API fileup OK',
			// 'command' => $command,
			'filename' => $filename,
			'id' => $internalfilename,
			'mimetype' => $mimetype,
			//'origpath' => $origpath,
			//'serverpath' => $path,
			'filesize' => $filesize,
			'secure' => $secure,
		], 200);
	}

	/**
	 * cleanLostFiles
	 * parameter: message id
	 * @return Json
	 */
	public static function deleteLostFiles()
	{

		// list files on upload
		$list = ['uploads', 'downloads'];

		foreach ($list as $path) {

			$files = Storage::disk('local')->files($path);
			foreach ($files as $file) {
				if (!Message::where('fileid', '=', explode($path . '/', $file)[1])->first()) {
					$output[] = $file;
					Storage::disk('local')->delete($file);
				}

				print "\n";
			}
		}
		Storage::disk('local')->deleteDirectory('outbox');
		Storage::disk('local')->deleteDirectory('tmp');

		return response()->json(['message' => 'delete Lost files, outbox and tmp' .  $path], 200);
	}
}
