<?php

namespace App\Http\Controllers;

use App\Models\User;
use Google_Client;
use Google_Service_Gmail;
use Illuminate\Support\Str;
use Google_Service_Gmail_Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GmailController extends Controller
{
    public function inbox()
    {
        // 🔍 Find your user (use the same ID as before)
        $user = User::find(Auth::id());

        if (!$user || !$user->gmail_access_token) {
            return "Gmail is not connected for this user.";
        }

        // ✅ Set up the Google Client with stored access token
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->setAccessToken([
            'access_token' => $user->gmail_access_token,
            'refresh_token' => $user->gmail_refresh_token,
            'expires_in' => $user->gmail_token_expires_in,
            'created' => strtotime($user->gmail_token_created_at),
        ]);

        // 🔄 Refresh token if expired
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                // Save new token to DB
                $user->gmail_access_token = $newToken['access_token'];
                $user->gmail_token_expires_in = $newToken['expires_in'];
                $user->gmail_token_created_at = now();
                $user->save();
            } else {
                return "Access token expired and no refresh token available.";
            }
        }

        // ✅ Initialize Gmail service
        $service = new Google_Service_Gmail($client);

        // 📬 Get the latest 10 messages
        $messages = $service->users_messages->listUsersMessages('me', ['maxResults' => 20]);

        $emails = [];

        foreach ($messages->getMessages() as $message) {
            $msg = $service->users_messages->get('me', $message->getId());
            $payload = $msg->getPayload();
            $headers = collect($payload->getHeaders());

            $from = optional($headers->firstWhere('name', 'From'))->value;
            $subject = optional($headers->firstWhere('name', 'Subject'))->value;

            // 📨 Extract the body
            $body = '';

            // Case 1: If message is plain text only
            if ($payload->getBody() && $payload->getBody()->getData()) {
                $body = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload->getBody()->getData()));
            }

            // Case 2: If message has parts (e.g., multipart/alternative)
            if (!$body && $payload->getParts()) {
                foreach ($payload->getParts() as $part) {
                    if ($part->getMimeType() === 'text/plain' && $part->getBody()->getData()) {
                        $body = base64_decode(str_replace(['-', '_'], ['+', '/'], $part->getBody()->getData()));
                        break;
                    }
                    // Optional: fallback to HTML
                    if (!$body && $part->getMimeType() === 'text/html' && $part->getBody()->getData()) {
                        $body = base64_decode(str_replace(['-', '_'], ['+', '/'], $part->getBody()->getData()));
                    }
                }
            }

            $emails[] = [
                'id' => $message->getId(),
                'from' => $from,
                'subject' => $subject,
                'body' => Str::limit(strip_tags($body), 100),
            ];
        }

        // Show raw data (or pass to view)
        return view('gmail.inbox', compact('emails'));
    }

    public function compose()
    {
        return view('gmail.compose');
    }

    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        $client = new \Google_Client();
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->setAccessToken(json_decode(file_get_contents(storage_path('app/gmail-token.json')), true));

        $gmail = new Google_Service_Gmail($client);

        $rawMessageString = "To: {$request->to}\r\n";
        $rawMessageString .= "Subject: {$request->subject}\r\n";
        $rawMessageString .= "MIME-Version: 1.0\r\n";
        $rawMessageString .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
        $rawMessageString .= "{$request->message}";

        $rawMessage = base64_encode($rawMessageString);
        $rawMessage = str_replace(['+', '/', '='], ['-', '_', ''], $rawMessage); // base64url encoding

        $message = new Google_Service_Gmail_Message();
        $message->setRaw($rawMessage);

        try {
            $gmail->users_messages->send("me", $message);
            return redirect()->route('gmail.compose')->with('success', 'Email sent successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
