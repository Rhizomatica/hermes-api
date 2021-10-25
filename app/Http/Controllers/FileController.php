<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
             'fileup' => 'required|file|max:' . env('MAX_FILE_SIZE') . '|mimes:jpg,png,ogg,mp3,mp4,wav',
             //'fileup' => 'required|file|image|max:102400|dimensions:max_width=1200,max_height=800',
             'pass' => 'filled|min:4|max:20',
         ]);

        $timestamp=time();
        // get the file and info
		$file = $request->file('fileup');
		$filename = $file->getClientOriginalName();
		$filetype = $file->getMimeType();

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
				if (preg_match("/\bimage\b/i", $filetype)) {
					$command = 'compress_image.sh ' . $path . ' ' . $path.$imageout; 
					$output = exec_cli($command);
					$filesize = explode(":", explode("\n",$output)[5])[1];

					// delete original file 
					if ( Storage::disk('local')->delete($origpath) ) {
            		    $origpath= $origpath.$imageout;
            	        $path = Storage::disk('local')->path($origpath);
					}
                    else{
        				return response()->json(['message' => 'API: fileup error on delete original image file ' .  $path], 500);
                    }   
                    
				}
				// compress audio - script supports  wav mp3 or acc
				elseif (preg_match("/\baudio\b/i", $filetype)) {
					$command = 'compress_audio.sh ' . $path . ' ' . $path.$audioout;; 
            		$path = Storage::disk('local')->path($origpath);
                    //TODO check if is ok
					$filesize = explode(":", explode("\n",$output)[5])[1];

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
        if ($secure = $request->pass){
                $command = 'gpg -o '. $path . '.gpg -c -t --cipher-algo AES256 --utf8-strings --symmetric --batch --passphrase "' . $secure . '"  --yes ' . $path;
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

        // move the file
        $internalfilename = explode('/', $origpath)[1];
        $uploadpath = 'upload/' . $internalfilename;

        if (Storage::disk('local')->move($origpath, $uploadpath)) {
            // $path = Storage::disk('local')->url($path);
            $filesize = Storage::disk('local')->size($uploadpath);
            $path = Storage::disk('local')->path($uploadpath);
        }
        else{
        	return response()->json(['message' => 'API: fileup error couldnt complete and move the file: ' ], 500);
        }

        return response()->json([
			'message' => 'API fileup OK',
			// 'command' => $command,
			'filename' => $filename,
			'serverpath' => $path,
            'filesize' => $filesize,
			'secure' => $secure,
		], 200);
    }

   /**
     * downloadFile
     * parameter: message id
     * TODO ALL
     * TODO crypt
     * @return Json
     */
     public static function downloadFile($file)
    {
        // test for name
        if (!$name= app('request')->input('name')){
            return response()->json(['message' => 'API: downloadFile error no name'], 500);
        }

        // get timestamp
        $timestamp = explode('.', $file)[0];
        $fileext = '.'. explode('.', $file)[1];
        $decompressext = '.decompress';
        $crypt = false;
        $fullpathroot = Storage::disk('local')->path('/');
        if ($fileext == '.gpg'){
            $fileext = '';
        }

        // set path
        $origpath = 'upload/' . $file;

        // get file path
        $path = Storage::disk('local')->path($origpath);

        // verify if file is GPG compressed
		if (preg_match("/\bgpg\b/i", $file)) {
            
            //test for pass 
            $fullpath = $fullpathroot . 'tmp/' . $timestamp.$fileext ;
            if ($pass = app('request')->input('pass')){
                $gppath = Storage::disk('local')->path('/');
                 $command = 'gpg -o  '. $fullpath. ' -d --batch --passphrase "' . $pass . '"  --yes ' . $path;
                 // print "DEBUG - gpg: : " . $command. "\n";
                 exec_cli_no($command);
                 $crypt = true;
                 $origpath = 'tmp/' . $timestamp .  $fileext;
            }
             else {
        	   return response()->json(['message' => 'API: download error, is encrypted, needs pass: ' ], 500);
            }
        }

        // verify if file is image and decompress
		if (preg_match("/\bvvc\b/i", $file)) {
            $fullpath = $fullpathroot . $origpath;
            // decompress image
            $command = 'decompress_image.sh ' . $fullpath . ' ' . $fullpath . $decompressext; 
            // print "DEBUG TODO uncompress command: " . $command. "\n";
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
            return response($content);
            // return response($content)
            //     ->header('Content-Type','image/png')
            //     ->header('Pragma','public')
            //     ->header('Content-Disposition','inline; filename="'. $name)
            //     ->header('Cache-Control','max-age=60, must-revalidate');
        }
        // verify if file is audio and decompress
		elseif (preg_match("/\blpcnet\b/i", $file)) {
            $fullpath = $fullpathroot . $origpath;
            // decompress image
            $command = 'decompress_audio.sh ' . $fullpath . ' ' . $fullpath . $decompressext; 
            // print "DEBUG TODO uncompress command: " . $command. "\n";
            if( exec_cli_no($command)){
                $origpath = $origpath . $decompressext;
                $path = Storage::disk('local')->path($origpath);
            }
            else{
        	   return response()->json(['message' => 'API: download uncompres audio error: ' ], 500);
            }
            // get content of file
            $content = Storage::disk('local')->get($origpath);
            // delete generated file
            Storage::disk('local')->delete($origpath);
            return response($content);
            // return response($content)
            //     ->header('Content-Type','audio/ogg')
            //     ->header('Pragma','public')
            //     ->header('Content-Disposition','inline; filename="'. $name)
            //     ->header('Cache-Control','max-age=60, must-revalidate');
        }
        else {
            $content = Storage::disk('local')->get($origpath);
            return response($content,200);
        }

    }

   /**
     * getImageDownloadHttp
     * parameter: image id
     * TODO crypt
     * @return Json
     */
    public function getImageDownloadHttp( $id)
    {
         $file =  $this:: getImage('uploads/' .$id);
         return response($file)
            ->header('Content-Type','image/png')
            ->header('Pragma','public')
            ->header('Content-Disposition','inline; filename="qrcodeimg.png"')
            ->header('Cache-Control','max-age=60, must-revalidate');
   }

   /**
     * responseRequestSuccess
     * parameter: image id
     * TODO? out of use
     * @return Json
     */
    protected function responseRequestSuccess($ret)
    {
        return response()->json(['status' => 'success', 'data' => $ret], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    /**
     * responseRequestError
     * parameter: image id
     * TODO? out of use
     * @return Json
     */
    protected function responseRequestError($message = 'Bad request', $statusCode = 200)
    {
        return response()->json(['status' => 'error', 'error' => $message], $statusCode)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    /**
     * Clues to lumen
     * parameter: message id
     * TODO remove
     * Storage::disk('s3')->exists('file.jpg')
     * Storage::download('file.jpg');
     * Storage::download('file.jpg', $name, $headers);
     * Storage::disk('local')->missing('file.jpg')
     *
     * @return Void
     */
}