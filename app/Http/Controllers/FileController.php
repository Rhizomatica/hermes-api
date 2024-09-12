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
		//TODO - Improve validate
		$this->validate($request, [
			'fileup' => 'required|file',
			'pass' => 'min:4|max:20'
		]);

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
		$audioout = '.lpcnet';
		$gpgout = '.gpg';

		// get contents of the file on request
		$contents = file_get_contents($request->fileup);

		if (!$contents) {
			(new ErrorController)->saveError(static::class, 500, 'API Error: fileup error fileup is a file? FILE:' . $request->fileup);
			return response()->json(['message' => 'Server error'], 500);
		}


		// Write a new file in tmp with a timestamp with contents
		$newFile = Storage::disk('local')->put($origpath, $contents);

		if (!$newFile) {
			(new ErrorController)->saveError(static::class, 500, 'API Error: fileup error on write file' . 'tmp/' . $timestamp);
			return response()->json(['message' => 'Server error'], 500);
		}

		$path = Storage::disk('local')->path($origpath);

		//TODO - Create own function
		// compress image
		if (preg_match("/\bimage\b/i", (string) $mimetype)) {
			$command = 'compress_image.sh ' . $path . ' ' . $path . $imageout;
			$output = exec_cli($command);
			$filesize = explode(":", explode("\n", (string) $output)[5])[1];

			// delete original file
			//TODO - create own function
			$deleteOldFile = Storage::disk('local')->delete($origpath);

			if (!$deleteOldFile) {
				(new ErrorController)->saveError(static::class, 500, 'API Error: fileup error on delete original image file ' .  $path);
				return response()->json(['message' => 'Server error'], 500);
			}

			$origpath = $origpath . $imageout;
			$path = Storage::disk('local')->path($origpath);
		}

		// compress audio - script supports  wav mp3 or aac
		if (preg_match("/\baudio\b/i", (string) $mimetype)) {
			$command = 'compress_audio.sh ' . $path . ' ' . $path . $audioout;
			$output = exec_cli($command);
			$filesize = explode(":", explode("\n", (string) $output)[0])[1];

			// delete original file
			$deleteOldFile = Storage::disk('local')->delete($origpath);

			if (!$deleteOldFile) {
				(new ErrorController)->saveError(static::class, 500, 'API Error: fileup error on delete original audio file ' .  $path);
				return response()->json(['message' => 'Server error'], 500);
			}

			$origpath = $origpath . $audioout;
			$path = Storage::disk('local')->path($origpath);
		}

		// secure the file
		//TODO - Create own function
		if ($request->pass &&  $request->pass != 'undefined') {
			$command = 'gpg -o ' . $path . '.gpg -c  --cipher-algo AES256 --symmetric --batch --passphrase "' . $request->pass . '"  --yes ' . $path;

			$output = exec_cli_no($command);

			if (!$output) {
				(new ErrorController)->saveError(static::class, 500, 'API Error: fileup error on encrypt the file:' . $path);
				return response()->json(['message' => 'Server error'], 500);
			}

			$deleteOldFile = Storage::disk('local')->delete($origpath);

			if (!$deleteOldFile) {
				(new ErrorController)->saveError(static::class, 500, 'API Error: fileup error on delete original file :' . $path);
				return response()->json(['message' => 'Server error'], 500);
			}

			$origpath = $origpath . $gpgout;
			$secure = true;
		} else {
			$secure = 'none';
		}

		// Test and create uploads folder if it doesn't exists
		if (!Storage::disk('local')->exists('uploads') && !Storage::disk('local')->makeDirectory('uploads')) {
			(new ErrorController)->saveError(static::class, 500, 'API Error: can not find or create uploads dir');
			return response()->json(['message' => 'Server error'], 500);
		}

		// move the file
		$internalfilename = explode('/', $origpath)[1];
		$uploadpath = 'uploads/' . $internalfilename;

		if (!Storage::disk('local')->move($origpath, $uploadpath)) {
			(new ErrorController)->saveError(static::class, 500, 'API Error: fileup error, could not complete and move the file');
			return response()->json(['message' => 'Server error'], 500);
		}

		$filesize = Storage::disk('local')->size($uploadpath);
		$path = Storage::disk('local')->path($uploadpath);

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
	 * downloadFile
	 * parameter: message id
	 * @return Json
	 */
	public static function downloadFile($file)
	{
		// check for password
		request()->has('i') ? $pass = request()->i : null;

		//get message from file
		$message = Message::where('fileid', '=', $file)->latest('id')->first();

		// get timestamp
		$timestamp = explode('.', (string) $file)[0];

		// handle real file extensions
		if (!isset(explode('.', (string) $file)[1])) {
			$fileext = '';
		} else {
			$fileext = '.' . explode('.', (string) $file)[1];
		}

		// handle file nameextension
		$countdot = count(explode('.', $message->file));
		if ($countdot > 1) {
			$decompressext = '.' . explode('.', $message->file)[$countdot - 1];
		} else {
			$decompressext = '.' . explode('.', $message->file)[1];
		}

		// set path
		if ($message->inbox) {
			$origpath = 'downloads/' . $file;
		} else {
			$origpath = 'uploads/' . $file;
		}

		// get file path
		$path = Storage::disk('local')->path($origpath);
		$fullpathroot = Storage::disk('local')->path('/');

		// teste if message is secure and pass (i) exists
		//TODO - Create own function
		if ($message->secure  && $pass) {
			$fullpath = $fullpathroot . 'tmp/' . $timestamp . $fileext;
			$gppath = Storage::disk('local')->path('/');
			$command = 'gpg -o  ' . $fullpath . ' -d --batch --passphrase "' . $pass . '"  --yes ' . $path;
			exec_cli_no($command);
			$origpath = 'tmp/' . $timestamp . $fileext;
		}

		// verify if file is image and decompress
		//TODO - Create own function
		if (preg_match("/\bvvc\b/i", (string) $file)) {
			$fullpath = $fullpathroot . $origpath;

			// decompress image
			// print "DEBUG uncompress command: " . $command. "\n";
			$command = 'decompress_image.sh ' . $fullpath . ' ' . $fullpath . $decompressext;

			if (!exec_cli_no($command)) {
				(new ErrorController)->saveError("FileController", 500, 'API Error: download uncompres image error');
				return response()->json(['message' => 'Server error'], 500);
			}

			$origpath = $origpath . $decompressext;
			$path = Storage::disk('local')->path($origpath);

			// get content of file
			$content = Storage::disk('local')->get($origpath);

			// delete generated file
			Storage::disk('local')->delete($origpath);
			return response($content)
				->header('Content-Type', $message->mimetype)
				->header('Pragma', 'public')
				->header('Content-Disposition', 'inline; filename="' . $message->file)
				->header('Cache-Control', 'max-age=60, must-revalidate');
		}

		// verify if file is audio  - LPCNET and decompress
		elseif (preg_match("/\blpcnet\b/i", (string) $file) || preg_match("/\bnesc\b/i", (string) $file)) {
			$fullpath = $fullpathroot . $origpath;

			// decompress audio
			// mount command to decompress audio
			$command = 'decompress_audio.sh ' . $fullpath . ' ' . $fullpath . $decompressext;
			$fullpath .= $decompressext;

			// decompress audio
			if (!exec_cli_no($command)) {
				(new ErrorController)->saveError("FileController", 500, 'API Error:download uncompres audio error');
				return response()->json(['message' => 'Server error'], 500);
			}

			$origpath = $origpath .  $decompressext;
			$path = Storage::disk('local')->path($origpath);

			// get content of file
			$content = Storage::disk('local')->get($origpath);

			// delete generated file
			Storage::disk('local')->delete($origpath);

			return response($content)
				->header('Content-Type', $message->mimetype)
				->header('Pragma', 'public')
				->header('Content-Disposition', 'inline; filename="' . $message->file)
				->header('Cache-Control', 'max-age=60, must-revalidate');

		} else {
			$content = Storage::disk('local')->get($origpath);
			return response($content)
				->header('Content-Type', $message->mimetype)
				->header('Pragma', 'public')
				->header('Content-Disposition', 'inline; filename="' . $message->file)
				->header('Cache-Control', 'max-age=60, must-revalidate');
		}
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

				$message = Message::where('fileid', '=', explode($path . '/', (string) $file)[1])->first();

				if (!$message) {
					$output[] = $file;
					Storage::disk('local')->delete($file);
				}
			}
		}

		Storage::disk('local')->deleteDirectory('outbox');
		Storage::disk('local')->deleteDirectory('tmp');

		return response()->json(['message' => 'delete Lost files, outbox and tmp' .  $path], 200);
	}
}
