<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    //get profile details
    public function profile()
    {
        try{
            $user = Auth::user();
            //make hidden device_token
            $user->makeHidden(['device_token']);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    //update profile details
    public function updateProfile(Request $request)
    {
       try{
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            // 'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
        ],
        [
            'fullname.required' => 'The name field is required',
            'phone.required' => 'The phone field is required',
            'avatar.required' => 'The avatar field is required',
            'avatar.image' => 'The avatar must be an image',
            'avatar.mimes' => 'The avatar must be a file of type: jpeg, png, jpg, gif, svg',
            'avatar.max' => 'The avatar must be less than 2MB',
        ]);

        if ($validator->fails()) {
            Log::error('Error: '.$validator->errors());
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        //if has avatar then update
        $avatar = $request->file('avatar');

        if($avatar){
            //remove old avatar
            $oldAvatarPath = str_replace('/storage/', '', parse_url($user->avatar)['path']);
            if(Storage::disk('public')->exists($oldAvatarPath)){
                Storage::disk('public')->delete($oldAvatarPath);
            }
            $avatarPath = $avatar->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->name = $request->fullname;
        $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
       }catch(\Exception $e){
        return response()->json([
            Log::error('Error: '.$e->getMessage()),
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
       }
    }

    //update password
    public function updatePassword(Request $request)
    {
        try{


            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'old_password' => 'required|string',
                'password' => 'required|string|confirmed|min:8',
            ],
            [
                'old_password.required' => 'The old password field is required',
                'password.required' => 'The password field is required',
                'password.confirmed' => 'The password confirmation does not match',
                'password.min' => 'The password must be at least 8 characters',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 400);
            }

            if (!password_verify($request->old_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Old password is incorrect'
                ], 400);
            }

            $user->password = $request->password;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
