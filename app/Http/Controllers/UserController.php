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
        //var_dump($request->all);
        if ($request['password']){
            $request['password'] = hash('sha256', $request['password']);
        }
        //return response()->json($user, 201);
        return response()->json($request, 201);
    }

    public function update($id, Request $request)
    {
        if ($request['password']){
            $request['password'] = hash('sha256', $request['password']);
        }
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
        $user = new User;
        if ($request->email){
            $user = User::firstWhere('email', $request->email);
            if ($user['password'] == hash('sha256', $request->password)){ //sucessfull login
                unset($user['password']);
                unset($user['recoverphrase']);
                unset($user['recoveranswer']);
                return response()->json($user, 200);
            }
        }
        else //fail
        {
            return response()->json('error', 404);
        }
    }
}