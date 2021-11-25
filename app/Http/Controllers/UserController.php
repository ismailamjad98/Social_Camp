<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Jobs\New_User_Register;
use App\Mail\Sendmail;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Token;
use App\Models\User;
use App\Services\Decode_User_Service;
use Illuminate\Support\Facades\Auth;
use Throwable;

class UserController extends Controller
{
    /**
     * This Function is creating a Token for the authenticated user.
     *
     */


    function createToken($data)
    {
        $key = config('constant.key');
        $payload = array(
            "iss" => "http://127.0.0.1:8000",
            "aud" => "http://127.0.0.1:8000/api",
            "iat" => time(),
            "nbf" => 1357000000,
            "id" => $data,
            'token_type' => 'bearer',
        );

        $token = JWT::encode($payload, $key, 'HS256');
        return $token;
    }

    /**
     * Registering a new user.
     */

    public function register(RegisterUserRequest $request)
    {
        try {
            // Validate the user inputs
            $request->validated();

            //create a link to varify email.        
            $verification_token = $this->createToken($request->email);
            $url = "http://localhost:8000/api/emailVerify/" . $verification_token . '/' . $request->email;

            //create new User in DB
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'verification_token' => $verification_token,
            ]);

            //send mail
            // Mail::to($request->email)->send(new Sendmail($url, 'bevegak100@d3ff.com'));
            
            //php artisan queue:work to make your emails in a queue 
            New_User_Register::dispatch($request->email, $url);

            //message on Register
            return new UserResource($request);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    //create function to verify the email
    function EmailVerify($token, $email)
    {
        try {
            $emailVerify = User::where('email', $email)->first();

            if ($emailVerify->email_verified_at != null) {
                return response([
                    'message' => 'Already Varified'
                ]);
            } elseif ($emailVerify) {
                $emailVerify->email_verified_at = date('Y-m-d h:i:s');
                $emailVerify->save();
                return response([
                    'message' => 'Thankyou Your Eamil Verified NOW !!!'
                ]);
            } else {
                return response([
                    'message' => 'Something Went Wrong'
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    // Login Method
    public function login(LoginUserRequest $request)
    {
        try {
            $request->validated();

            $user = User::where('email', $request->email)->first();
            //get data from user
            $data = [
                'email' => $request->email,
                'password' => $request->password,
            ];

            //give token after login and assign user id to token
            $token = $this->createToken($user->id);

            if ($user['email_verified_at'] == null) {
                return response([
                    'Status' => '400',
                    'message' => 'Bad Request',
                    'Error' => 'Please Verify your Email before login'
                ], 400);
            }
            //check the user in DB and varify if it is authenticated or not
            else {
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
                } else {
                    return response([
                        'Status' => '400',
                        'message' => 'Bad Request',
                        'Error' => 'Email or Password doesnot match'
                    ], 400);
                }
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function Logout(Request $request)
    {
        try {
            $getToken = $request->bearerToken();
            $key = config('constant.key');
            $decoded = JWT::decode($getToken, new Key($key, "HS256"));

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
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    public function profile(Request $request)
    {
        try {
            //get token from header
            $getToken = $request->bearerToken();

            // if token is invalid
            $check = Token::where('token', $getToken)->first();
            if (!isset($check)) {
                return response([
                    "message" => "Invalid Token"
                ], 200);
            }
            // $key = config('constant.key');
            // $decoded = JWT::decode($getToken, new Key($key, "HS256"));
            // $userID = $decoded->id;

            $userID =(new Decode_User_Service)->DecodeUser($getToken);

            if ($userID) {

                $profile = User::find($userID);
                return response([
                    "Details" => $profile
                ], 200);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // Update user profile
    public function update(Request $request, $id)
    {
        try {
            
            $userupdate = User::all()->where('id', $id)->first();

            //message on Successfully
            if (isset($userupdate)) {
                $userupdate->update($request->all());
                return response([
                    'Status' => '200',
                    'message' => 'you have successfully Update User Profile',
                ], 200);
            }
            if ($userupdate == null) {
                return response([
                    'Status' => '200',
                    'message' => 'User not found',
                ], 404);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
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
