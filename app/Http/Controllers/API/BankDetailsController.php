<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankDetails;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankDetailsController extends Controller
{
    use ApiResponse;

    /**
     * Show all bank details for logged-in user.
     */
    public function index()
    {
        try {
            $bankDetails = Auth::user()->bankDetails;
            return $this->ok('Bank details retrieved successfully.',$bankDetails);
        } catch (\Exception $e) {
           return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Store a new bank detail.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50|unique:bank_details',
                'account_holder_name' => 'required|string|max:255',
                'branch_name' => 'nullable|string|max:255',
                'swift_code' => 'nullable|string|max:50',
                'ifsc_code' => 'nullable|string|max:50',
                'is_default' => 'boolean',
            ]);

            $validatedData['user_id'] = Auth::id();
            $validatedData['is_default'] = false;

            $bankDetail = BankDetails::create($validatedData);

            return $this->ok('Bank detail added successfully!', $bankDetail);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
