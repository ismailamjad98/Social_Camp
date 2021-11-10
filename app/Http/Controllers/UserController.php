<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;
use App\Models\Token;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Input\Input;

class UserController extends Controller
{
    //
    function createToken($data)
    {
        $key = "SocialCamp";
        $payload = array(
            "iss" => "http://127.0.0.1:8000",
            "aud" => "http://127.0.0.1:8000/api",
            "iat" => time(),
            "nbf" => 1357000000,
            "id" => $data,
            'token_type' => 'bearer',
            // 'expires_in' => auth()->factory()->getTTL() * 60,
        );

        $token = JWT::encode($payload, $key, 'HS256');

        return $token;
    }

    public function register(Request $request)
    {
        // Validate the user inputs
        $request->validate(
            [
                'name' => 'required|string|min:3',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|string|min:5'
            ]
        );

        //create new User in DB
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        // email data
        $Email = array(
            'name' => $request->name,
            'email' => $request->email,
        );

        // send email with the template
        Mail::send('welcome_email', $Email, function ($message) use ($Email) {
            $message->to($Email['email'], $Email['name'])
                ->subject('Welcome to SocialCamp')
                ->from('Ismailamjad98@gmail.com', 'SocialCamp');
        });


        //message on Register
        return response([
            'Status' => '200',
            'message' => 'Thanks, you have successfully signup',
            "Mail" => "Email Sended Successfully",
            'user' => $user
        ], 200);
    }

    // Login Method
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();
        $data = [
            'email' => $request->email,
            'password' => $request->password,
        ];


        //check the user in DB and varify if it is authenticated or not
        if (Auth::attempt($data)) {
            //give token after login and assign user id to token
            $token = $this->createToken($user->id);

            // check if user is already loggedin and assigned token 
            if (Token::where('userID', '=', $user->id)->first()) {
                return response([
                    'Message' => "Already Login!",
                    "Token" => $token
                ]);
            } else {
                // save token in db to user 
                $token_save = Token::create([
                    'userID' => $user->id,
                    'token' => $token
                ]);
            }

            return response([
                'Status' => '200',
                'Message' => 'Successfully Login',
                'Email' => $request->email,
                'token' => $token
            ], 200);
        }else {
            return response([
                'Status' => '400',
                'message' => 'Bad Request',
                'Error' => 'Email or Password doesnot match'
            ], 400);
        }
    }

    public function Logout(Request $request)
    {

        $getToken = $request->bearerToken();

        $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));

        $userID = $decoded->id;

        $userExist = Token::where("userID", $userID)->first();

        if ($userExist) {
            $userExist->delete();
        } else {
            return response([
                "message" => "This user is already logged out"
            ], 404);
        }
        return response([
            "message" => "logout successfully"
        ], 200);
    }


    public function profile(Request $request)
    {
        //get token from header
        $getToken = $request->bearerToken();
        
        // if token is invalid
        $check = Token::where('token' , $getToken)->first();
        if(!isset($check)){
            return response([
            "message" => "Invalid Token"
            ], 200);
        }

        $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));

        $userID = $decoded->id;

        if($userID) {

            $profile = User::find($userID);
            return response([
                "Details" => $profile
            ], 200);
        }
    }
}
