<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cache unread count per user
        $unreadCount = \Cache::remember("unread_count_{$user->id}", 60, function() use ($user) {
            return $user->unreadNotifications()->count();
        });

        return response()->json([
            'notifications' => $user->notifications()->take(20)->get(),
            'unread_count' => $unreadCount
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        \Cache::forget("unread_count_{$user->id}");

        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        \Cache::forget("unread_count_{$user->id}");
        return response()->json(['message' => 'All marked as read']);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }

    public function subscribePush(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        \DB::table('push_subscriptions')->updateOrInsert(
            ['endpoint' => $request->endpoint],
            [
                'site_id' => $request->site_id,
                'public_key' => $request->keys['p256dh'],
                'auth_token' => $request->keys['auth'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json(['message' => 'Subscribed successfully']);
    }
}
