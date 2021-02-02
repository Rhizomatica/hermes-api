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

    public function uploadImage( Request $request)
    {
       /*$request->validate([
            'fileup' => 'required|file|image|size:1024|dimensions:max_width=500,max_height=500',
            'data.name' => 'required|filled|size:100',
        ]);*/


        Storage::disk('local')->put('lastrequest' , $request);
        //$filename = $request->file('fileup')->getClientOriginalName();

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

    public static function getImage( $id)
    {
         return  Storage::disk('local')->get($id);
   }

    public function getImageHttp( $id)
    {
         $file =  $this:: getImage('uploads/' .$id);
         return response($file)
            ->header('Content-Type','image/png')
            ->header('Pragma','public')
            ->header('Content-Disposition','inline; filename="qrcodeimg.png"')
            ->header('Cache-Control','max-age=60, must-revalidate');
   }

    public function uploadImage_old(Request $request)
    {
        $response = null;
        $user = (object) ['image' => ""];

        if ($request->hasFile('image')) {
            $original_filename = $request->file('image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $destination_path = './upload/user/';
            $image = 'U-' . time() . '.' . $file_ext;

            if ($request->file('image')->move($destination_path, $image)) {
                $user->image = '/upload/user/' . $image;
                return $this->responseRequestSuccess($user);
            } else {
                return $this->responseRequestError('Cannot upload file');
            }
        } else {
            return $this->responseRequestError('File not found');
        }
    }

    protected function responseRequestSuccess($ret)
    {
        return response()->json(['status' => 'success', 'data' => $ret], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    protected function responseRequestError($message = 'Bad request', $statusCode = 200)
    {
        return response()->json(['status' => 'error', 'error' => $message], $statusCode)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }
}


/*clues

if (Storage::disk('s3')->exists('file.jpg')) {
if (Storage::disk('s3')->missing('file.jpg')) {

return Storage::download('file.jpg');

return Storage::download('file.jpg', $name, $headers);