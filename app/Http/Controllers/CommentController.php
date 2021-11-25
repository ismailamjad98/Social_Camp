<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Throwable;

class CommentController extends Controller
{
    public function friend_posts($id)
    {
        try {
            $f_posts = Post::all()->where('user_id', '==', $id)->where('status', 'public');

            if ($f_posts->toArray() == null) {
                return "This User Have No Post";
            }

            if (isset($f_posts)) {
                return response([
                    $f_posts
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    public function create(CommentRequest $request, $id)
    {
        try {
            //call a helper function to decode user id
            $userID = DecodeUser($request);

            $request->validated();

            $comment = Post::where('id', '=', $id)->where('status', 'public')->first();

            $private = Post::where('id', '=', $id)->where('status', 'private')->first();

            if (isset($comment)) {

                $comment_store = Comment::create([
                    'user_id' => $userID,
                    'post_id' => $id,
                    'comment' => $request->comment,
                    'attachment' => $request->attachment
                ]);

                if (isset($comment_store)) {
                    return response([
                        'message' => 'Comment Created Succesfully',
                        'Comment' => $comment_store,
                    ]);
                } else {
                    return response([
                        'message' => 'Something Went Wrong While added Comment',
                    ]);
                }
            } elseif (isset($private)) {
                return response([
                    'message' => 'This Post is Private',
                ]);
            } else {
                return response([
                    'message' => 'No Post Found',
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    public function update(Request $request, $id)
    {
        try {
            //call a helper function to decode user id
            $userID = DecodeUser($request);

            $update_comment = Comment::all()->where('user_id', $userID)->where('id', $id)->first();

            if (Comment::where('id', '!=', $id)) {
                return response([
                    'message' => 'Comment Not Exits',
                ]);
            }

            if (isset($update_comment)) {
                $update_comment->update($request->all());
                //message on Successfully
                return response([
                    'Status' => '200',
                    'message' => 'you have successfully Update Comment',
                ], 200);
            } else {
                //message on Unauthorize
                return response([
                    'Status' => '200',
                    'message' => 'you are not Authorize to Update this Comment',
                ], 200);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            //call a helper function to decode user id
            $userID = DecodeUser($request);

            $comment = Comment::where('id', $id)->where('user_id', $userID)->first();

            if (Comment::where('id', '!=', $id)) {
                return response([
                    'message' => 'Comment Not Exits',
                ]);
            }

            if (isset($comment)) {
                $comment->delete();
                return response([
                    'message' => 'Comment has been Deleted',
                ]);
            } else {
                return response([
                    'message' => 'You Unauthorize to Delete Comment',
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}
