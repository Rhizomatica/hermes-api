<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\FileUploader;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;

use League\Flysystem\Adapter\Local;

class FileController extends Controller
{

    public function new( Request $request)
    {


         if ( $request['data'] ) {
            $data = json_decode($request['data']);
            Storage::disk('local')->put('data' , $data);
         }

        /*$request->validate([
            'file' => 'required|file|image|size:1024|dimensions:max_width=500,max_height=500',
            'data.name' => 'required|filled|size:100',
        ]);*/
        if ($_FILES){
            return response()->json( [ $_FILES], 200);
            Storage::disk('local')->put('files' , $contents);
        }


        //$response = null;
        //$timestamp=time();
        //$output = $request->name;

        //$output= file_get_contents($request->file->path);
        //$output = file_get_contents($request->file);
        //Storage::disk('local')->put('output' , $output);
        //debug guarda request completo

        //$path = $request->photo->storeAs('images', 'filename.jpg', 's3');



        if($request->hasFile('file')){
            //$file = $request->file('file')->getClientOriginalName();
            $contents = file_get_contents($request->file('file'));
            Storage::disk('local')->put('contents' , $contents);
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }
        else{
            Storage::disk('local')->put('request' , $request);
            return response()->json( [ $request], 200);
        }

        //return $request->file('file');


/*
        // get the `UploadedFile` object
            $file = $request->file('file_name');
            $file = $request->file_name;
            // get the original file name
        $filename = $request->file('file_name')->getClientOriginalName();
        $filename = $request->file_name->getClientOriginalName();
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