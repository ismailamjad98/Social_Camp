<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{

    public function create(Request $request)
    {
        $request->validate(
            [
                'title' => 'required',
                'body' => 'required',
                'image' => 'required',
                'status' => 'required',
            ]
        );
        //get token from header and check user id
        $getToken = $request->bearerToken();
        $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
        $userID = $decoded->id;
        //save a new post in db
        $post = new Post;
        $post->title = $request->title;
        $post->body = $request->body;
        $post->user_id = $userID;
        $post->status = $request->status;
        // Attachments_folder is created in Storage/app/ 
        $post->image = $request->file('image')->store('Attachments_Folder');
        $post->save();
        //message on Successfully
        return response([
            'Status' => '200',
            'message' => 'successfully Posted',
        ], 200);
    }

    public function myposts(Request $request)
    {
        //get token from header and check user id
        $getToken = $request->bearerToken();
        $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
        $userID = $decoded->id;

        $myposts = Post::all()->where('user_id' ,  $userID);
        
        if (is_null($myposts)) {
            return response()->json('Data not found', 404); 
        }
        return $myposts;
    }

    public function allposts(Request $request)
    {

        $myposts = Post::all();
        
        if (is_null($myposts)) {
            return response()->json('Data not found', 404); 
        }
        return $myposts;
    }

    public function update(Request $request, $id)
    {
        //get token from header and check user id
        $getToken = $request->bearerToken();
        $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
        $userID = $decoded->id;

        $request->validate(
            [
                'title' => 'required',
                'body' => 'required',
                'image' => 'required',
                'status' => 'required'
            ]
        );
        $post = Post::find($id);
        $post->title = $request->title;
        $post->body = $request->body;
        // Attachments_folder is created in Storage/app/ 
        $post->image = $request->file('image')->store('Attachments_Folder');
        $post->user_id = $userID;
        $post->status = $request->status;
        $post->update();

        //message on Successfully
        return response([
            'Status' => '200',
            'message' => 'you have successfully Update Post',
        ], 200);
    }

    /**
     * Remove the specified record from storage.
     *
     */
    public function destroy($id)
    {

        if (Post::where('id', '=', $id)->delete($id)) {
            return response([
                'Status' => '200',
                'message' => 'you have successfully Deleted Entry',
                'Deleted Post ID' => $id
            ], 200);
        } else {
            // save token in db to user 
            return response([
                'Status' => '201',
                'message' => 'This Post Does not Exits'
            ], 200);
        }
    }
}
