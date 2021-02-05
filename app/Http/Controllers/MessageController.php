<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use App\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function showAllMessages()
    {
        return response()->json(Message::all());
    }

    public function showOneMessage($id)
    {
        return response()->json(Message::find($id));
    }

    public function create(Request $request)
    {
        $message = Message::create($request->all());
        return response()->json($message, 201);
    }

    public function update($id, Request $request)
    {
        $message = Message::findOrFail($id);
        $message->update($request->all());

        return response()->json($user, 200);
    }

    public function delete($id)
    {
        Message::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }

    public function oldRenderMessage($id)
    {
        $message = Message::find($id);
        $message_image =  FileController::getImage('uploads/' . $id);
        $message_concat = $message . $message_image;
        \Storage::disk('local')->put('output/' . $id  , $message_concat);
        return response($message);
    }

    //Render output message with folders and tar
    public function packMessage($id)
    {
        //find the message in database
        if($message = Message::find($id)){

            // Assures to delete the working path
            Storage::deleteDirectory('tmp/'.$id);

            // Writes message file
            if (! Storage::disk('local')->put('tmp/' . $id . '/hmp.json'  , $message)){
                return response('Hermes pack message Error: can\'t write message file',500);
            }
            // test for image  - TODO change file for image in DB
            if ($message['file']){
                // test for image file
                if (Storage::disk('local')->exists('uploads/'.$id)) {
                    // TODO testing purposes -  change for move
                    // if (!$image = Storage::disk('local')->move('uploads/' . $id , 'tmp/'. $id . '/image' )){
                    if (! Storage::disk('local')->copy('uploads/' . $id , 'tmp/' . $id . '/image' )){
                        return response('Hermes pack message Error:  can\'t move image file',500);
                    }
                }
                else{
                    return response('Hermes pack message Error: theres a file path but can\'t find the image file');
                }
            }

            $path = Storage::disk('local')->path('tmp');
            $command  = 'tar cfz ' . $path . '/' . $id . '.hmp -C '.  $path . ' ' . $id  ;
            if ($output = exec_cli($command) ){
                return response('Hermes pack message Error: can\'t package the files: ' . $output . $command);
            }

            // Clean outbox destination and move the package
            Storage::disk('local')->delete('outbox/'.$id.'.hmp');
            if (! Storage::disk('local')->move('tmp/'.$id.'.hmp', 'outbox/'.$id.'.hmp')){
                return response('Hermes pack message Error: can\'t package the files: ' . $output . $command);
            }
            //$message = @json_decode(json_encode($messagefile), true);
            Storage::disk('local')->deleteDirectory('tmp/'.$id);
        }
        else{
            return response('Hermes pack message Error: can\'t find message');
        }
        return response(['Hermes pack DONE', $message],200);
    }

    public function sendMessage($id)
    {
        if($message = Message::find($id)){
            //TODO remove mock
            //$remote = $message->dest . $remotePath;
            $remote = "trambolho";
            //work path
            $path = Storage::disk('local')->path('outbox/'.$id.'.hmp');
            $source =  env('HERMES_NAME');
            $remotePath = env('HERMES_PATH') . env('HERMES_INBOX');
            /* // TODO test for draft
            if($message['draft']){
            }*/

            // UUCP -C Copy  (default) / -d create dirs
            if (Storage::disk('local')->exists('outbox/'.$id.'.hmp')) {
                $command = 'uucp -C -d \'' .  $path . '\' ' . $remote . '!~/' . $source . '/' . $id . '.hmp' ;
                if ($output = exec_cli($command) ){
                    return response('Hermes sendMessage: Error on uucp : ' . $output . $command);
                }
                $message['draft']=false;
                //$message->update($message);

                Storage::prepend('send.log', 'sent . '. $id  . ' ' .  time() . ' ' );
                //$output = exec_cli($command);
                //TODO test output for error
            }
            else{
                return response('Hermes sendMessage Error: can\'t find message package in outbox: ' . $output . $command);
            }
        }
        else{
            return response('Hermes sendMessage Error: can\'t find message: ' . $output . $command);
        }
        return response(['Hermes sendMessage: DONE', $command,'output cli: '. $output,$message],200);
    }

    //Process Inbox Message HMP - Hermes Message Pack
    public function unpackInboxMessage($id){

        // Test for tmp dir, if doesnt exist, creates it
        if (! Storage::disk('local')->exists('inbox/tmp')){
            if(!Storage::disk('local')->makeDirectory('tmp')){
                return response('Hermes unpack inbox message Error: can\'t find or create tmp dir');
            }
        }

        // Test for HMP file and unpack it
        if (Storage::disk('local')->exists('inbox/' . $id . '.hmp')){
            $messagePack = Storage::disk('local')->get('inbox/'. $id . '.hmp');
            $message='';

            // Get path, unpack into tmp and read message data
            $path = Storage::disk('local')->path('');
            $command  = 'tar xvfz ' .  $path . 'inbox/' . $id . '.hmp ' .  '-C ' . $path . 'tmp/'  ;
            $output = exec_cli($command);
            $files[] = explode(' ', $output);

            // Test for HMP: hermes message package, create record on messages database
            if (Storage::disk('local')->exists('tmp/'.$id.'/hmp.json')){
                $messagefile = json_decode(Storage::disk('local')->get('tmp/'. $id . '/hmp.json'));
                $message = @json_decode(json_encode($messagefile), true);
                $message['id'] = null;
                $message['inbox'] = true;
            }
            else {
                return response('Hermes unpack inbox message Error: can\'t file data from unpacked message');
            }
            // Move attached files
            if (Storage::disk('local')->exists('tmp/'.$id.'/image')){
                // Test and create download folder if it doesn't exists
                if (! Storage::disk('local')->exists('downloads')){
                    if(!Storage::disk('local')->makeDirectory('downloads')){
                        return response('Hermes unpack inbox message Error: can\'t find or create downloads dir');
                    }
                }
                // TODO can be removed, just  for debugging and test
                Storage::disk('local')->delete('downloads/' . $id . '.image');

                // move image
                if (!Storage::disk('local')->copy('tmp/' . $id . '/image', 'downloads/' .$id. '.image' )){
                    return response('Hermes unpack inbox message Error: can\'t copy file image from unpacked message');
                }
                // TODO move audio and other files
            }
            else {
                return response('Hermes unpack inbox message Error: can\'t file image from unpacked message');
            }

            //create message on database, delete tar and hmp
            if($message = Message::create($message)){
                if (Storage::disk('local')->exists('tmp/'.$id)){
                    if (!Storage::disk('local')->deleteDirectory('tmp/' . $id)){
                        return response('Hermes unpack inbox message Error: can\'t delete dir');
                    }
                    if (!Storage::disk('local')->delete('inbox/' . $id . '.hmp')){
                        return response('Hermes unpack inbox message Error: can\'t delete dir');
                    }
                }
                else{
                    return response('Hermes unpack inbox message Error: can\'t create message on database', 500);
                }
            } else {
                return response('Hermes unpack inbox message Error: can\'t delete dir', 500);
            }
        }
        else {
            return response('Hermes unpack inbox message Error: can\'t find HMP', 500);
        }
        return response( $message,200);
    }

    public function showAllInboxMessages()
    {
        $files = \Storage::Files('inbox');
        $file = [];

        /*$filtered_files = array_filter($files, function($str){
            return strpos($str, 'hmp') === 0;
        });*/

        $files_out = [];
        for ($i = '0' ; $i < count($files); $i++) {
            $file = explode('inbox/', $files[$i]);

            if(!empty($files[$i])) {
                $files_out[] = $file[1];
            }

        }
        //var_dump($files_out);
        return response()->json($files_out);
    }

    public function showOneInboxMessage($id)
    {
        $file = \Storage::get('inbox/' . $id);
        $output = explode('}', $file)[0];
        $output = $output . '}';
        $output = json_decode($output);
        return response()->json($output);
    }

    public function showOneInboxMessageImage($id)
    {
        $file = \Storage::get('inbox/' . $id);
        $output = explode('}', $file);
        return response($output[1],200);
    }

    public function hideInboxMessage($id)
    {
        \Storage::move('inbox/' . $id, 'inbox/.' . $id);
        return response('hide ' . $id . ' Successfully', 200);
    }

    public function unhideInboxMessage($id)
    {
        \Storage::move('inbox/.' . $id, 'inbox/' . $id);
        return response('unhide ' . $id . ' Successfully', 200);
    }

    public function deleteInboxMessage($id)
    {
        $file = \Storage::get('inbox/' . $id);
        return response('Deleted Successfully', 200);
    }
}