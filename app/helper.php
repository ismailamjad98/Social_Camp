<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


function DecodeUser($request)
{
    $getToken = $request->bearerToken();
    $key = config('constant.key');
    $decoded = JWT::decode($getToken, new Key($key, "HS256"));
    $userID = $decoded->id;
    return $userID;
}
