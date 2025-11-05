<?php

namespace App\Http\Controllers;

use App\Events\SupportMessageSent;
use App\Http\Resources\SupportMessageResource;
use App\Http\Resources\SupportTicketResource;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\NotificationService;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    use ApiResponse;
    //  public function index($ticketId)
    // {
    //     $messages = SupportMessage::where('support_ticket_id', $ticketId)
    //         ->with('senderable')
    //         ->orderBy('created_at')
    //         ->get();


    //     return $this->success(SupportMessageResource::collection($messages), 'Messages retrieved successfully', 200);
    // }



    public function index($ticketId)
    {
        // Load ticket with messages + senderable for each message
        $ticket = SupportTicket::with(['messages.senderable', 'order', 'user'])
            ->where('ticket_id', $ticketId)
            ->firstOrFail();

        return $this->success(
            new SupportTicketResource($ticket),
            'Ticket and messages retrieved successfully',
            200
        );
    }




    public function store(Request $request, NotificationService $notify)
    {
        // Find the ticket by ticket_id and get its primary key (id)
        $ticket = SupportTicket::where('ticket_id', $request->ticket_id)->firstOrFail();

        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'senderable_id' => auth()->id(),
            'senderable_type' => get_class(auth()->user()),
            'message' => $request->message,
        ]);

        broadcast(new SupportMessageSent($message))->toOthers();


         $notify->notifyMessageSent($message);

        return $this->success($message, 'Message sent successfully', 201);
    }



    public function fetchMessages($ticketId)
    {
        // Find the ticket by ticket_id and get its primary key (id)
        $ticket = SupportTicket::where('ticket_id', $ticketId)->firstOrFail();
        
        $messages = SupportMessage::where('support_ticket_id', $ticket->id)
            ->with('senderable')
            ->orderBy('created_at', 'asc')
            ->get();

        // return response()->json($messages);
        return $this->success($messages, 'Messages retrieved successfully', 200);
    }
}
