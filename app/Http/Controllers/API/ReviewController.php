<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class ReviewController extends Controller
{
    use ApiResponse;

    // Add or update a review for a user
    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string',
            ]);

            // If the user has already given a review, then update it
            $review = Review::updateOrCreate(
                [
                    'reviewer_id' => Auth::id(),
                    'user_id' => $request->user_id,
                ],
                [
                    'rating' => $request->rating,
                    'review' => $request->review,
                ]
            );

            return $this->ok('Review submitted successfully', $review);
        } catch (\Exception $exception){
            return $this->error($exception->getMessage(),500);
        }
    }

    // Show all reviews of a user
    public function index()
    {
        try {
            $user_id = auth()->id(); // Authenticated user ID
    
            // Load reviews given by the authenticated user and include the reviewer's information
            $reviews = Review::where('user_id', $user_id)
            // Reviewer's information
                ->with('reviewer:id,first_name,last_name,avatar') 
                ->latest()
                ->get();
    
            return $this->ok('Your reviews retrieved successfully', $reviews);
        }catch (\Exception $exception){
            return $this->error($exception->getMessage(),500);
        }
    }
    
    // Get user's average rating
    public function averageRating()
    {
        try {
            $userId = auth()->id();
            $reviewsCount = Review::where('user_id', $userId)->count();
            $averageRating = Review::where('user_id', $userId)->avg('rating');

            return $this->ok('Average rating retrieved successfully', ['average_rating' => round($averageRating, 1), 'reviews_count' => $reviewsCount]);
        } catch (\Exception $exception){
            return $this->error($exception->getMessage(),500);
        }
    }
}
