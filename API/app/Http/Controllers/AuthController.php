<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController
{
    protected $secret = "MFJJrLdUS+ScapA5TsZwtLlOov76Em+X/yEAA9McEUw=";
    protected $alg = "HS256";

    public function generate(Request $request, int $expiryHours = 1)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6']
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $payload = [
            'username' => $request->username,
            'role' => $user->role,
            'iat' => time(),
            'exp' => time() + ($expiryHours * 3600 * 10),
        ];

        $header = json_encode(['typ' => 'JWT', 'alg' => $this->alg]);

        $base64URLHeader = $this->encodeBase64($header);
        $base64URLPayload = $this->encodeBase64(json_encode($payload));

        $signature = hash_hmac('sha256', $base64URLHeader . '.' . $base64URLPayload, $this->secret, true);
        $base64URLSignature = $this->encodeBase64($signature);

        $JWT = $base64URLHeader . '.' . $base64URLPayload . '.' . $base64URLSignature;
        
        return response()->json(['token' => $JWT]);
    }

    public function verifyToken (Request $request)
    {
        try {
            $AuthorizationHeader = $request->header('Authorization');
            if (!$AuthorizationHeader) {
                return response()->json(['valid' => false],401);
            }

            $token = str_replace('Bearer ', '', $AuthorizationHeader);
            $parts = explode('.',$token);
            if (count($parts) !== 3) {
                return response()->json(['valid' => false],400);
            }

            [$header , $payload , $signature] = $parts;

            $payloadDecode = json_decode($this->decodeBase64($payload), true);
            if (isset($payloadDecode['exp']) && $payloadDecode['exp'] < time()) {
                return response()->json(['valid' => false],400);
            }
            
            $validSignature = $this->encodeBase64(hash_hmac('sha256', $header . '.' . $payload, $this->secret, true));

            return response()->json(['valid' => hash_equals($signature, $validSignature)]);
        } catch (Exception $e) {
            return response()->json(['valid' => false],500);
        }
    }

    public function getPayload(Request $request)
    {
        $token = $request->header("Authorization");
        if (!$token) {
            return response()->json(['error' => "Token is missing!"], 400);
        }
        $token = str_replace('Bearer ', '', $token);
        return response()->json(['payload' => json_decode($this->decodeBase64(explode('.',$token)[1]), true)]);
    }

    protected function encodeBase64 (string $data): string
    {
        return str_replace(['+','/','='], ['-','_',''],base64_encode($data));
    }

    protected function decodeBase64 (string $data): string
    {
        return base64_decode(str_replace(['-','_'],['+','/'],$data));
    }
}
