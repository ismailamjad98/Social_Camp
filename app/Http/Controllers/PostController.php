<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Throwable;

class PostController extends Controller
{

    public function create(PostRequest $request)
    {
        try {
            $request->validated();
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $key = config('constant.key');
            $decoded = JWT::decode($getToken, new Key($key, "HS256"));
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
                'Details' => new PostResource($post)
            ], 200);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function myposts(Request $request)
    {
        try {
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $key = config('constant.key');
            $decoded = JWT::decode($getToken, new Key($key, "HS256"));
            $userID = $decoded->id;

            $myposts = Post::all()->where('user_id',  $userID);

            if (json_decode($myposts) == null) {
                return response([
                    'Status' => '200',
                    'message' => 'You dont have any Post',
                ], 200);
            } else {
                return new PostResource($myposts);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function allposts()
    {
        try {
            $myposts = Post::all();

            if (is_null($myposts)) {
                return response()->json('Data not found', 404);
            }
            return new PostResource($myposts);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    public function update(Request $request, $id)
    {
        try {
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $key = config('constant.key');
            $decoded = JWT::decode($getToken, new Key($key, "HS256"));
            $userID = $decoded->id;

            $post = Post::all()->where('user_id', $userID)->where('id', $id)->first();

            if (Post::where('id', '!=', $id)) {
                return response([
                    'message' => 'Post Not Exits',
                ]);
            }

            if (isset($post)) {
                // Attachments_folder is created in Storage/app/ 
                $post->image = $request->file('image')->store('Attachments_Folder');
                $post->update($request->all());
                //message on Successfully
                return response([
                    'Status' => '200',
                    'message' => 'you have successfully Update Post',
                ], 200);
            } else {
                //message on Unauthorize
                return response([
                    'Status' => '200',
                    'message' => 'you are Authorize to Update other User Posts',
                ], 200);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * Remove the specified record from storage.
     *
     */
    public function destroy(Request $request, $id)
    {
        try {
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $key = config('constant.key');
            $decoded = JWT::decode($getToken, new Key($key, "HS256"));
            $userID = $decoded->id;
            $delete_post = Post::all()->where('user_id', $userID)->where('id', $id)->first();

            if (Post::where('id', '!=', $id)) {
                return response([
                    'message' => 'Post Not Exits',
                ]);
            }

            if (isset($delete_post)) {
                $delete_post->delete($id);
                return response([
                    'Status' => '200',
                    'message' => 'you have successfully Deleted Entry',
                    'Deleted Post ID' => $id
                ], 200);
            } else {
                return response([
                    'Status' => '201',
                    'message' => 'you are not Authorize to delete other User Posts'
                ], 200);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}
