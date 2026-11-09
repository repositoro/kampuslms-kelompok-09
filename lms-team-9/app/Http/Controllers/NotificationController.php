<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, string $id)
    {
        // Otorisasi IDOR: Hanya pemilik notifikasi yang bisa menandai dibaca
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Notifikasi berhasil ditandai sebagai sudah dibaca.']);
        }

        return back()->with('success', 'Notifikasi berhasil ditandai sebagai sudah dibaca.');
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Semua notifikasi berhasil ditandai sebagai sudah dibaca.']);
        }

        return back()->with('success', 'Semua notifikasi berhasil ditandai sebagai sudah dibaca.');
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
