<?php

namespace App\Http\Controllers;

use App\Models\Send_Friend_Request as ModelsSend_Friend_Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class Send_Friend_Request extends Controller
{
    //
    public function Send_Friend_Request(Request $request){

        $request->validate(
            [
                'reciver_id'=> 'required'
            ]
        );

        //get token from header and check user id
        $getToken = $request->bearerToken();
        $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
        $userID = $decoded->id;
        //check if user is exists in DB
        $users_table = User::where('id','=', $request->reciver_id)->first();

        if ($userID == $request->reciver_id) {
            return response([
                "Message" => "You cannot Send Friend Request to yourself"
            ]);
        }
        
        if(isset($users_table)) {
            $data = new ModelsSend_Friend_Request();
            $data->reciver_id = $request->reciver_id;
            $data->sender_id = $userID;
            $data->save();

            return response([
                "Message" => "You have Successfully Send Friend Request "
            ]);   
        }else {
            return response([
                "Message" => "This User Doesnot Exists in Records"
            ]);
        } 

    }
}
