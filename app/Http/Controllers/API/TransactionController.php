<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DiscussionRequest;
use App\Models\ServiceFee;
use App\Models\Transaction;
use App\Models\UserProblem;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    use ApiResponse;

    // Function to create a payment transaction
    public function createTransaction(Request $request)
    {
        try {
            // Find the discussion request for the logged-in user
            $discussionRequest = DiscussionRequest::where('user_id', Auth::id()) // Authenticated user
                ->where('status', 'accepted') // Only accepted requests
                ->latest() // Get the most recent request
                ->firstOrFail();

            // Check if the request status is 'accepted'
            if ($discussionRequest->status === 'accepted') {

                // Match the user_id with the user_id in the UserProblem table
                $userProblem = UserProblem::where('user_id', $discussionRequest->user_id)
                    ->latest() // Get the latest service related to the user
                    ->firstOrFail();

                $serviceId = $userProblem->service_id; // Get the service_id from UserProblem

                // Fetch the fee from the service_fees table
                $serviceFee = ServiceFee::firstOrFail(); // Assuming there is only one row or use a condition to filter

                // Create a new transaction with the accepted engineer and service
                $transaction = Transaction::create([
                    'user_id' => $discussionRequest->user_id, // User who made the request
                    'engineer_id' => $discussionRequest->engineer_id, // Engineer from discussion request
                    'service_id' => $serviceId, // The latest service the user is using
                    'amount' => $serviceFee->fee, // Fee fetched from the service_fees table
                    'status' => 'pending', // Default status is pending
                ]);

                // Return response indicating successful transaction creation
                return $this->ok('Transaction created successfully', $transaction);
            } else {
                // If the request is not accepted
                return $this->ok('Discussion request not accepted yet.');
            }
        }catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
