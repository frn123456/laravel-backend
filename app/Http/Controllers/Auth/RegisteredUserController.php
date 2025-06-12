<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // public function store(Request $request)
    // {
    //     $fields = $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    //         'password' => ['required', 'confirmed', Rules\Password::defaults()],
    //     ]);

    //     $user = User::create($fields);

    //     $token = $user->createToken($request->name);

    //     return [
    //         'user' => $user,
    //         'token' => $token->plainTextToken
    //     ];
    // }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    
        // ✅ Hash the password before storing
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']), // Hash password
        ]);
    
        // ✅ Log in the user (Creates session & cookie)
        Auth::login($user);
    
        // ✅ Return the authenticated user
        return response()->json([
            'user' => $user,
            'message' => 'User registered successfully'
        ]);
    }

    // public function store(Request $request)
    // {
    //     $fields = $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
    //         'password' => ['required', 'confirmed', Rules\Password::defaults()],
    //     ]);

    //     // Create User
    //     $user = User::create([
    //         'name' => $fields['name'],
    //         'email' => $fields['email'],
    //         'password' => bcrypt($fields['password']),
    //     ]);

    //     // Log in the user (Creates session & cookie)
    //     Auth::login($user);

    //     // Return the authenticated user
    //     return response()->json([
    //         'user' => $user,
    //         'message' => 'User registered successfully'
    //     ]);
    // }
}
