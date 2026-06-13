<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 💡 অ্যাপ থেকে আসা রিকোয়েস্টে রোল ফিল্ডের আর প্রয়োজন নেই
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|regex:/^[a-zA-Z0-9._%+-]+@uttarauniversity\.edu\.bd$/',
            'password' => 'required|min:6',
        ], [
            'email.regex' => 'নিবন্ধন শুধুমাত্র উত্তরা ইউনিভার্সিটির প্রাতিষ্ঠানিক ইমেইল (@uttarauniversity.edu.bd) দিয়ে সম্ভব।'
        ]);

        // 💡 এখানে রোল সরাসরি 'passenger' লক করে দেওয়া হলো
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'passenger',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // ২. লগইন
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }
}
