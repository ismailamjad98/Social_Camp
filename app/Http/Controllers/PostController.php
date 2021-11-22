<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use MongoDB\Client as MongoDB;
use MongoDB\Operation\Aggregate;

class PostController extends Controller
{

    public function create(Request $request)
    {
        $getToken = $request->bearerToken();
        $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
        $userID = $decoded->id;

        $request->validate(
            [
                'title' => 'required',
                'body' => 'required',
                'image' => 'required',
                'status' => 'required',
            ]
        );

        //get token from header and check user id
        $collection = (new MongoDB())->MongoApp->posts;

        $encode = json_encode($userID);
        $decoded = json_decode($encode, true);
        $str_decode = $decoded['$oid'];

        //save a new post in db
        $collection->insertOne([
            'user_id' => $str_decode,
            'title' => $request->title,
            'body' => $request->body,
            'status' => $request->status,
            // Attachments_folder is created in Storage/app/ 
            'image' => $request->file('image')->store('Attachments_Folder')
        ]);

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

        $encode = json_encode($userID);
        $decoded = json_decode($encode, true);
        $str_decode = $decoded['$oid'];

        //DB Collection
        $collection = (new MongoDB())->MongoApp->posts;
        $myposts = $collection->find(['user_id' => $str_decode]);


        // $myposts = Post::all()->where('user_id',  $userID);

        if ($myposts == null) {

            return response([
                'Status' => '200',
                'message' => 'You dont have any Post',
            ], 200);
        } else {
            return response([
                'Status' => '200',
                'Data' => $myposts->toArray(),
            ], 200);
        }
    }

    public function allposts(Request $request)
    {

        // $myposts = Post::all();
        //DB Collection
        $collection = (new MongoDB())->MongoApp->posts;
        $allposts = $collection->find();

        if (is_null($allposts)) {
            return response()->json('Data not found', 404);
        }
        return response($allposts->toArray());
    }

    public function update(Request $request, $id)
    {
        //get token from header and check user id
        $getToken = $request->bearerToken();
        $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
        $userID = $decoded->id;

        $encode = json_encode($userID);
        $decoded = json_decode($encode, true);
        $str_decode = $decoded['$oid'];

        //DB Collection
        $collection = (new MongoDB())->MongoApp->posts;
        //first compare user_id to the db user id 
        $not_exists = $collection->find(['_id' =>  new \MongoDB\BSON\ObjectID($id)]); 

        if ($not_exists->toArray() == null) {
            return response([
                'message' => 'Post Not Exits',
            ]);
        }
        //than find the posts of user_id and get the specific user
        $updatepost = $collection->findOne(['user_id'=> $str_decode]);
        
        if (isset($updatepost)) {
            $updatepost = $collection->updateOne(
                ['_id' => new \MongoDB\BSON\ObjectID($id)],
                ['$set' => [
                    // Attachments_folder is created in Storage/app/ 
                    'image' => $request->file('image')->store('Attachments_Folder'),
                    'title' => $request->title,
                    'body' => $request->body,
                    'status' => $request->status
                ]]
            );
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
    }

    /**
     * Remove the specified record from storage.
     *
     */
    public function destroy(Request $request, $id)
    {
        //get token from header and check user id
        $getToken = $request->bearerToken();
        $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
        $userID = $decoded->id;

        $collection = (new MongoDB())->MongoApp->posts;

        //to change token into string from array
        $encode = json_encode($userID);
        $decoded = json_decode($encode, true);
        $str_decode = $decoded['$oid'];
        //first compare user_id to the db user id 
        $delete_post = $collection->findOne(['user_id'=> $str_decode]);
        //than find the posts of user_id and get the specific user
        $not_exists = $collection->find(['_id' =>  new \MongoDB\BSON\ObjectID($id)]); 
        
        if ($not_exists->toArray() == null) {
            return response([
                'message' => 'Post Not Exits',
            ]);
        }

        if (isset($delete_post)) {
            $collection->deleteOne(['_id' => new \MongoDB\BSON\ObjectID($id)]);

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
    }
}
