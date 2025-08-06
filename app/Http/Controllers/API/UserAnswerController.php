<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\UserAnswer;
use App\Models\UserAnswerImage;
use App\Models\UserProblem;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class UserAnswerController extends Controller
{
    use ApiResponse;

    public function storeUserAnswer(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'question_id' => 'required|array',
            'question_id.*' => 'required|exists:questions,id',
            'answer_id' => 'required|array',
            'answer_id.*' => 'required|exists:answers,id',
            'description' => 'nullable|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        // Check if the number of question_ids matches the number of answer_ids
        if (count($validated['question_id']) !== count($validated['answer_id'])) {
            return $this->error('Each question must have its corresponding answer.');
        }

        // Save User Problem
        $userProblem = UserProblem::create([
            'user_id' => auth()->id(),
            'service_id' => $validated['service_id'],
            'description' => $validated['description'] ?? null,
        ]);

        // Save answers for each question
        foreach ($validated['question_id'] as $index => $question_id) {
            // Ensure each answer_id is associated with the correct question_id
            foreach ($validated['answer_id'][$index] as $answer_id) {
                UserAnswer::create([
                    'user_problem_id' => $userProblem->id,
                    'question_id' => $question_id,
                    'answer_id' => $answer_id,
                ]);
            }
        }

        // Save the images if provided (Alifs code)
        // if ($request->hasFile('images')) {
        //     foreach ($request->file('images') as $image) {
        //         $path = Helper::fileUpload($image, 'user_answers', $userProblem->id);

        //         UserAnswerImage::create([
        //             'user_problem_id' => $userProblem->id,
        //             'image' => $path,
        //         ]);
        //     }
        // }

        // Save the images if provided (iqus code)
        if ($request->hasFile('images')) {
            $uploadedImages = []; // Duplicate Check

            foreach ($request->file('images') as $image) {
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME); // Original name without extension
                $extension = $image->getClientOriginalExtension(); // File extension
                $fileName = time() . '_' . $originalName . '.' . $extension; // Final name with timestamp and original name

                // Check if this image is already uploaded
                if (!in_array($fileName, $uploadedImages)) {
                    // Upload with custom file name
                    $path = Helper::fileUpload($image, 'user_answers', $fileName);

                    UserAnswerImage::create([
                        'user_problem_id' => $userProblem->id,
                        'image' => $path,
                    ]);

                    $uploadedImages[] = $fileName; // Save the file name to prevent duplicates
                }
            }
        }






        // **💡 Load all related data before returning the response**
        $userProblem->load([
            'images',
            'userAnswer.question',
            'userAnswer.answer',
            'service:id,title',
        ]);

        return $this->ok('Answer saved successfully!', $userProblem);
    }
}
