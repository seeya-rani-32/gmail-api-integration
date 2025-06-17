<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Google_Client;
use Google_Service_Gmail;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        $client = new Google_Client();

        // Load your app credentials (client_id, secret)
        $client->setAuthConfig(storage_path('app/credentials.json'));

        // Ask only to read Gmail and send
        $client->addScope([
            Google_Service_Gmail::GMAIL_SEND,
            Google_Service_Gmail::GMAIL_READONLY,
        ]);

        // Where should Google redirect after login?
        $client->setRedirectUri(route('gmail.callback'));

        // Request long-term access
        $client->setAccessType('offline'); // important!
        $client->setPrompt('consent'); // always show account select screen

        // Redirect to Google login page
        return redirect()->away($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->setRedirectUri(route('gmail.callback'));

        // Google sends a 'code', exchange it for a token
        $token = $client->fetchAccessTokenWithAuthCode($request->code);

        // Save the token (like storing a library card)
        file_put_contents(storage_path('app/gmail-token.json'), json_encode($token));

        // 🔍 Find the user you want to update (e.g., user with ID 1)
        $user = User::find(1); // change this to your intended user ID

        if (!$user) {
            return 'User not found.';
        }

        // 📝 Update user record with Gmail token details
        $user->gmail_access_token = $token['access_token'];
        $user->gmail_refresh_token = $token['refresh_token'] ?? $user->gmail_refresh_token;
        $user->gmail_token_expires_in = $token['expires_in'];
        $user->gmail_token_created_at = now();
        $user->save();

        return redirect()->route('gmail.inbox')->with('success', 'Gmail connected successfully!');
    }
}
