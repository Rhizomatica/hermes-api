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
         if( $user = User::firstWhere('email', $id)){
            return response()->json($user, 200);
         }
         else
         {
            return response()->json('Error: cant find user ', 500);
         }
    }

    public function create(Request $request)
    {
        //var_dump($request->all);
        if ($request['password']){
            $request['password'] = hash('sha256', $request['password']);
        }
        //return response()->json($user, 201);
        if($user = User::create($request->all())){
            return response()->json($request, 201); //Created
        }
        else{
            return response()->json('error', 404);
        }

    }

    public function update($id, Request $request)
    {
        if ($request->all()){
            if(  $user = User::firstWhere('email', $id)){
                if ($request['password']){
                    $request['password'] = hash('sha256', $request['password']);
                }

                if (User::where('email', $id)->update($request->all())){
                    return response()->json($id . ' updated', 200);
                }
                else {
                    return response()->json('can\'t delete', 500);
                }
            }
            else {
                return response()->json('can\'t find', 404);
            }
        }
        else {
            return response()->json('Error, does not have request data', 500);
        }
    }

    public function delete($id)
    {
        if( User::firstWhere('email', $id)){
            if (User::where('email', $id)->delete()){
                return response()->json($id . ' deleted', 200);
            }
            else {
                return response()->json('can\'t delete', 500);
             }
         }
         else {
            return response()->json('can\'t find', 404);
         }

    }

    public function login(Request $request)
    {
        $user = new User;
        if ($request->email){
            if ($user = User::firstWhere('email', $request->email)){
                if ($user['password'] == hash('sha256', $request->password)){ //sucessfull login
                    unset($user['password']);
                    unset($user['recoverphrase']);
                    unset($user['recoveranswer']);
                    unset($user['created_at']);
                    unset($user['updated_at']);
                    return response()->json($user, 200);
                }
                else{//fail
                    return response()->json('wrong password', 500);
                }
            }
            else{ //fail
                return response()->json('wrong user', 404);
            }
        }
        else //fail
        {
            return response()->json('lack parameters', 500);
        }
    }
}