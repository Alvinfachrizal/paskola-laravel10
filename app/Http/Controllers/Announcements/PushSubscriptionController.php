<?php

namespace App\Http\Controllers\Announcements;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Simpan subscription baru dari browser
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint'   => 'required|url',
            'public_key' => 'required|string',
            'auth_token' => 'required|string',
        ]);

        $user = Auth::user();

        // Cek berdasarkan user_id + hash endpoint
        $hash = hash('sha256', $data['endpoint']);

        $sub = PushSubscription::where('user_id', $user->id)
                   ->whereRaw('SHA2(endpoint, 256) = ?', [$hash])
                   ->first();

        if ($sub) {
            $sub->update(['public_key' => $data['public_key'], 'auth_token' => $data['auth_token']]);
        } else {
            PushSubscription::create([
                'user_id'    => $user->id,
                'endpoint'   => $data['endpoint'],
                'public_key' => $data['public_key'],
                'auth_token' => $data['auth_token'],
            ]);
        }

        return response()->json(['status' => 'subscribed'], 201);
    }

    /**
     * Hapus subscription (saat user unsubscribe / logout)
     */
    public function destroy(Request $request)
    {
        $endpoint = $request->validate(['endpoint' => 'required|string'])['endpoint'];

        PushSubscription::where('user_id', Auth::id())
            ->where('endpoint', $endpoint)
            ->delete();

        return response()->json(['status' => 'unsubscribed']);
    }

    /**
     * Cek status subscription user saat ini
     */
    public function status()
    {
        $count = PushSubscription::where('user_id', Auth::id())->count();
        return response()->json(['subscribed' => $count > 0]);
    }
}
