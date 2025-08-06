<?php

namespace App\Http\Controllers\API;

use App\Filament\Resources\ReportIssuesResource;
use App\Http\Controllers\Controller;
use App\Models\DiscussionRequest;
use App\Models\ReportIssues;
use App\Models\RequestAcceptDenied;
use App\Models\Service;
use App\Models\User;
use App\Traits\ApiResponse;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Filament\Notifications\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportIssueController extends Controller
{
    use ApiResponse;

    /* public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:issue,other',
            'description' => 'nullable|string',
        ]);

        try {
            // Get the latest accepted discussion request for the current user
            $latestAcceptedRequest = DiscussionRequest::where('user_id', auth()->id())
                ->where('status', 'accepted')
                ->latest()
                ->firstOrFail();

            // Create report issue
            $report = ReportIssues::create([
                'user_id' => auth()->id(),
                'engineer_id' => $latestAcceptedRequest->engineer_id, // Engineer from latest accepted request
                'type' => $request->type,
                'description' => $request->description,
            ]);

            return $this->ok('Report submitted successfully', $report);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    } */


    public function store(Request $request)
    {
        $request->validate([
            'discussion_request_id' => 'required|exists:discussion_requests,id',
            'type' => 'required|in:issues,else',
            'description' => 'nullable|string',
        ]);

        try {
            $discussionRequest = DiscussionRequest::findOrFail($request->discussion_request_id);
//            // Check if the service_title is empty
//            if (empty($discussionRequest->service_title)) {
//                // If service_title is empty, fetch it from the Services table
//                $service = Service::find($discussionRequest->service_id); // Assuming `Service` is the name of the model for the services table
//
//                if ($service) {
//                    // If the service exists, use its title
//                    $serviceTitle = $service->title; // Assuming 'title' is the field name in the services table
//                } else {
//                    return $this->error('Service not found for the discussion request.', 400);
//                }
//            } else {
//                // Use the service_title from the discussion request if it's not empty
//                $serviceTitle = $discussionRequest->service_title;
//            }
//
//            // Check if the discussion request is "completed"
//            if ($discussionRequest->status !== 'completed') {
//                return $this->error('Discussion request must be completed before reporting an issue.', 400);
//            }
//
//            // Check if there is an accepted status in the request_accept_denieds table
//            $requestAcceptDenied = RequestAcceptDenied::where('request_id', $discussionRequest->id)
//                ->where('status', 'accepted')
//                ->first();
//
//            if (!$requestAcceptDenied) {
//                return $this->error('Discussion request has not been accepted by the engineer.', 400);
//            }

            // Create report issue with the service title
            $report = ReportIssues::create([
                'user_id' => auth()->id(),
                'engineer_id' => $discussionRequest->engineer_id ?? 8, // Engineer from the discussion request
                'service_title' => $discussionRequest->service_title, // Service title taken from the Services table or DiscussionRequest
                'type' => $request->type,
                'description' => $request->description,
                'discussion_request_id' => $request->discussion_request_id,
            ]);

            $adminUsers = User::where('role', 'admin')->get();
            foreach ($adminUsers as $adminUser) {
                Notification::make()
                    ->title('New issue request by '.Auth::user()->name)->warning()->broadcast($adminUsers)->actions([
                        Action::make('view')->url(ReportIssuesResource::getUrl()),
                    ])->sendToDatabase($adminUser);
            }

            return $this->ok('Report submitted successfully', $report);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
