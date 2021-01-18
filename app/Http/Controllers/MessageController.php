<?php

namespace App\Http\Controllers;

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
}