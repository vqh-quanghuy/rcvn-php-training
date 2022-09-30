<?php
namespace App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

use App\Models\User;

class UserAuthController extends Controller
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'remember' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Inputs',
                'error' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $remember = $request->remember ? true : false;

        // Get Client IP Address
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (Auth::attempt(['email'=>$request->email, 'password'=>$request->password, 'is_active' => 1], $remember)) {
            /** @var \App\Models\User $user **/
            $user = Auth::user();
            $user->last_login_ip = $ip;
            $user->last_login_at = date('M d, Y H:i A', strtotime(now()));
            $user->save();
            $token = $user->createToken('rcvn2012')->plainTextToken;
            // $minutes = 1440; // 24 hours
            $minutes = 60; // 1 hour
            $timestamp = now()->addMinute($minutes);
            $expires_at = date('M d, Y H:i A', strtotime($timestamp));
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_at' => $expires_at
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successful',
        ], Response::HTTP_OK);
    }
}