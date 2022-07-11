<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{

    /**
 * @OA\Post(
 * path="/api/register",
 * summary="Sign up",
 * description="signing up new account",
 * operationId="authRegister",
 * tags={"auth"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass user credentials",
 *    @OA\JsonContent(
 *       required={"email","password"},
 *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
 *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
 *       @OA\Property(property="name", type="string", format="string", example="User1"),
 *    ),
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Wrong credentials response",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Invalid credentials")
 *        )
 *     )
 * )
     */
    public function register (Request $request) {
           $fileds =  $request->validate(['name' => 'required|string', 'email' => 'required|string|unique:users,email', 'password'=>'required|string|confirmed']);
    $user = User::create([
        'name' => $fileds['name'],
        'email' => $fileds['email'],
        'password' => bcrypt($fileds['password']),
    ]);

        $token = $user->createToken('myAppToken')->plainTextToken;

        $response = ['user' => $user, 'token' => $token];

        return response()->json($response, Response::HTTP_CREATED);
    }

    /**
 * @OA\Post(
 * path="/api/logout",
 * summary="Logout",
 * description="Logout user and invalidate token",
 * operationId="authLogout",
 * tags={"auth"},
 * security={ {"bearer": {} }},
 * @OA\Response(
 *    response=200,
 *    description="Success",
 *    @OA\JsonContent(
 *     @OA\Property(property="message", type="string", example="Successfully logged out"),
 *    ),
 *  ),
 * @OA\Response(
 *    response=401,
 *    description="Returns when user is not authenticated",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="unauthorized"),
 *    ),
 *   ),
 * )
     */
    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        return [
            'messsage'=> 'Successfuly logout'
        ];
    }

    /**
 * @OA\Post(
 * path="/api/login",
 * summary="Sign in",
 * description="Login by email, password",
 * operationId="authLogin",
 * tags={"auth"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass user credentials",
 *    @OA\JsonContent(
 *       required={"email","password"},
 *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
 *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
 *    ),
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Wrong credentials response",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
 *        )
 *     ),
 * @OA\Response(
 *   response=200,
 *   description="Success",
 *   @OA\JsonContent(
 *        @OA\Property(property="type", type="string", example="bearer"),
 *        )
 *       )
 *    )
     */

    public function login(Request $request){
        $fileds =  $request->validate(['email' => 'required|string', 'password'=>'required|string']);

        // check email
        $user = User::where('email', $fileds['email'])->first();

        //check password
        if (!$user || !Hash::check($fileds['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('myAppToken')->plainTextToken;
        $response = ['user' => $user, 'token' => $token];
        return response()->json($response, Response::HTTP_OK);
    }
}
