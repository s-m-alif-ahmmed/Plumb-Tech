<?php

namespace App\Http\Controllers\API;


use App\Enums\Status;
use App\Events\AcceptRequest;
use App\Http\Controllers\Controller;
use App\Models\DiscussionRequest;
use App\Models\Payment;
use App\Models\RequestAcceptDenied;
use App\Models\Service;
use App\Models\Skill;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class EngineerController extends Controller
{
    use ApiResponse;


    public function index(Request $request)
    {
        $limit = $request->get('limit',10);
        $tasks = RequestAcceptDenied::byEngineer()->with('discussionRequest.user')->latest()->paginate($limit);
        return $this->pagination('Task list retrieved successfully',$tasks->map(fn($item) => $item->discussionRequest),$tasks);
    }

    public function getSkills()
    {
        $skills = Skill::select('id', 'name')->get();

        return $this->ok('Skills retrieved successfully', $skills);
    }


    public function getEngineerDetails($id)
    {
        try {
            $engineer = User::findOrFail($id);
            $reviews = $engineer->receivedReviews()->with('reviewer:id,first_name,last_name,service,about,avatar,address')->get();
            $averageRating = $reviews->avg('rating');
            $reviewCount = $reviews->count();

            return $this->ok('Engineer details retrieved successfully', [
                'engineer' => $engineer->load('skills', 'portfolios'),
                'average_rating' => round($averageRating, 1),
                'review_count' => $reviewCount,
                'reviews' => $reviews->map(function ($review) {
                    return [
                        'reviewer' => [
                            'id' => $review->reviewer->id,
                            'first_name' => $review->reviewer->first_name,
                            'last_name' => $review->reviewer->last_name,
                            'service' => $review->reviewer->service,
                            'about' => $review->reviewer->about,
                            'avatar' => $review->reviewer->avatar,
                            'address' => $review->reviewer->address,
                        ],
                        'rating' => $review->rating,
                        'review' => $review->review,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    public function acceptRequest(string $id){
        $request = DiscussionRequest::find($id);
        if (!$request) {
            return $this->error('Discussion Request not found', 404);
        }

        $acceptDeniesRequest = $request->requestAcceptDenies()->where('engineer_id',auth()->user()->id)->first();
//        if (!$acceptDeniesRequest) {
//            return  $this->error('Request access Denied', 403);
//        }
//
//        if ($request->status !== Status::PENDING && $request->engineer_id == auth()->user()->id) {
//            return $this->error('You have already accepted this request', 400);
//        }
//
//        if ($request->status !== Status::PENDING || $request->requestAcceptDenies()->where('status',Status::ACCEPTED)->exists()) {
//            return $this->error('This request already accepted by another engineer', 400);
//        }


        $request->update([
            'engineer_id' =>  auth()->user()->id,
        ]);
        $acceptDeniesRequest->update([
            'status' => Status::ACCEPTED,
        ]);

        $request->update([
            'status' => 'processing'
        ]);

        $engineer = auth()->user()->load(['skills','portfolios']);
        $service = Service::select(['id', 'title', 'thumbnail', 'price'])->find($request->service_id);
        $ratingStats = $engineer->reviews()
            ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as average_rating')
            ->first();
        $engineer->ratting = number_format($ratingStats->average_rating ?? 0, 1);
        $engineer->total_reviews = $ratingStats->total_reviews ?? 0;

        broadcast(new AcceptRequest([
            'engineer' => $engineer,
            'service' => $service,
            ],$request->id))->toOthers();

        return $this->ok('Request Accepted successfully',$request);

    }

    public function declineRequest(string $id)
    {
        $request = DiscussionRequest::find($id);
        if (!$request) {
            return $this->error('Discussion Request not found', 404);
        }

        $acceptDeniesRequest = $request->requestAcceptDenies()->where('engineer_id',auth()->user()->id)->first();
        if (!$acceptDeniesRequest) {
            return  $this->error('Request Accept Denied', 400);
        }

        if ($request->engineer_id == auth()->id() || $request->requestAcceptDenies()->where('status',Status::ACCEPTED)->where('engineer_id',auth()->id())->exists()) {
            return $this->error('You have already accepted this. You can no longer denied.', 400);
        }

        $acceptDeniesRequest->update([
            'status' => Status::DENINED,
        ]);

        return $this->ok('Request denied successfully',$request);
    }

    public function workingHistory(Request $request)
    {
        $limit = $request->get('limit',10);
        $workingHistory = DiscussionRequest::where('status',Status::COMPLETED)->select(['id','service_title','created_at'])->where('engineer_id',auth()->user()->id)->paginate($limit);
        return $this->pagination('Working History', $workingHistory);
    }

    public function workingHistoryDetails(string $id)
    {
        $workingHistoryDetails = DiscussionRequest::with(['user','service','engineer','payment'])->find($id);
        if (!$workingHistoryDetails) {
            return $this->error('Discussion Request not found', 404);
        }

        return $this->ok('Discussion Request working history details',$workingHistoryDetails);
    }

}
