<?php

namespace App\Http\Controllers\API;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\DiscussionRequest;
use App\Models\Payment;
use App\Models\WithdrawalRequest;
use App\Traits\ApiResponse;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    use ApiResponse;

    public function balance()
    {
        $totalCompletedTaskCount = DiscussionRequest::where('status', Status::COMPLETED)
            ->whereHas('requestAcceptDenies', fn($query) => $query->byEngineer()->where('status', Status::ACCEPTED))
            ->count();

        $availableForWithDraw = auth()->user()->load('wallet')?->wallet?->balance ?? '0.00';

        $currentMonthEarning = DiscussionRequest::where('status', Status::COMPLETED)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('engineer_id', auth()->user()->id)
            ->sum('price');

        // Calculate average selling price for completed tasks
        $avgSellingPrice = DiscussionRequest::where('status', Status::COMPLETED)
            ->where('engineer_id', auth()->user()->id)
            ->avg('price') ?? '0.00';

        $paymentBeingCleared = WithdrawalRequest::where('status', Status::APPROVED)->where('user_id', auth()->user()->id)->sum('amount');

        return $this->ok('Balance details retrieved successfully', [
            'totalCompletedTaskCount' => (string)round($totalCompletedTaskCount,2),
            'availableForWithDraw' => (string)round($availableForWithDraw,2),
            'currentMonthEarning' => (string)round($currentMonthEarning,2),
            'avgSellingPrice' => (string)round($avgSellingPrice,2),
            'paymentBeingCleared' => (string)round($paymentBeingCleared,2),
        ]);
    }

    public function incomeHistory(Request $request)
    {
        $limit = $request->get('limit',10);
        $incomeHistory = Payment::where('engineer_id',\auth()->id())->whereHas('discussionRequest',function ($query){
            $query->where('status', Status::COMPLETED);
        })->select(['id','transaction_id','amount','user_id','status','created_at','application_fee'])->with('paymentBy')->latest()->paginate($limit);

        return  $this->pagination('Income histories', $incomeHistory);
    }
}
