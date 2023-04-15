<?php

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/tweets', function () {
    return Tweet::with('user:id,name,username,avatar')->latest()->paginate(10);
});

Route::get('/tweets/{tweet}', function (Tweet $tweet) {
    return $tweet->load('user:id,name,username,avatar');
});

Route::post('/tweets', function (Request $request) {
    $request->validate([
        'body' => 'required'
    ]);

    return Tweet::create([
        'user_id' => 1,
        'body' => $request->body
    ]);
});

Route::get('/users/{user}', function (User $user) {
    return $user->only(
        'id',
        'name',
        'username',
        'avatar',
        'profile',
        'location',
        'link',
        'linkText',
        'created_at'
    );
});

Route::get('/users/{user}/tweets', function (User $user) {
    return $user->tweets()->with('user:id,name,username,avatar')->latest()->paginate(10);
});


Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken($request->device_name)->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user->only('id', 'name', 'username', 'email', 'avatar'),
    ], 201);
});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json('Logged out', 200);
});
