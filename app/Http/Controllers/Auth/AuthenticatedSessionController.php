<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;


class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    /* use for cookies only  

    public function store(Request $request)
     {
         // Validate user inputs
         $request->validate([
             'email' => 'required|email',
             'password' => 'required'
         ]);

         // Attempt to authenticate the user
         if (!Auth::attempt($request->only('email', 'password'))) {
             return response()->json([
                 'errors' => ['email' => ['Invalid credentials']]
             ], 401);
         }

         // Generates a new session ID without destroying the current session to prevent session fixation attacks
         $request->session()->regenerate();

         return response()->json([
             'user' => Auth::user(), // Returns authenticated user
             'message' => 'Login successful'
         ]);
     } */

    // public function store(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'email' => 'required|string|email',
    //         'password' => 'required|string',
    //     ]);

    //     if (!Auth::attempt($credentials)) {
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }

    //     $user = Auth::user();

    //     return response()->json([
    //         'user' => $user,
    //         'message' => 'Login successful'
    //     ]);
    // }


    public function store(Request $request)
    {
        //Validate the inputs of the user 
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        //Fetches a user from the user table by searching for an email that matches the email input of the user  
        $user = User::where('email', $request->email)->first();

        /*  Checks if the user exists in the user table and if yes, checks if the password input of the user matches 
        that of the hashed password stored in the database */
        if (
            !$user || !Hash::check(
                $request->password,
                $user->password
            )
        )
        // Returns an error if the user input is incorrect
        {
            return response()->json([
                'errors' => [
                    'email' => ['Invalid credentials']
                ]
            ], 422);
        }

        //Generates a token for the authenticated user from the database
        $token = $user->createToken('auth_token');

        //Generates a refresh token
        $refreshToken = bin2hex(random_bytes(32)); // Generate a refresh token

        Cookie::queue(
            'refresh_token',
            $refreshToken,
            60 * 24 * 7, // 7 days
            '/', // Path
            '.up.railway.app', // ✅ Domain
            true, // Secure (HTTPS)
            true, // HttpOnly
            false, // Raw
            'None' // SameSite
        );


        //Saves the refresh token in the database
        $user->refresh_token = $refreshToken;
        $user->save();

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
            'message' => 'Login successful'
        ];
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['message' => 'Refresh token not found'], 401);
        }

        $user = User::where('refresh_token', $refreshToken)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid refresh token'], 401);
        }

        return response()->json(['access_token' => $user->createToken('auth_token')->plainTextToken]);
    }

    /**
     * Destroy an authenticated session.
     */
    /* public function destroy(Request $request)
    {
        Auth::guard('web')->logout();//Logs out currently authenticated user    

        $request->session()->invalidate();//Completely clears all session data and invalidates the session

        $request->session()->regenerateToken();//Regenerates the csrf token

        return response()->json([
            'message' => 'Logout successful'
        ]);
    } */

    public function destroy(Request $request)
    {
        Cookie::queue(Cookie::forget("refresh_token"));

        return response()->json(['message' => 'Logged out successfully']);
    }

}
