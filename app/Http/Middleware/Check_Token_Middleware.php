<?php

namespace App\Http\Middleware;

use App\Models\Token;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class Check_Token_Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $getToken = $request->bearerToken();
        //if token is Empty
        if(empty($getToken)){
            return response([
            "message" => "Token is Empty Please Enter Berear Token!"
            ], 200);
        }
        $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));

        // $userID = $decoded->id;

        // if token is invalid
        $check = Token::where('token' , $getToken)->first();

        if(!isset($check)){
            return response([
            "message" => "Token Doesnot Exists"
            ], 200);
        }
        else{
            return $next($request);
        }
    }
}
