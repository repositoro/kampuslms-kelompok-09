<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar semua notifikasi milik pengguna.
     */
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Menandai satu notifikasi sebagai dibaca lalu mengarahkan ke URL tujuan.
     */
    public function read(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $url = $notification->data['url'] ?? null;
        $path = is_string($url) ? parse_url($url, PHP_URL_PATH) : null;

        if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            $path = route('dashboard', [], false);
        }

        return redirect($path);
    }

    /**
     * Menandai seluruh notifikasi pengguna sebagai dibaca.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }
}