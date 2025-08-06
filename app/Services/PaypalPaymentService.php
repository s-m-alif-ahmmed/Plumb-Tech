<?php

namespace App\Services;

use App\Enums\Status;
use Exception;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;
use App\Models\Payment; // Assuming you have a Payment model

class PaypalPaymentService
{
    private PayPalClient $paypalClient;

    /**
     * Create a new class instance.
     * @throws Exception
     * @throws Throwable
     */
    public function __construct()
    {
        $this->paypalClient = new PayPalClient;
        $this->paypalClient->setApiCredentials(config('paypal'));
        $this->paypalClient->getAccessToken();
    }

    /**
     * Create a PayPal order
     * @param Payment $payment
     * @return array|string
     * @throws Throwable
     */
    public function createOrder(Payment $payment): array|string
    {
        return $this->paypalClient->createOrder([
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => route('paypal.return',['paymentId' => $payment->id]),
                'cancel_url' => route('paypal.cancel'),
            ],
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => $payment->amount,
                    ],
                    'custom_id' => $payment->id,
                    'description' => 'Payment ID: ' . $payment->id,
                ]
            ]
        ]);
    }

    public function client()
    {
        return $this->paypalClient;
    }

    /**
     * Capture a PayPal order payment
     * @param string $orderId
     * @return array|string
     * @throws Throwable
     */
    public function captureOrder(string $orderId): array|string
    {
        return $this->paypalClient->capturePaymentOrder($orderId);
    }

    /**
     * @throws Throwable
     */
    public function showDetails(string $orderId): array|string
    {
        return $this->paypalClient->showOrderDetails($orderId);
    }

    /**
     * @throws Throwable
     */
    public function reAuthorizeOrder(string $orderId, $amount): array|string
    {
        return $this->paypalClient->reAuthorizeAuthorizedPayment($orderId,$amount);
    }

    /**
     * Handle PayPal webhook events
     * @param Request $request
     * @return void
     * @throws Exception
     * @throws Throwable
     */
    public function handleWebhook(Request $request): void
    {
        $payload = $request->all();

        if (!$this->verifyWebhookSignature($request)) {
            throw new Exception('Invalid webhook signature');
        }

        switch ($payload['event_type']) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->handlePaymentCompleted($payload);
                break;

            case 'PAYMENT.CAPTURE.DENIED':
                $this->handlePaymentDenied($payload);
                break;

            case 'PAYMENT.CAPTURE.REFUNDED':
                $this->handlePaymentRefunded($payload);
                break;
        }
    }

    /**
     * Extract payment ID from payload
     * @param array $payload
     * @return string|null
     */
    private function extractPaymentId(array $payload): ?string
    {
        return $payload['resource']['purchase_units'][0]['custom_id'] ?? null;
    }

    /**
     * Verify webhook signature
     * @param Request $request
     * @return bool
     * @throws Throwable
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        try {
            $webhookId = config('paypal.sandbox.webhook_id');

            return $this->paypalClient->verifyWebhook(
                $request->header('PAYPAL-AUTH-ALGO'),
                $request->header('PAYPAL-CERT-URL'),
                $request->header('PAYPAL-TRANSMISSION-ID'),
                $request->header('PAYPAL-TRANSMISSION-SIG'),
                $request->header('PAYPAL-TRANSMISSION-TIME'),
                $webhookId,
                $request->getContent()
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Handle completed payment webhook
     * @param array $payload
     * @return void
     */
    private function handlePaymentCompleted(array $payload): void
    {
        $paymentId = $this->extractPaymentId($payload);
        if ($paymentId) {
            Payment::where('id', $paymentId)->update([
                'status' => Status::COMPLETED,
                'transaction_id' => $payload['resource']['id'],
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Handle denied payment webhook
     * @param array $payload
     * @return void
     */
    private function handlePaymentDenied(array $payload): void
    {
        $paymentId = $this->extractPaymentId($payload);
        if ($paymentId) {
            Payment::where('id', $paymentId)->delete();
        }
    }

    /**
     * Handle pending payment webhook
     * @param array $payload
     * @return void
     */
    private function handlePaymentRefunded(array $payload): void
    {
        $paymentId = $this->extractPaymentId($payload);
        if ($paymentId) {
            Payment::where('id', $paymentId)->update([
                'status' => 'refunded',
            ]);
        }
    }

    /**
     * @throws Throwable
     */
    public function refund($paymentId,$transactionId,$amount,$note): \Psr\Http\Message\StreamInterface|array|string
    {
        return $this->paypalClient->refundCapturedPayment($paymentId,$transactionId,$amount,$note);
    }

}
