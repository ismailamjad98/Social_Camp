<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //message on Sucessfully Register
        $user = $request->all();
        return [
            'Status' => '201',
                'message' => 'Thanks, you have successfully signup',
                "Mail" => "Email Sended Successfully",
                'user' => $user
        ];
    }
}
