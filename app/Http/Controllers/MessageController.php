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

    public function renderMessage($id)
    {
        $message = Message::find($id);
        $message_image =  FileController::getImage('uploads/' . $id);
        $message_concat = $message . $message_image;
        \Storage::disk('local')->put('output/' . $id  , $message_concat);
        return response($message);

    }

    public function showAllInboxMessages()
    {
        $files = \Storage::allFiles('inbox');
        return response()->json($files);
    }

    public function showOneInboxMessage($id)
    {
        $file = \Storage::get('inbox/' . $id);
        $output = explode('}', $file)[0];
        $output = $output . "}";
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