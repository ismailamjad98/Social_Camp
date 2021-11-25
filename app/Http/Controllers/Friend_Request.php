<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptFriendRequest;
use App\Http\Requests\SendFriendRequest;
use App\Models\Friend_Request as ModelsFriend_Request;
use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class Friend_Request extends Controller
{
    //
    public function Send_Friend_Request(SendFriendRequest $request)
    {
        try {
            $request->validated();

            //get token from header and check user id
            $getToken = $request->bearerToken();
            $key = config('constant.key');
            $decoded = JWT::decode($getToken, new Key($key, "HS256"));
            $userID = $decoded->id;

            if ($userID == $request->reciver_id) {
                return response([
                    "Message" => "You cannot Send Friend Request to yourself"
                ]);
            }
            //check if recever_user is exists in Users_table DB
            $users_table = User::where('id', '=', $request->reciver_id)->first();
            $data = new ModelsFriend_Request();

            //chcek if user is already sended request or not
            $check_alreadySent = ModelsFriend_Request::where('sender_id', $userID)->where('reciver_id', $request->reciver_id)->first();

            if (isset($check_alreadySent)) {
                return response([
                    "Message" => "You have already Sent the Friend Request to this User"
                ]);
            }

            if (isset($users_table)) {
                $data->reciver_id = $request->reciver_id;
                $data->sender_id = $userID;
                $data->save();

                return response([
                    "Message" => "You have Successfully Send Friend Request "
                ]);
            } else {
                return response([
                    "Message" => "This User Doesnot Exists in Records"
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function My_Requests(Request $request)
    {
        try {
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $key = config('constant.key');
            $decoded = JWT::decode($getToken, new Key($key, "HS256"));
            $userID = $decoded->id;

            $req = ModelsFriend_Request::all()->where('reciver_id',  $userID)->where('status', '0');

            if (json_decode($req) != null) {

                return $req;
            } else {
                return response([
                    'message' => 'You Dont have any Friend Request'
                ], 404);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function Receive_Request(AcceptFriendRequest $request)
    {
        try {
            $request->validated();

            //get token from header and check user id
            $getToken = $request->bearerToken();
            $key = config('constant.key');
            $decoded = JWT::decode($getToken, new Key($key, "HS256"));
            $userID = $decoded->id;

            if ($userID == $request->sender_id) {
                return response([
                    "Message" => "You cannot Receive Friend Request of yourself"
                ]);
            }
            //check if recever_user is exists in Request Table DB
            $recive_req = ModelsFriend_Request::where('sender_id', $request->sender_id)->where('reciver_id', $userID)->first();

            if (!$recive_req) {
                return response([
                    "Message" => "You do not have any friend request from this user"
                ]);
            }

            if ($recive_req->status == '1') {
                return response([
                    "Message" => "You are already Friend of this User"
                ]);
            }

            if (isset($recive_req)) {
                $recive_req->status = '1';
                $recive_req->save();
                return response([
                    "Message" => "Congratulations! You are Friends Now"
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function Delete_Request(Request $request, $id)
    {
        try {
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $key = config('constant.key');
            $decoded = JWT::decode($getToken, new Key($key, "HS256"));
            $userID = $decoded->id;

            $delete_request = ModelsFriend_Request::all()->where('reciver_id', $userID)->where('sender_id', $id)->first();

            if (isset($delete_request)) {
                $delete_request->delete($id);
                return response([
                    'Status' => '200',
                    'message' => 'you have successfully Delete Friend Request',
                    'Deleted Post ID' => $id
                ], 200);
            } else {
                return response([
                    'Status' => '201',
                    'message' => 'This User Not send Request'
                ], 200);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}
