<?php

namespace App\Jobs;

use App\Enums\Status;
use App\Models\DiscussionRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ServiceSessionEnd implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public DiscussionRequest $discussionRequest;
    public function __construct(DiscussionRequest $discussionRequest)
    {
        $this->discussionRequest = $discussionRequest;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $serviceAmount = $this->discussionRequest->price;
        $engineer = $this->discussionRequest->engineer;
        if ($engineer) {
            if ($engineer->wallet){
                $engineer->wallet->increment('balance', $serviceAmount - $this->discussionRequest?->payment?->application_fee ?? 0);
            }else{
                $engineer->wallet()->create([
                    'balance' => $serviceAmount,
                ]);
            }
        }
        $this->discussionRequest->update([
            'status' => Status::COMPLETED
        ]);
    }
}
