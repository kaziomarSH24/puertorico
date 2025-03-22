<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    /**
     * Register a User
     */

    public function register(Request $request)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'device_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }


        $data = [
            'name' => $request->full_name,
            'email' => $request->email,
        ];

        //send otp
        $data = sentOtp($data, 5);
        $user = new User();
        $user->name = $request->full_name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->otp = $data['otp'];
        $user->otp_expire_at = $data['otp_expire_at'];
        $user->device_token = $request->device_token;
        $user->save();

        return response()->json([
            'success' => true,
            'otp' => $data['otp'],
            'message' => 'User created successfully! Please check your email for OTP!',
        ]);
    }

    /**
     * Login a User
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|',
            'password' => 'required|string|min:8',
            // 'device_token' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        if (!$token = JWTAuth::attempt($validator->validated())) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password!'
            ]);
        } else {
            $user = Auth::user();
            if ($user->email_verified_at == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your email address!',
                ]);
            }
        }

        $user->device_token = $request->device_token;
        $user->save();

        return $this->responseWithToken($token);
    }

    /**
     * Verify account
     */
    public function verifyEmail(Request $request)
    {
        if (isset($request->otp)) {
            $user = User::where('otp', $request->otp)->first();
            if ($user) {
                if ($user->otp_expire_at < Carbon::now()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'OTP has expired! Please request for a new OTP!',
                    ], 401);
                }
                $user->email_verified_at = Carbon::now();
                $user->otp = null;
                $user->otp_expire_at = null;
                $user->save();

                $token = JWTAuth::fromUser($user);

                return $this->responseWithToken($token, 'Email verified successfully!');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP!',
                ], 401);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'OTP is required!',
            ], 401);
        }
    }


    /**
     * Logout a User
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
            ]);
        }
    }


    /**
     * Response with Token
     */
    public function responseWithToken($token, $msg = null)
    {
        return response()->json([
            'success' => true,
            'message' => $msg ?? 'User logged in successfully!',
            'token_type' => 'bearer',
            'access_token' => $token,
            'expires_in' => JWTAuth::factory()->getTTL() * 600000000,
        ]);
    }


    /**
     * resend OTP // also using for forgot password
     */
    public function resendOtp(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $data = [
                'name' => $user->name,
                'email' => $user->email,
            ];
            $data = sentOtp($data, 5);
            $user->otp = $data['otp'];
            $user->otp_expire_at = $data['otp_expire_at'];
            $user->save();

            return response()->json([
                'success' => true,
                'otp' => $data['otp'],
                'message' => 'OTP sent successfully! Please check your email!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found!',
            ]);
        }
    }


    /**
     * reset password
     */
    public function resetPassword(Request $request)
    {

            $user = Auth::user();
            if ($user) {
                $validator = Validator::make($request->all(), [
                    'password' => 'required|string|confirmed|min:6',
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors());
                }
                $user->password = Hash::make($request->password);
                $user->otp = null;
                $user->otp_expire_at = null;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Password reset successfully!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized user!',
                ], 401);
            }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        if (Hash::check($request->old_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid old password!',
            ]);
        }
    }

    //update profile
    // public function updateProfile(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'first_name' => 'required|string',
    //         'last_name' => 'required|string',
    //         'phone' => 'required|string|unique:users,phone,' . Auth::id(),
    //         'avatar' => 'nullable|image|mimes:jpeg,png,jpg| max:2048',
    //         'address' => 'required|string',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json($validator->errors());
    //     }

    //     $user = Auth::user();

    //     // Check update limit for lawyer
    //     if ($user->role == 'lawyer') {

    //         $currentWeekStart = now()->startOfWeek();
    //         $currentWeekEnd = now()->endOfWeek();
    //         $lastUpdated = $user->last_updated_at ? Carbon::parse($user->last_updated_at) : null;

    //         if ($lastUpdated && $lastUpdated->between($currentWeekStart, $currentWeekEnd)) {
    //             if ($user->update_count >= 2) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'You can only update your profile twice a week.'
    //                 ], 400);
    //             }
    //         } else {
    //             $user->update_count = 0;
    //         }
    //         $user->update_count += 1;
    //         $user->last_updated_at = now();
    //     }
    //     $user->first_name = $request->first_name;
    //     $user->last_name = $request->last_name;
    //     $user->phone = $request->phone;
    //     $user->address = $request->address;

    //     if ($request->hasFile('avatar')) {

    //         if (!empty($user->avatar)) {
    //             $old_avatar = $user->avatar;
    //             if (Storage::disk('public')->exists($old_avatar)) {
    //                 Storage::disk('public')->delete($old_avatar);
    //             }
    //         }

    //         $avatar = $request->file('avatar');
    //         $user->avatar = $avatar->store('uploads/avatars', 'public');
    //     }
    //     $user->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Profile updated successfully!',
    //         'user' => $user,
    //     ]);
    // }

    public function validateToken(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if ($token) {
                $user = JWTAuth::setToken($token)->authenticate();

                if ($user) {
                    return response()->json([
                        'token_status' => true,
                        'message'      => 'Token is valid.',
                    ]);
                } else {
                    return response()->json([
                        'token_status' => false,
                        'message'      => 'Token is valid but user is not authenticated.',
                    ]);
                }
            }

            return response()->json([
                'token_status' => false,
                'error'        => 'No token provided.',
            ], 401);

        } catch (JWTException $e) {
            return response()->json([
                'token_status' => false,
                'error'        => 'Token is invalid or expired.',
            ], 401);
        }
    }

    //Social Login
    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'   => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'avatar'      => 'nullable|image|mimes:jpeg,png,jpg',
            'google_id'   => 'nullable|string',
            'facebook_id' => 'nullable|string',
            'device_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->first(),
            ], 422);
        }

        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            if (
                ($request->filled('google_id') && $existingUser->google_id === $request->google_id) ||
                ($request->filled('facebook_id') && $existingUser->facebook_id === $request->facebook_id)
            ) {
                return $this->responseWithToken(JWTAuth::fromUser($existingUser), 'User login successfully.');
            }

            if (!$existingUser->google_id && !$existingUser->facebook_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already exists. Please sign in manually.',
                ], 400);
            }

            $existingUser->update([
                'google_id'   => $request->google_id ?? $existingUser->google_id,
                'facebook_id' => $request->facebook_id ?? $existingUser->facebook_id,
            ]);

            return $this->responseWithToken(JWTAuth::fromUser($existingUser), 'User login successfully.');
        }

        //upload avatar
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarPath = $avatar->store('uploads/avatars', 'public');
        }

        $user = User::create([
            'name'        => $request->full_name,
            'email'       => $request->email,
            'avatar'      => $avatarPath ?? null,
            'password'    => Hash::make($request->email . '@' . $request->goole_id ?? $request->facebook_id),
            'google_id'   => $request->google_id,
            'facebook_id' => $request->facebook_id,
        ]);

        return $this->responseWithToken(JWTAuth::fromUser($user), 'User registered & logged in successfully.');
    }

}
