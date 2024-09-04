<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Inquiry;
use Carbon\Carbon;

class WebhookController extends Controller
{
    public function registerNewUrl(Request $request)
    {
        $user = User::where('name', $request->name)->first();
        if ($user) {
            return response()->json(['message' => 'User already registered'], 409);
        } else {
            $pathInquiry = '/v1.1/transfer-va/inquiry';
            $newUser = $this->addUser($request);
            $userDetails = $this->inquiryUser($newUser);
            $inquiry = new Inquiry();
            $inquiry->path_url = $request->name . $pathInquiry . $request->path_inquiry;
            $inquiry->user_id = $userDetails->id;
            $inquiry->created_at = Carbon::now();
            $inquiry->type = $request->type;
            $inquiry->token = $request->name . '/' . $request->path_token;
            $inquiry->save();
            return response()->json(['message' => 'Success'], 201);
        }
    }

    public function loginUser(Request $request)
    {
        //halaman login
    }

    public function validationUserLogin(Request $request)
    {
        //get data user.name dan user.password dari loginUser
        //validasi user.name apakah ada, semisal ada lanjut ke validasi selanjutnya
        //jika user.name not found, response dengan user not found, please register first to access this feature
        //validasi user.password apakah sama dengan $request atau tidak, jangan lupa encrypt password
        //
    }

    public function tokenUser(Request $request, $user)
    {
        $userData = User::where('name', $user)->latest()->first();
        $userId = $userData->id ?? null;
        //call token function dari dokuUtils
        //validasi signature reqeust
        //prepare credential user
        
    }

    public function login(Request $requet)
    {
        echo "login dulu";
    }

}
