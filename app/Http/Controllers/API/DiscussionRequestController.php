<?php

namespace App\Http\Controllers\API;

use App\Enums\Status;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Jobs\SendDiscussionRequest;
use App\Models\Answer;
use App\Models\DiscussionRequest;
use App\Models\Service;
use App\Models\ServiceFee;
use App\Models\User;
use App\Models\UserProblem;
use App\Notifications\NewDiscussionRequestNotification;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiscussionRequestController extends Controller
{
    use ApiResponse;

    /*  public function sendRequest(Request $request)
    {
        try {
            // Validate the skills
            $validated = $request->validate([
                'skills' => 'required|array',
                'skills.*' => 'integer|exists:skills,id',
            ]);

            $skills = $validated['skills'];

            // Get engineers based on matching skills and role
            $engineers = User::whereHas('skills', function ($query) use ($skills) {
                $query->whereIn('skills.id', $skills);
            })
                ->where('role', 'engineer')
                ->get();

            if ($engineers->isEmpty()) {
                return $this->error('No engineers found for these skills', 404);
            }

            // Delete expired discussion requests (older than 2 minutes)
            DiscussionRequest::where('status', 'pending')
                ->where('created_at', '<=', Carbon::now()->subMinutes(2)) // 2 minutes||subMinutes || subSeconds(120)
                ->delete();


            // Check if the user already has a pending request
            $existingRequest = DiscussionRequest::where('user_id', auth()->id())
                ->where('status', 'pending')
                ->exists();

            if ($existingRequest) {
                // Prevent sending a new request
                return $this->ok('You already have a pending request');
            }


            // Create the discussion request with null engineer_id initially
            $discussionRequest = DiscussionRequest::create([
                'user_id' => auth()->id(),
                'engineer_id' => null,
                'status' => 'pending',
                'created_at' => now(),  // Set creation time
            ]);

            // Retrieve the user problem and answers
            $userProblem = UserProblem::where('user_id', auth()->id())->latest()->first();

            if (!$userProblem) {
                return $this->error('User problem not found', 404);
            }

            // If null, then send an empty collection
            $userAnswers = $userProblem->userAnswer ?? collect();
            $images = $userProblem->images ?? collect(); // Same for images

            // Prepare the data for notification
            $userData = [
                'description' => $userProblem->description,
                'answers' => $userAnswers->map(function ($answer) {
                    return [
                        'question' => $answer->question->question_text,
                        'answer' => $answer->answer->answer_text,
                    ];
                }),
                'images' => $images->pluck('image'),
            ];

            // Prepare additional user details
            $userName = auth()->user()->name;
            $userDescription = $userProblem->description;

            // Send notification to engineers
            foreach ($engineers as $engineer) {
                $engineer->notify(new NewDiscussionRequestNotification([
                    'discussion_request_id' => $discussionRequest->id,
                    'eng_id' => $engineer->id,
                    'subject' => 'New Discussion Request',
                    'message' => 'A new discussion request is available for you!',
                    'user_id' => auth()->id(),
                    'user_name' => $userName,
                    'avatar' => auth()->user()->avatar,
                    'user_description' => $userDescription,
                    'questions_and_answers' => $userData['answers'],
                    'images' => $userData['images'],
                ]));
            }

            return $this->ok('Request sent Successfully!', $discussionRequest);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    } */

    public function sendRequest(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'skills' => 'required|array',
                'skills.*' => 'integer|exists:skills,id',
                'service_id' => 'required|integer|exists:services,id',
                'answer' => 'required|array',
                'answer.*' => 'required|integer|exists:answers,id',
                'images' => 'nullable|array',
                'description' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();
            // Retrieve service details
            $service = Service::find($validated['service_id']);
            $serviceTitle = $service->title;
            $serviceFeeValue = $service->price;
            // Search engineers by skills
            $engineers = User::whereHas('skills', function ($query) use ($validated) {
                $query->whereIn('skills.id', $validated['skills']);
            })->where('role', 'engineer')->get();

            if ($engineers->isEmpty()) {
                return $this->error('No engineers found for these skills', 404);
            }

            $questionsAnswer = Answer::with(['question'])->whereIn('id', $validated['answer'])->get()->map(function ($answer) {
                return [
                    'question'  => $answer->question->question_text,
                    'answer' => $answer->answer_text
                ];
            });

            $images = $request->has('images') ? collect($validated['images'])
                ->filter()
                ->map(function ($file) {
                    if ($file->isValid()) {
                        return Helper::fileUpload($file, 'discussion_requests/images', getFileName($file));
                    }
                    return null;
                })
                ->filter()
                ->all()
                : null;

            // Store discussion request
            $discussionRequest = DiscussionRequest::create([
                'user_id' => auth()->id(),
                'engineer_id' => null,
                'service_id' => $validated['service_id'],
                'service_title' => $serviceTitle,
                'price' => $serviceFeeValue,
                'status' => 'pending',
                'question_answer' => $questionsAnswer,
                'images' => $images,
                'description' => $validated['description'] ?? null,
                'created_at' => now(),
            ]);
            $user = auth()->user();
            DB::commit();
            // Send notification to engineers
            SendDiscussionRequest::dispatch(
                'New Service Request Received!',
                "You have a new {$serviceTitle} request from {$user->name}. Review the details and respond promptly to provide assistance. Tap to view the request.",
                $discussionRequest->id,
                $engineers
            );

            return $this->ok('Request sent Successfully!', $discussionRequest);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function engineerProfile(string $engineerId)
    {
        try {
            $request =  DiscussionRequest::find($engineerId);
            if (!$request) {
                return $this->error('Request not found', 404);
            }

            if (!$request->engineer_id) {
                return $this->error('No engineer has accepted this request.', 403);
            }

            $engineer = User::with(['skills','portfolios'])->find($request->engineer_id);
            if (!$engineer) {
                return $this->error('Engineer not found! for this request', 404);
            }

            $service = Service::select(['id', 'title', 'thumbnail', 'price'])->find($request->service_id);

            if (!$service) {
                return $this->error('Service not found! for this request', 404);
            }

            $ratingStats = $engineer->reviews()
                ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as average_rating')
                ->first();
            $engineer->ratting = number_format($ratingStats->average_rating ?? 0, 1);
            $engineer->total_reviews = $ratingStats->total_reviews ?? 0;

            return $this->ok('Profile fetch successfully.', [
                'engineer' => $engineer,
                'service' => $service,
            ]);
        }catch (\Exception $exception){
            return $this->error($exception->getMessage(), 500);
        }

    }
}
