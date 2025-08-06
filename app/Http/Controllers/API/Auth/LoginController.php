<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ProfileImage;
use App\Models\Skill;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    use ApiResponse;
    public function login(Request $request)
    {
         $request->validate([
             'email' => 'required|string|email',
             'password' => 'required|string',
         ]);
         if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])){
             return Helper::jsonErrorResponse('The provided credentials do not match our records.',401,[
                 'email' => 'The provided credentials do not match our records.'
             ]);
         }

        if (Auth::user()->email_verified_at === null){
          return $this->error('Email not verified.',403);
         }

         $user = Auth::user();
         if ($user->role == 'admin'){
             return $this->error('You are not authorized to access this page.', 401);
         }
         return response()->json([
             'status' => true,
             'message' => 'Login Successful',
             'token_type' => 'Bearer',
             'token' => $user->createToken('AuthToken')->plainTextToken,
             'data' => $user->load(['portfolios','skills'])
         ]);
    }

    public function logout(Request $request)
    {
        try {
            // Revoke the current user’s token
            $request->user()->currentAccessToken()->delete();
            // Return a response indicating the user was logged out
            return $this->ok('Logged out successfully.');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage(),500);
        }
    }

    public function logoutAll(Request $request)
    {
        try {
            // Revoke the current user’s token
            $request->user()->tokens()->delete();
            // Return a response indicating the user was logged out
            return $this->ok('All devices have been successfully logged out.');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage(),500);
        }
    }

    public function user()
    {
        return $this->ok('User Details fetch successfully.',Auth::user());
    }

    public function profile_update(Request $request)
    {
        $user = \auth()->user();
        if ($user->role === 'customer'){
            $validatedData =   $request->validate([
                'first_name' => 'sometimes|required|string|max:100',
                'last_name' => 'sometimes|required|string|max:100',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'address' => 'sometimes|required|string|max:100',
            ]);
        }else{
            $validatedData =   $request->validate([
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:100',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'service' => 'sometimes|required|string|max:100',
                'skills' => 'sometimes|required|array',
                'skills.*' => 'sometimes|required|exists:skills,id',
                'about' => 'sometimes|required|string|max:500',
                'level' => 'sometimes|string|in:beginner,intermediate,advanced',
                'portfolio' => 'sometimes|nullable|array',
                'portfolio.*' => 'sometimes|required|image|mimes:jpeg,jpg,png|max:10240',
            ]);
        }

        $user->load(['skills','portfolios']);
        try {
            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                if ($user->avatar && file_exists(public_path($user->avatar))){
                    Helper::fileDelete(public_path($user->avatar));
                }
                $avatar = Helper::fileUpload($request->file('avatar'), 'user/avatar',getFileName($request->file('avatar')));
            }else{
                $avatar = $user->avatar;
            }
            unset($validatedData['avatar']);
            $validatedData['avatar'] = $avatar;
            //modify validated data

            if (isset($validatedData['skills'])) {
                $user->skills()->sync($validatedData['skills']);
            }


            //update portfolio
            foreach ($validatedData['portfolio'] ?? [] as $portfolio){
                $user->portfolios()->create(['image' => Helper::fileUpload($portfolio, 'user/portfolio', getFileName($portfolio))]);
            }
            unset($validatedData['portfolio']);

            // Update user details
            $user->update($validatedData);
            return $this->ok('Profile updated successfully.',$user);
        }catch (\Exception $exception){
            return $this->error($exception->getMessage(),500);
        }
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|confirmed',
        ]);
        $user = \auth()->user();
        if (!\Hash::check($request->current_password, $user->password)){
            return $this->error("Your current password does not matches with the password you provided.");
        }
        $user->password = bcrypt($request->password);
        $user->save();
        $user->tokens()->delete();
        return $this->ok('Password Changed successfully.');
    }

    public function profile()
    {
        $user = auth()->user()->load(['skills','portfolios']);
        if ($user->role === 'engineer'){
            $ratingStats = $user->reviews()
                ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as average_rating')
                ->first();

            $user->ratting = number_format($ratingStats->average_rating ?? 0, 1);
            $user->total_reviews = $ratingStats->total_reviews ?? 0;

        }
        return $this->ok('Profile fetch successfully.',$user);
    }

    public function delete_portfolio(string $id){
        $portfolioImage = \auth()->user()->portfolios()->find($id);
        if(!$portfolioImage){
            return $this->error("Portfolio image not found");
        }
        if($portfolioImage->image){
            Helper::fileDelete(public_path($portfolioImage->image));
        }
        $portfolioImage->delete();
        return $this->ok('Portfolio image deleted successfully.');
    }
}
