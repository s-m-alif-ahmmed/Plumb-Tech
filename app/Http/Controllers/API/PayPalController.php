<?php

namespace App\Http\Controllers\API;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\DiscussionRequest;
use App\Models\Payment;
use App\Services\PaypalPaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Joaopaulolndev\FilamentGeneralSettings\Models\GeneralSetting;
use Throwable;

class PayPalController extends Controller
{
    use ApiResponse;

    /**
     * @throws Throwable
     */
    public function createPayment(Request $request, PaypalPaymentService $paypalService)
    {
       $request->validate([
           'discussion_request_id' => 'required|exists:discussion_requests,id',
       ]);

        try {
            DB::beginTransaction();
            $discussionRequest = DiscussionRequest::with(['payment'])->find($request->discussion_request_id);
            if (!$discussionRequest) {
                return $this->ok('Discussion request not found');
            }

            if (!$discussionRequest->engineer_id) {
                return $this->error('No engineer has accepted this request.', 403);
            }

            if ($discussionRequest->status !== Status::PENDING) {
                return $this->error('This discussion already completed or cancelled', 400);
            }

            if ($discussionRequest->payment && $discussionRequest->payment->status === Status::COMPLETED) {
                return $this->error('Already paid', 400);
            }

            if ($discussionRequest->payment && $discussionRequest->payment->status !== Status::PENDING) {
                return $this->error('Somethings went worng!', 400);
            }

            $settings = GeneralSetting::first();

            if ($settings && $settings->application_fee_percent) {
                $applicationFee = ($settings->application_fee_percent / 100) * $discussionRequest->price;
            }else{
                $applicationFee = 0;
            }


            if ($discussionRequest->payment){
                if ($discussionRequest->payment->created_at->diffInHours(now()) < 2){
                    $orderCreate = $paypalService->showDetails($discussionRequest->payment->payment_id);
                }
                $payment = $discussionRequest->payment;
            } else{
                $payment = Payment::create([
                    'user_id' => auth()->user()->id,
                    'discussion_request_id' => $discussionRequest->id,
                    'amount' => $discussionRequest->price,
                    'status' => Status::PENDING,
                    'application_fee' => $applicationFee,
                    'engineer_id' => $discussionRequest->engineer_id,
                    'currency_code' => 'USD',
                ]);
            }

            if (!isset($orderCreate) || $orderCreate['status'] !== 'CREATED') {
                $orderCreate = $paypalService->createOrder($payment);
            }

            if (isset($orderCreate['id']) && $orderCreate['id'] != null) {
                $payment->update([
                    'payment_id' => $orderCreate['id'],
                    'created_at' => now()
                ]);
                foreach ($orderCreate['links'] as $links) {
                    if ($links['rel'] == 'approve') {
                        DB::commit();
                        return $this->ok('Payment order created successfully!', [
                            'payment_url' => $links['href'],
                        ]);
                    }
                }
            }
            DB::rollBack();
            return $this->error('Something went wrong!');
        }catch (Throwable $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }


    /**
     * @throws Throwable
     */
    public function webhook(PaypalPaymentService $paypalService, Request $request)
    {
        try {
            $paypalService->handleWebhook($request);
            return $this->ok('Webhook successful!');
        }catch (Throwable $e) {
            Log::error($e->getMessage());
            return $this->error($e->getMessage(), 500);
        }

    }


    public function returnUrl(Request $request, PaypalPaymentService $paypalService)
    {
        try {
            $paymentId = $request->get('paymentId');
            $response = $paypalService->captureOrder($request->token);
            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                Payment::where('id', $paymentId)->update([
                    'status' => Status::COMPLETED,
                    'transaction_id' => $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? '',
                    'updated_at' => now(),
                ]);
            }
        return $this->ok('Payment returned successfully!');
        }catch (Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }

    }

    public function cancelUrl()
    {
        return $this->error('Payment cancelled!');
    }
}
