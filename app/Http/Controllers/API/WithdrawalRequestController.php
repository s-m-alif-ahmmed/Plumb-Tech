<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalRequestController extends Controller
{
    use ApiResponse;


    /**
     * Display a listing of withdrawal requests.
     */
    public function index()
    {
        $withdrawalRequests = WithdrawalRequest::where('user_id', Auth::id())->get()->load('user', 'bankDetails');
        return $this->ok('Withdrawal requests', $withdrawalRequests);
    }

    /**
     * Store a new withdrawal request.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'bank_details_id' => 'nullable|exists:bank_details,id',
                'amount' => 'required|numeric|min:10',
                'rejection_reason' => 'nullable|string',
            ]);

            DB::beginTransaction();
            $validatedData['user_id'] = Auth::id();
            // Get the wallet for the authenticated user
            $wallet = Wallet::where('user_id', Auth::id())->first();
            // Check if the wallet exists and if the user has sufficient balance
            if (!$wallet || $wallet->balance < $validatedData['amount']) {
                return $this->ok('Insufficient balance for withdrawal.');
            }
            // Create the withdrawal request with pending status
            $validatedData['status'] = 'pending'; // Set status as 'pending'
            $withdrawalRequest = WithdrawalRequest::create($validatedData);
            Auth::user()->wallet()->decrement('balance', $validatedData['amount']);
            DB::commit();

            // Return the response with the created withdrawal request
            return $this->ok('Withdrawal request created successfully.', $withdrawalRequest->load('user', 'bankDetails'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }
}
