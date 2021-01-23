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

    public function new( Request $request)
    {

       /*$request->validate([
            'fileup' => 'required|file|image|size:1024|dimensions:max_width=500,max_height=500',
            'data.name' => 'required|filled|size:100',
        ]);*/


        Storage::disk('local')->put('lastrequest' , $request);
        //$filename = $request->file('fileup')->getClientOriginalName();

        if($request->hasFile('fileup')){
            $timestamp=time();
            //$file = $request->file('file')->getClientOriginalName();
            $contents = file_get_contents($request->fileup);
            //Storage::disk('local')->put($request->file('fileup')->getClientOriginalName() , $contents);
            Storage::disk('local')->put($timestamp , $contents);
            return response()->json( [ 'fileup', $request->file('fileup')->getClientOriginalName(), $timestamp], 200);
        }
        else{
            return response()->json( [ 'Hermeserror - not a file?'], 500);
        }

        /*
        // get the `UploadedFile` object
            $file = $request->file('file_name');
            $file = $request->file_name;
            // get the original file name
        $filename = $request->file('file_name')->getClientOriginalName();
        $filename = $request->file_name->getClientOriginalName();
        */

        /*
         if ( $request['data'] ) {
            $data = json_decode($request['data']);
            Storage::disk('local')->put('data' , $data);
         }

        $response = null;
        $output = $request->name;
        */


    }

    public function get( $id)
    {
        return Storage::disk('local')->get($id);
    }

    public function uploadImage(Request $request)
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