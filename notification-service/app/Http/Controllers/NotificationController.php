<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // POST /api/notifications
    // Called by the Order service (Mariah) when an order status changes.
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'  => 'required|integer',
            'order_id' => 'required|integer',
            'status'   => 'required|in:Pending,In Progress,Ready for Pickup,Completed,Cancelled',
        ]);

        $data['message'] = $this->makeMessage($data['order_id'], $data['status']);

        $notification = Notification::create($data);

        return response()->json(new NotificationResource($notification), 201);
    }

    // GET /api/notifications
    // The customer sees only their own notifications, newest first.
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $this->userId($request))
            ->orderBy('created_at', 'desc')
            ->get();

        return NotificationResource::collection($notifications);
    }

    // GET /api/notifications/unread-count  (number in the navbar)
    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', $this->userId($request))
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count], 200);
    }

    // PATCH /api/notifications/{id}/read
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        // a customer can't change another customer's notification
        if ($notification->user_id != $this->userId($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->update(['is_read' => true]);

        return new NotificationResource($notification);
    }

    // Who is the logged in user?
    // TODO: when Norah's login is ready, take the id from the token instead:
    //       return $request->user()->id;
    // For now we send ?user_id=1 in Postman to test.
    private function userId(Request $request)
    {
        return $request->input('user_id');
    }

    // Text of the notification (in English and Arabic)
    private function makeMessage($orderId, $status)
    {
        $messages = [
            'Pending'          => "Order #{$orderId} was received. | تم استلام طلبك رقم {$orderId}",
            'In Progress'      => "Order #{$orderId} is being washed. | طلبك رقم {$orderId} قيد التنفيذ",
            'Ready for Pickup' => "Order #{$orderId} is ready for pickup. | طلبك رقم {$orderId} جاهز للاستلام",
            'Completed'        => "Order #{$orderId} is completed. Thank you! | تم تسليم طلبك رقم {$orderId}، شكراً لك",
            'Cancelled'        => "Order #{$orderId} was cancelled. | تم إلغاء طلبك رقم {$orderId}",
        ];

        return $messages[$status];
    }
}
