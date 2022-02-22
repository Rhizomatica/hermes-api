<?php

namespace App\Http\Controllers;

use Log;
use App\Message;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Http\FileUploader;
use Illuminate\Http\Validate;

use Illuminate\Support\Facades\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class FileController extends Controller
{

	/**
	 * uploadFile
	 * parameter: http request
	 * @return Json
	 */
	public function uploadFile( Request $request)
	{
		 $this->validate($request, [
			 //TODO check if really need this
			 //'fileup' => 'required|file|max:' . env('MAX_FILE_SIZE') . '| mimes:jpg,png,ogg,mp3,mp4,wav',
			  'fileup' => 'required|file',
			  'pass' => 'min:4|max:20',
		  ]);

		$timestamp=time();
		// get the file and info
		$file = $request->file('fileup');
		$filename = $file->getClientOriginalName();
		$mimetype = $file->getMimeType();

		// set internal path
		$origpath = 'tmp/'.$timestamp;

		// set external path
		$path = '';
		$imageout = '.vvc';
		$audioout = '.lpcnet';
		$gpgout = '.gpg';

		// get contents of the file on request
		if( $contents = file_get_contents($request->fileup)){
			// Write a new file in tmp with a timestamp with contents 
			if ( Storage::disk('local')->put($origpath, $contents)){
				$path = Storage::disk('local')->path($origpath);

				// check for file types
				// compress image
				if (preg_match("/\bimage\b/i", $mimetype)) {
					$command = 'compress_image.sh ' . $path . ' ' . $path.$imageout; 
					$output = exec_cli($command);
					$filesize = explode(":", explode("\n",$output)[5])[1];

					// delete original file 
					if ( !Storage::disk('local')->delete($origpath) ) {
						return response()->json(['message' => 'API: fileup error on delete original image file ' .  $path], 500);
					}   
					$origpath= $origpath.$imageout;
					$path = Storage::disk('local')->path($origpath);
				}
				// compress audio - script supports  wav mp3 or acc
				elseif (preg_match("/\baudio\b/i", $mimetype)) {
					$command = 'compress_audio.sh ' . $path . ' ' . $path.$audioout;; 
					$output = exec_cli($command);
					$path = Storage::disk('local')->path($origpath);

					$filesize = explode(":", explode("\n",$output)[0])[1];

					// delete original file 
					if ( Storage::disk('local')->delete($origpath) ) {
						$origpath= $origpath.$audioout;
						$path = Storage::disk('local')->path($origpath);
					}
					else{
						return response()->json(['message' => 'API: fileup error on delete original audio file ' .  $path], 500);
					}
				}
			}
			else{
				return response()->json(['message' => 'API: fileup error on write file' . 'tmp/' . $timestamp], 500);
			}
		}
		else{
			return response()->json(['message' => 'API: fileup error fileup is a file?'], 500);
		}

		// secure the file 
		if ($request->pass &&  $request->pass != 'undefined'){
			$command = 'gpg -o '. $path . '.gpg -c  --cipher-algo AES256 --symmetric --batch --passphrase "' . $request->pass. '"  --yes ' . $path;
			if ($output = exec_cli_no($command) ){
				if ( ! Storage::disk('local')->delete($origpath) ) {
					return response()->json(['message' => 'API: fileup error on delete original file ' .  $path], 500);
				}
		   		$origpath= $origpath.$gpgout;
			}
			else {
				return response()->json(['message' => 'API: fileup error on encrypt the file: ' ], 500);
			}
			$secure=true;
		}
		else{
			$secure = 'none';
		}

		// Test and create uploads  folder if it doesn't exists
		if (! Storage::disk('local')->exists('uploads')){
			if(!Storage::disk('local')->makeDirectory('uploads')){
				return response()->json(['message' => 'API: Error, can\'t find or create uploads dir'], 500);
			}
		}

		// move the file
		$internalfilename = explode('/', $origpath)[1];
		$uploadpath = 'uploads/' . $internalfilename;
		if (Storage::disk('local')->move($origpath, $uploadpath)) {
			$filesize = Storage::disk('local')->size($uploadpath);
			$path = Storage::disk('local')->path($uploadpath);
		}
		else{
			return response()->json(['message' => 'API: fileup error, couldnt complete and move the file: ' ], 500);
		}

		// Log fileup
		Log::info('API file upload: '. $filename . ' - ' . $internalfilename);

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
	} // end uploadFile

   /**
	 * downloadFile
	 * parameter: message id
	 * @return Json
	 */
	 public static function downloadFile($file)
	{
		// check for password
		request()->has('i') ? $pass=request()->i : null;

		//get message from file
		$message = Message::where('fileid', '=', $file)->latest('id')->first();

		// get timestamp
		$timestamp = explode('.', $file)[0];

		// handle real file extensions
		if (! isset(explode('.', $file)[1])){
			$fileext = '';
		}
		else{
			$fileext = '.' . explode('.', $file)[1];
		}

		// handle file nameextension
		$countdot = count(explode('.', $message->file));
		if ($countdot > 1){
			if ($countdot > 1){
				$decompressext = '.' . explode('.', $message->file)[$countdot-1];
			}
			else {
				$decompressext = '.' . explode('.', $message->file)[1];
			}
		}

		// set path
		if ($message->inbox){
			$origpath = 'downloads/' . $file;
		}
		else{
			$origpath = 'uploads/' . $file;
		}

		// get file path
		$path = Storage::disk('local')->path($origpath);
		$fullpathroot = Storage::disk('local')->path('/');

		// verify if file is GPG cryptedj
		$crypt = false;
		// teste if message is secure and pass (i) exists
		if ( $message->secure  && $pass ){
			$fullpath = $fullpathroot . 'tmp/' . $timestamp . $fileext;
			$gppath = Storage::disk('local')->path('/');
			$command = 'gpg -o  '. $fullpath . ' -d --batch --passphrase "' . $pass . '"  --yes ' . $path;
			exec_cli_no($command);
			$crypt = true;
			$origpath = 'tmp/' . $timestamp .$fileext;
		}

		// verify if file is image and decompress
		if (preg_match("/\bvvc\b/i", $file)) {
			$fullpath = $fullpathroot . $origpath;
			// decompress image
			$command = 'decompress_image.sh ' . $fullpath . ' ' . $fullpath . $decompressext; 
			// print "DEBUG uncompress command: " . $command. "\n";
			if( exec_cli_no($command)){
				$origpath = $origpath . $decompressext;
				$path = Storage::disk('local')->path($origpath);
			}
			else{
			   return response()->json(['message' => 'API: download uncompres image error: ' ], 500);
			}
			// get content of file
			$content = Storage::disk('local')->get($origpath);

			// delete generated file
			Storage::disk('local')->delete($origpath);
			return response($content)
				 ->header('Content-Type',$message->mimetype)
				 ->header('Pragma','public')
				 ->header('Content-Disposition','inline; filename="'. $message->file)
				 ->header('Cache-Control','max-age=60, must-revalidate');
		}
		// verify if file is audio  - LPCNET and decompress
		elseif (preg_match("/\blpcnet\b/i", $file)) {
			$fullpath = $fullpathroot . $origpath;
			// decompress audio
			// mount command to decompress audio
			$command = 'decompress_audio.sh ' . $fullpath . ' ' . $fullpath . $decompressext;; 
			$fullpath .= $decompressext;

			// decompress audio 
			if( exec_cli_no($command)){
				$origpath = $origpath .  $decompressext;
				$path = Storage::disk('local')->path($origpath);
			}
			else{
				return response()->json(['message' => 'API: download uncompres audio error: ' ], 500);
			}

			// get content of file
			$content = Storage::disk('local')->get($origpath);
			// delete generated file
			Storage::disk('local')->delete($origpath);
			// return response($content);
			 return response($content)
				 ->header('Content-Type', $message->mimetype)
				 ->header('Pragma','public')
				 ->header('Content-Disposition','inline; filename="'. $message->file)
				 ->header('Cache-Control','max-age=60, must-revalidate');
		}
		else {
			// $fullpath = $fullpathroot . $origpath;
			$content = Storage::disk('local')->get($origpath);
			 return response($content)
				 ->header('Content-Type', $message->mimetype)
				 ->header('Pragma','public')
				 ->header('Content-Disposition','inline; filename="'. $message->file)
				 ->header('Cache-Control','max-age=60, must-revalidate');
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

		foreach ($list as $path){

			$files = Storage::disk('local')->files($path);
			foreach ($files as $file){
		 		if (! Message::where('fileid', '=', explode($path . '/', $file)[1])->first()){
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
