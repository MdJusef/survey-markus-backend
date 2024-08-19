<?php

namespace App\Http\Controllers;

use App\Notifications\AdminNotification;
use App\Notifications\CompanyNotification;
use App\Notifications\EmployeeNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    public function guard()
    {
        return Auth::guard('api');
    }


    function sendNotification($image = null,$name = null,$message = null, $time = null, $data = null, $isGlobal = null)
    {
        try {
            Notification::send($data, new EmployeeNotification($image,$name,$message,$time,$data, $isGlobal));
            return response()->json([
                'message' => 'Notification Added Successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    function sendAdminNotification($image = null,$name = null,$message = null, $time = null, $data = null, $isGlobal = null)
    {
        try {
            Notification::send($data, new AdminNotification($image,$name,$message,$time,$data, $isGlobal));
            return response()->json([
                'message' => 'Notification Added Successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    function sendCompanyNotification($image = null,$name = null,$message = null, $time = null, $data = null, $isGlobal = null)
    {
        try {
            Notification::send($data, new CompanyNotification($image,$name,$message,$time,$data, $isGlobal));
            return response()->json([
                'message' => 'Notification Added Successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }


    public function notifications()
    {
        $user = $this->guard()->user();

        if ($user) {
            $userId = $user->id;
            $query = DB::table('notifications')
                ->where('notifiable_id', $userId)
                ->orWhere('type', '=', 'App\\Notifications\\EmployeeNotification')
                ->orderBy('created_at', 'desc')
                ->get();

            $user_notifications = $query->map(function ($notification) {
                $notification->data = json_decode($notification->data);
                return $notification;
            });

            $unread_count = $query->where('read_at',null)->count();

            return response()->json([
                'message' => 'Notification list',
                'notifications' => $user_notifications,
                'unread_count' => $unread_count,
            ], 200);
        } else {
            return response()->json([
                'message' => 'User not authenticated',
                'notifications' => [],
                'unread_count' => 0,
            ], 401);
        }
    }

    public function userReadNotification()
    {
        $user = $this->guard()->user();

        if ($user) {
            $userId = $user->id;

            $unreadNotifications = DB::table('notifications')
                ->where(function ($query) use ($userId) {
                    $query->where(function ($query) use ($userId) {
                        $query->where('notifiable_type', 'App\Models\User')
                            ->where('notifiable_id', $userId);
                    });
                })
                ->whereNull('read_at')
                ->get();

            // Mark notifications as read
            foreach ($unreadNotifications as $notification) {
                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['read_at' => now()]);
            }

            return response()->json([
                'message' => 'Notifications marked as read successfully',
                'unread_notifications_count' => 0,
            ], 200);
        } else {
            return response()->json([
                'message' => 'User not authenticated',
            ], 401);
        }
    }

    public function adminNotification(){

        $Notifications = DB::table('notifications')
            ->where('notifications.type', 'App\\Notifications\\AdminNotification')
            ->orderBy('notifications.created_at', 'desc')
            ->paginate(7);


//        $formattedReadNotifications = $Notifications->map(function($notification) {
//            $notification->data = json_decode($notification->data);
//            return $notification;
//        });

        return response()->json([
            'message' => 'Notifications List',
            'notifications' => $Notifications,

//            'pagination' => [
//                'current_page' => $Notifications->currentPage(),
//                'total_pages' => $Notifications->lastPage(),
//                'per_page' => $Notifications->perPage(),
//                'total' => $Notifications->total(),
//                'next_page_url' => $Notifications->nextPageUrl(),
//                'prev_page_url' => $Notifications->previousPageUrl(),
//            ]
        ]);
    }

    public function readNotificationById(Request $request)
    {
        $notification = DB::table('notifications')->find($request->id);
        if ($notification) {
            $notification->read_at = Carbon::now();
            DB::table('notifications')->where('id', $notification->id)->update(['read_at' => $notification->read_at]);
            return response()->json([
                'status' => 'success',
                'message' => 'Notification read successfully.',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found',
            ], 404);
        }
    }
}
