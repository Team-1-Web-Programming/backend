<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    // Get all blogs with pagination
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $blogs = Blog::with('user')->orderBy('date', 'desc')->paginate($perPage);

        return response()->json($blogs);
    }

    // Show a specific blog
    public function show($id)
    {
        $blog = Blog::with('user')->find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        return response()->json($blog);
    }

    // Store a new blog
    public function store(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'cover_image' => 'nullable|url',
            'date' => 'required|date_format:Y-m-d H:i:s',
        ]);

        // Create the new blog post with the authenticated user's ID
        $blog = Auth::user()->blogs()->create($validated);

        return response()->json($blog, 201);
    }

    // Update an existing blog
    public function update(Request $request, $id)
    {
        // Find the blog to update
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        // Validate updated data
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required',
            'cover_image' => 'nullable|url',
        ]);

        // Ensure only the author can update their own blog
        if ($blog->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result = $blog->update($validated);
        if ($result) {
            return response()->json($blog, 200);
        } else {
            return response()->json(['message' => 'No changes detected'], 200);
        }
    }

    // Delete a blog
    public function destroy($id)
    {
        // Find the blog to delete
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        // Ensure only the author can delete their own blog
        if ($blog->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete the blog
        $blog->delete();
        return response()->json(['message' => 'Blog deleted successfully']);
    }
}
