<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ManageUser extends Controller
{
    //get all users
    public function getAllUsers(Request $request)
    {
        $searchKey = $request->search_key;
        $users = User::where('name', 'like', '%' . $searchKey . '%')
            ->orWhere('email', 'like', '%' . $searchKey . '%')
            ->orWhere('phone', 'like', '%' . $searchKey . '%')
            ->paginate($request->per_page);
        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Users not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    //get user by id
    public function showUser($id){
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    //delete user
    public function deleteUser($id) {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

}
