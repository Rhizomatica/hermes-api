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
     * uploadImage
     * parameter: http request
     * @return Json
     */
    public function uploadImage( Request $request)
    {
       /*$request->validate([
            'fileup' => 'required|file|image|size:1024|dimensions:max_width=500,max_height=500',
            'data.name' => 'required|filled|size:100',
        ]);*/

        Storage::disk('local')->put('lastrequest' , $request);

        $filename = $request->file('fileup')->getClientOriginalName();


        //TODO compress and crypt image!!!
         // $command = "compress_image.sh \"" .  $target_file . "\"";
         /*
         if (isset($_POST['encrypt']) && $file_in_place == 1)
         {
             $command = "encrypt.sh \"" . $target_file . "\" \"" . $_POST['password'] . "\"";
             //           echo "encrypt command: " . $command . "<br />";
             ob_start();
             system($command , $return_var);
             $output = ob_get_contents();
             ob_end_clean();
             unlink($target_file);
             $target_file = $target_file . ".gpg";
             //           echo "Criptografia ativada!<br />";
         }
        */


        if($request->hasFile('fileup')){
            $timestamp=time();
            $filename = $request->file('fileup')->getClientOriginalName();

            //$file = $request->file('file')->getClientOriginalName();
            $contents = file_get_contents($request->fileup);
            //Storage::disk('local')->put($request->file('fileup')->getClientOriginalName() , $contents);
            Storage::disk('local')->put('uploads/' . $timestamp , $contents);
            return response()->json( [ 'fileup', $filename, $timestamp], 200);
        }
        else{
            return response()->json( [ 'Hermeserror - not a file?'], 500);
        }
    }

   /**
     * execDecrypt
     * parameter: message id
     * TODO ALL
     * @return Json
     */
     public static function getImage( $id)
    {
         return  Storage::disk('local')->get($id);
    }

    /**
     * getImageUploadHttp
     * parameter: image id
     * TODO crypt
     * @return Json
     */
    public function getImageUploadHttp( $id)
    {
         $file =  $this:: getImage('uploads/' .$id);
         return response($file)
            ->header('Content-Type','image/png')
            ->header('Pragma','public')
            ->header('Content-Disposition','inline; filename="qrcodeimg.png"')
            ->header('Cache-Control','max-age=60, must-revalidate');
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