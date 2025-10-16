<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Order;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Send a general notification.
     *
     * @param  string $title
     * @param  string $message
     * @param  string|null $url
     * @param  string|null $type
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|array $recipients
     */
    public function send($title, $message, $url = null, $type = null, $recipients = [])
    {
        if (!($recipients instanceof Collection)) {
            $recipients = collect($recipients);
        }

        foreach ($recipients as $recipient) {
            $recipient->notify(new GenericNotification(
                $title,
                $message,
                $url,
                $type
            ));
        }
    }

    /**
     * When a support ticket is created.
     */
    public function notifyTicketCreated(SupportTicket $ticket)
    {
        // Notify all admins
        $this->send(
            'New Support Ticket',
            "A new ticket #{$ticket->id} was created.",
            "/support/chatsupport/{$ticket->id}",
            'ticket',
            User::all()
        );

        // Notify traveler / rider (if any)
        if ($ticket->order) {
            $this->send(
                'Support Ticket Created',
                "Ticket for order #{$ticket->order->id}",
                "/orders/{$ticket->order->id}",
                'ticket',
                collect([
                    $ticket->order->traveler,
                    $ticket->order->rider,
                ])->filter() // removes nulls
            );
        }
    }

    /**
     * When a support message is sent.
     */
    public function notifyMessageSent(SupportMessage $message)
    {
        $ticket = $message->ticket;
        if (! $ticket) return;

        $sender = $message->senderable;
        $senderClass = get_class($sender);

        $recipients = collect();


        if ($ticket->order_id) {
            $order = Order::with(['traveler', 'partner', 'rider'])->find($ticket->order_id);

            if ($order) {
                // Add all participants from order
                if ($order->traveler) $recipients->push($order->traveler);
                if ($order->partner) $recipients->push($order->partner);
                if ($order->rider) $recipients->push($order->rider);
            }

            // Always include admins (User model)
            $recipients = $recipients->merge(User::all());
        } else {

            if ($senderClass === User::class) {

                if ($ticket->user) {
                    $recipients->push($ticket->user);
                }
            } else {

                $recipients = User::all();
            }
        }

        $recipients = $recipients->reject(
            fn($r) =>
            $r->id === $sender->id && get_class($r) === $senderClass
        );

        foreach ($recipients as $recipient) {
            if (method_exists($recipient, 'notify')) {
                $recipient->notify(new GenericNotification(
                    'New Support Message',
                    "A new message was sent in ticket #{$ticket->id}",
                    "/support/chatsupport/{$ticket->id}",
                    'message'
                ));
            }
        }
    }

    /**
     * When an order is dispatched.
     */
    public function notifyOrderDispatched(Order $order)
    {
        $this->send(
            'Order Dispatched',
            "Your order #{$order->id} has been dispatched.",
            "/orders/{$order->id}",
            'order',
            collect([$order->traveler, $order->rider])->filter()
        );
    }

    /**
     * You can add more events like:
     * - notifyOrderDelivered()
     * - notifyRefundRequested()
     * - notifyAdminAlert()
     */
}
