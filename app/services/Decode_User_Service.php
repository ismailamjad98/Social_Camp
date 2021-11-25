<?php

namespace App\Services;

use Illuminate\Http\Request;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Decode_User_Service
{

    protected $key;
    protected $payload;

    public function DecodeUser($getToken)
    {
        $key = config('constant.key');
        $decoded = JWT::decode($getToken, new Key($key, "HS256"));
        $userID = $decoded->id;
        return $userID;
    }
}
