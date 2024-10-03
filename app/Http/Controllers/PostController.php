<?php

namespace App\Http\Controllers;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::all();

        $response = [
            'success' => true,
            'message' => "All posts data",
            'data' => [
                'posts' => $posts
            ]
        ];

        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
        // Validate the post data
        $validatePost = Validator::make(
            $req->all(),[
                'title' => 'required',
                'description' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,gif',
            ]
        );

        // If the validation fails, return error
        if ($validatePost->fails()) {
            $errormessage = $validatePost->errors()->all();
            return $this->sendError('Validation Error', $errormessage, 401);
        }

        // Process the image
        $img = $req->file('image');
        $exten = $img->getClientOriginalExtension();
        $imageName = time() . '.' . $exten;
        $img->move(public_path('uploads'), $imageName);

        // Store the post data
        $post = Post::create([
            'title' => $req->title,
            'description' => $req->description,
            'image' => $imageName,
        ]);

        // If the post data is inserted successfully
        return $this->sendResponse($post, 'Post Created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Post::select('id', 'title', 'description', 'image')
                    ->where('id', $id)
                    ->first();

        if (!$data) {
            return $this->sendError('Post not found', [], 404);
        }

        return $this->sendResponse($data, 'Your Post is here');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $req, string $id)
    {
        $validatePost = Validator::make($req->all(), [
            'title' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,gif',
        ]);

        if ($validatePost->fails()) {
            return $this->sendError('Validation Error', $validatePost->errors()->all(), 401);
        }

        // Get the post data
        $postData = Post::select('id', 'image')->where('id', $id)->first();

        // If an image is provided, process it
        if ($req->hasFile('image')) {
            $path = public_path('uploads');
            if ($postData->image && file_exists($path . '/' . $postData->image)) {
                unlink($path . '/' . $postData->image);
            }

            $img = $req->file('image');
            $exten = $img->getClientOriginalExtension();
            $imageName = time() . '.' . $exten;
            $img->move(public_path('uploads'), $imageName);
        } else {
            $imageName = $postData->image;
        }

        // Update post data
        Post::where('id', $id)->update([
            'title' => $req->title,
            'description' => $req->description,
            'image' => $imageName,
        ]);

        return $this->sendResponse($id, 'Post Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::select('image')->where('id', $id)->first();

        if (!$post) {
            return $this->sendError('Post not found', [], 404);
        }

        // Delete the image if exists
        if ($post->image) {
            $filePath = public_path('uploads/' . $post->image);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete the post
        Post::where('id', $id)->delete();

        return $this->sendResponse($id, 'Post Deleted successfully');
    }

    // Send success response
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $result,
        ];

        return response()->json($response, 200);
    }

    // Send error response
    public function sendError($error, $message = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($message)) {
            $response['data'] = $message;
        }
        return response()->json($response, $code);
    }
}
