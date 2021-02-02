<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function showAllUsers()
    {
        return response()->json(User::all());
    }

    public function showOneUser($id)
    {
        return response()->json(User::find($id));
    }

    public function create(Request $request)
    {
        $user = User::create($request->all());

        return response()->json($user, 201);
    }

    public function update($id, Request $request)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        return response()->json($user, 200);
    }

    public function delete($id)
    {
        User::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }


    public function login(Request $request)
    {
        //$data = [$request->login, $request->password];
        $object = (object) [
            'ret' => 'ok',
            'login' => $request->login,
            'admin' => true,
          ];

          //TODO buscar do banco
        if ($request->login == 'admin' ){
           return response()->json($object);
        }
        else{
            return response()->json('$request->login');
        }

    }
}