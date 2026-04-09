<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushDeviceToken;
use Illuminate\Http\Request;

class PushDeviceController extends Controller
{
    public function index(Request $request)
    {
        $devices = PushDeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get(['id', 'platform', 'device_name', 'app_version', 'is_active', 'last_seen_at', 'created_at']);

        return response()->json(['devices' => $devices], 200);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'platform' => 'required|in:ios,android,web',
            'token' => 'required|string|min:16|max:1024',
            'device_name' => 'nullable|string|max:120',
            'app_version' => 'nullable|string|max:40',
        ]);

        $user = $request->user();
        $record = PushDeviceToken::query()->updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $user->id,
                'organisation_id' => $user->organisation_id,
                'platform' => $data['platform'],
                'device_name' => $data['device_name'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'is_active' => true,
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Push device registered',
            'device' => $record,
        ], 200);
    }

    public function unregister(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string|max:1024',
        ]);

        PushDeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token', $data['token'])
            ->update(['is_active' => false, 'last_seen_at' => now()]);

        return response()->json(['message' => 'Push device unregistered'], 200);
    }
}
