<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $posts = Post::with('user')->get();

        return response()->json([
            'posts' => $posts
        ]);
    }


    /**
     * @param StorePostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePostRequest $request)
    {
        $validation = $request->validated();

        if ($request->file('image')) {
            $image = $request->file('image')->store('public/images');
            $url = url('/') . Storage::url($image);

            $newPost = new Post([
                'header' => $request->header,
                'body' => $request->body,
                'image' => $url,
                'user_id' => $request->user_id,
            ]);
        } else {
            $newPost = new Post([
                'header' => $request->header,
                'body' => $request->body,
                'image' => null,
                'user_id' => $request->user_id,
            ]);
        }

        $newPost->save();

        return response()->json([
            'post' => $newPost
        ], 200);
    }


    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $post = Post::with(['user', 'comments'])->where('id', $id)->get();

        if (!$post) {
            return response()->json([
                'message' => 'Post does not exist'
            ], 400);
        }

        return response()->json([
            'post' => $post
        ], 200);
    }


    /**
     * @param UpdatePostRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePostRequest $request, $id)
    {
        $validation = $request->validated();

        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post does not exist'
            ], 400);
        }

        if ($request->file('image')) {
            $image = $request->file('image')->store('public/images');
            $url = url('/') . Storage::url($image);

            $post->update([
                'header' => $request->header,
                'body' => $request->body,
                'upvotes' => $request->upvotes,
                'image' => $url,
            ]);
        } else {
            $post->update($request->all());
        }

        $post->save();

        return response()->json([
            'post' => $post
        ]);
    }


    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!($post instanceof Post)) {
            return response()->json([
                'message' => 'Post does not exist'
            ], 400);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted'
        ], 200);
    }
}
