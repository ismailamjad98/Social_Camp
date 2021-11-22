<?php

namespace App\Http\Controllers;

use App\Mail\Sendmail;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Token;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * This Function is creating a Token for the authenticated user.
     *
     */

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


    /**
     * Registering a new user.
     */

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

        //create a link to varify email.
        $verification_token = $this->createToken($request->email);
        $url = "http://localhost:8000/api/emailVerify/" . $verification_token.'/'. $request->email;

        //create new User in DB
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'url' => $url
        ]);
        
        //send Email by using php artisan make:mail
        Mail::to($request->email)->send(new Sendmail($user->url, $user->id));


        /**
         * send email by just using Send function.
         * // email data
         * $Email = array(
         *  'name' => $request->name,
         * 'email' => $request->email,
         * );
         * 
         * send email with the template
         * Mail::send('welcome_email', $Email, function ($message) use ($Email) {
         * $message->to($Email['email'], $Email['name'])
         *          ->subject('Welcome to SocialCamp')
         *          ->from('Ismailamjad98@yahoo.com', 'SocialCamp');
         * });
         *
         */

        //message on Register
        return response([
            'Status' => '200',
            'message' => 'Thanks, you have successfully signup',
            "Mail" => "Email Sended Successfully",
            'user' => $user
        ], 200);
    }

    //create function to verify the email
    function EmailVerify($token , $email){

        $emailVerify = User::where('email',$email)->first();

        if($emailVerify->email_verified_at != null){

            return response([
                'message'=> 'Already Varified'
            ]);

        }elseif ($emailVerify) {

            $emailVerify->email_verified_at = date('Y-m-d h:i:s');
            $emailVerify->save();

            return response([
                'message' => 'Thankyou Your Eamil Verified NOW !!!'
            ]);
        }else{

            return response([
                'message'=>'Something Went Wrong'
            ]);
        }
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

         //give token after login and assign user id to token
         $token = $this->createToken($user->id);

        //check the user in DB and varify if it is authenticated or not
        if (Auth::attempt($data)) {
            
            // check if user is already loggedin and assigned token 
            if (Token::where('user_id', '=', $user->id)->first()) {
                $token = Token::where('user_id', '=', $user->id)->first()->delete();
                $new_token = $this->createToken($user->id);
                // save token in db to user 
                $token_save = Token::create([
                    'user_id' => $user->id,
                    'token' => $new_token
                ]);

                return response([
                    'Message' => "Already Login!",
                    "Token" => $new_token
                ]);
            } else {
                // save token in db to user 
                $token_save = Token::create([
                    'user_id' => $user->id,
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

        $userExist = Token::where("user_id", $userID)->first();

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

    // Update user profile
    public function update(Request $request, $id)
    {
        $userupdate = User::where('id', $id)->first();
        //message on Successfully
        if(isset($userupdate)){
            return response([
                'Status' => '200',
                'message' => 'you have successfully Update User Profile',
            ], 200);
    
        }
        if($userupdate == null){
            return response([
                'Status' => '200',
                'message' => 'User not found',
            ], 404);
        }
    }

    // //delete User Function if You want to delete the Registered User
     /**
         * public function destroy_User($id)
         * {
            * if (User::where('id', '=', $id)->delete($id)) {
                * return response([
                * 'Status' => '200',
                * 'message' => 'you have successfully Deleted Entry',
                *  'Deleted User ID' => $id
                * ], 200);
            * } else {
                * return response([
                *  'Status' => '201',
                * 'message' => 'This User Does not Exits'
                * ], 200);
            * }
         * }
         *
         */
}
