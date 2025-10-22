<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupportTicketResource;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Refund;
use App\Models\Rider;
use App\Models\SupportTicket;
use App\Models\Traveler;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Services\NotificationService;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{

    use ApiResponse;

    public function index()
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user instanceof User) {
            $tickets = SupportTicket::with(['order', 'user', 'messages.senderable'])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->success(SupportTicketResource::collection($tickets), 'Support tickets retrieved successfully', 200);
        }

        $tickets = SupportTicket::with(['order', 'user', 'messages.senderable'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('user_type', get_class($user));

                $q->orWhereHas('order', function ($orderQuery) use ($user) {
                    $class = get_class($user);

                    if ($class === \App\Models\Traveler::class) {
                        $orderQuery->where('traveler_id', $user->id);
                    } elseif ($class === \App\Models\Partner::class) {
                        $orderQuery->where('partner_id', $user->id);
                    } elseif ($class === \App\Models\Rider::class) {
                        $orderQuery->where('rider_id', $user->id);
                    }
                });
            })
            ->get();

        return $this->success(SupportTicketResource::collection($tickets), 'Support tickets retrieved successfully', 200);
    }


    /**
     * Store a new support ticket.
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'   => 'nullable|exists:orders,id',
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string',
        ]);

        $user = auth()->user();

        if (!empty($validated['order_id'])) {
            $ticket = SupportTicket::where('order_id', $validated['order_id'])
                ->where('user_id', $user->id)
                ->where('user_type', get_class($user))
                ->first();

            if ($ticket) {
                return $this->success(
                    new SupportTicketResource($ticket->load('order', 'user')),
                    'Support ticket already exists',
                    200
                );
            }
        }

        $ticket = SupportTicket::create([
            'order_id'       => $validated['order_id'] ?? null,
            'subject'        => $validated['subject'],
            'message'        => $validated['message'],
            'status'         => 'Pending',
            'user_id'   => $user->id,
            'user_type' => get_class($user),
        ]);

        User::each(function ($admin) use ($ticket) {
            $admin->notify(new GenericNotification(
                'New Support Ticket',
                "A new ticket #{$ticket->id} was created",
                "/support/chatsupport/{$ticket->id}",
                'ticket'
            ));
        });

        if ($ticket->order) {
            if ($ticket->order->traveler) {
                $ticket->order->traveler->notify(new GenericNotification(
                    'Support Ticket Created',
                    "Ticket for order #{$ticket->order->id}",
                    "/orders/{$ticket->order->id}",
                    'ticket'
                ));
            }

            if ($ticket->order->rider) {
                $ticket->order->rider->notify(new GenericNotification(
                    'Support Ticket Created',
                    "Ticket for order #{$ticket->order->id}",
                    "/orders/{$ticket->order->id}",
                    'ticket'
                ));
            }
        }

        return $this->success(
            new SupportTicketResource($ticket->load('order', 'user')),
            'Support ticket created successfully',
            201
        );
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status' => 'required|in:In Progress,Rejected,Pending',
        ]);

        $ticket->update([
            'status' => $request->status,
        ]);


        $ticket = SupportTicket::with(['messages.senderable', 'order', 'user'])
            ->find($ticket->id);

        return response()->json([
            'message' => 'Status updated successfully',
            'ticket'  => new SupportTicketResource($ticket),
        ]);
    }


    // public function checkOrCreate(Request $request)
    // {
    //     $request->validate([
    //         'order_id' => 'required|exists:orders,id',
    //     ]);

    //     $order = Order::with(['traveler', 'rider', 'partner'])->findOrFail($request->order_id);


    //     $ticket = SupportTicket::where('order_id', $order->id)->first();

        // if ($ticket) {
        //     return response()->json([
        //         'status' => 'exists',
        //         'ticket_id' => $ticket->id,
        //         'message' => 'Support ticket already exists.',
        //     ]);
        // }


    //     $ticket = SupportTicket::create([
    //         'order_id' => $order->id,
    //         'created_by' => auth()->id(),
    //     ]);


    //     $notifyUsers = collect([
    //         $order->traveler,
    //         $order->rider,
    //     ])->filter();

    //     $admins = User::role('admin')->get();
    //     $notifyUsers = $notifyUsers->merge($admins);


    //     Notification::send($notifyUsers, new SupportTicketCreated($ticket));

        // return response()->json([
        //     'status' => 'created',
        //     'ticket_id' => $ticket->id,
        //     'message' => 'New support ticket created.',
        // ]);
    // }

    // public function checkOrCreate(Request $request)
    // {
    //     $validated = $request->validate([
    //         'order_id'   => 'nullable|exists:orders,id',
    //     ]);

    //     $user = auth()->user();

    //     if (!empty($validated['order_id'])) {
    //         $ticket = SupportTicket::where('order_id', $validated['order_id'])
    //             ->where('user_id', $user->id)
    //             ->where('user_type', get_class($user))
    //             ->first();

    //         if ($ticket) {
    //             return $this->success(
    //                 new SupportTicketResource($ticket->load('order', 'user')),
    //                 'Support ticket already exists',
    //                 200
    //             );


    //         }
    //     }

    //     $ticket = SupportTicket::create([
    //         'order_id'       => $validated['order_id'] ?? null,
    //         'status'         => 'Pending',
    //         'user_id'   => $user->id,
    //         'user_type' => get_class($user),
    //     ]);

    //     User::each(function ($admin) use ($ticket) {
    //         $admin->notify(new GenericNotification(
    //             'New Support Ticket',
    //             "A new ticket #{$ticket->id} was created",
    //             "/support/chatsupport/{$ticket->id}",
    //             'ticket'
    //         ));
    //     });

    //     if ($ticket->order) {
    //         if ($ticket->order->traveler) {
    //             $ticket->order->traveler->notify(new GenericNotification(
    //                 'Support Ticket Created',
    //                 "Ticket for order #{$ticket->order->id}",
    //                 "/orders/{$ticket->order->id}",
    //                 'ticket'
    //             ));
    //         }

    //         if ($ticket->order->rider) {
    //             $ticket->order->rider->notify(new GenericNotification(
    //                 'Support Ticket Created',
    //                 "Ticket for order #{$ticket->order->id}",
    //                 "/orders/{$ticket->order->id}",
    //                 'ticket'
    //             ));
    //         }
    //     }

    //     return $this->success(
    //         new SupportTicketResource($ticket->load('order', 'user')),
    //         'Support ticket created successfully',
    //         201
    //     );
    //     //       return response()->json([
    //     //     'status' => 'created',
    //     //     'ticket_id' => $ticket->id,
    //     //     'message' => 'New support ticket created.',
    //     // ]);
    // }

       public function checkOrCreate(Request $request, NotificationService $notify)
    {
        $validated = $request->validate([
            'order_id'   => 'nullable|exists:orders,id',
        ]);

        $user = auth()->user();

        if (!empty($validated['order_id'])) {
            $ticket = SupportTicket::where('order_id', $validated['order_id'])
                ->where('user_id', $user->id)
                ->where('user_type', get_class($user))
                ->first();

            if ($ticket) {
                return $this->success(
                    new SupportTicketResource($ticket->load('order', 'user')),
                    'Support ticket already exists',
                    200
                );
            }
        }

        $ticket = SupportTicket::create([
            'order_id'  => $validated['order_id'] ?? null,
            'status'    => 'Pending',
            'user_id'   => $user->id,
            'user_type' => get_class($user),
        ]);

        $notify->notifyTicketCreated($ticket);

        return $this->success(
            new SupportTicketResource($ticket->load('order', 'user')),
            'Support ticket created successfully',
            201
        );
    }

}
