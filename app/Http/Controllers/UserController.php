<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    private $photo_profile_path;

    public function __construct(Request $request)
    {
        $this->photo_profile_path = 'uploads/'.$request->user()->id.'/profile';
    }

    public function index(Request $request)
    {
        $data = $request->user();
        if ($data->photo_profile) {
            $data['photo_profile'] = $this->getFileUploadedResize(public_path($this->photo_profile_path) . '/' . $data['photo_profile'], 50);
        }
        return $data;
    }

    public function update(UserUpdateRequest $request)
    {
        if ($request->hasFile('photo_profile')) {
            if ($request->user()->photo_profile) {
                $old_file = public_path($this->photo_profile_path) . '/' . $request->user()->photo_profile;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            $file = $request->file('photo_profile');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move($this->photo_profile_path, $filename);

            $request->user()->photo_profile = $filename;
        }
        else if (Arr::has($request->all(), 'photo_profile')) {
            $request->user()->photo_profile = null;
        }

        if ($request->has('name')) {
            $request->user()->name = $request->name;
        }

        if ($request->has('password')) {
            $request->user()->password = bcrypt($request->password);
        }

        $request->user()->save();

        return response()->json([
            'message' => 'User updated successfully',
        ]);
    }
}
