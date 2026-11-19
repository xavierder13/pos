<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

/**
 * The signed-in user's own notifications (Laravel database notifications).
 * No permission: everyone sees only their own.
 */
class NotificationController extends Controller
{
    // GET /notifications?unread=1&page=&per_page= → { data: [...], meta: {...}, unread_count }
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'unread' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $user = $request->user();
        $page = ($request->boolean('unread') ? $user->unreadNotifications() : $user->notifications())
            ->paginate((int) ($filters['per_page'] ?? 20));

        return response()->json([
            'data' => $page->getCollection()->map(fn (DatabaseNotification $n) => $this->row($n))->values(),
            'meta' => ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total()],
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    // GET /notifications/unread-count → { unread_count } (the bell polls it with X-Session-Check: passive)
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json(['unread_count' => $request->user()->unreadNotifications()->count()]);
    }

    // POST /notifications/{id}/read → { data, unread_count }
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['data' => $this->row($notification), 'unread_count' => $request->user()->unreadNotifications()->count()]);
    }

    // POST /notifications/read-all → { unread_count: 0 }
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['unread_count' => 0]);
    }

    // DELETE /notifications/{id} → { message, unread_count }
    public function destroy(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return response()->json(['message' => 'Notification deleted.', 'unread_count' => $request->user()->unreadNotifications()->count()]);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(DatabaseNotification $n): array
    {
        return [
            'id' => $n->id,
            'title' => $n->data['title'] ?? class_basename($n->type),
            'body' => $n->data['body'] ?? '',
            'link' => $n->data['link'] ?? null,
            'type' => $n->data['type'] ?? 'info',
            'read_at' => $n->read_at?->toIso8601String(),
            'created_at' => $n->created_at?->toIso8601String(),
        ];
    }
}
