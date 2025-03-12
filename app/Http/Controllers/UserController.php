<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //update profile details
    public function updateProfile(Request $request)
    {
       try{
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
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
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
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
