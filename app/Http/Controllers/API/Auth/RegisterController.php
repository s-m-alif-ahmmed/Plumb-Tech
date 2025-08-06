<?php

namespace App\Http\Controllers\API\Auth;


use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Skill;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Hash;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Joaopaulolndev\FilamentGeneralSettings\Services\MailSettingsService;


class RegisterController extends Controller
{
    use ApiResponse;
    public function register(RegisterRequest $request){
        DB::beginTransaction();
        try {
            if ($request->role === 'customer') {
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'address' => $request->address,
                ]);
            } else {
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'role' => $request->role,
                    'service' => $request->service,
                    'about' => $request->about,
                    'address' => $request->address,
                ]);

                // manage skills
                $skills = $request->skills ?? [];
                $user->skills()->attach($skills);

                // manage portfolio
                foreach ($request->portfolio as $portfolio) {
                    $path = Helper::fileUpload($portfolio, 'user/portfolio', getFileName($portfolio));
                    $user->portfolios()->create([
                        'image' => $path,
                    ]);
                }
            }

            DB::commit();
            return $this->success('Register successfully', ['otp' => $this->send_otp($user)->token], 201);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage(), 500);
        }
    }



    /**
     * @throws Exception
     */
    public function send_otp(User $user, $mailType = 'verify')
    {
        $otp  = (new Otp)->generate($user->email, 'numeric', 4, 60);
        $message = $mailType === 'verify' ? 'Verify Your Email Address' : 'Reset Your Password';
        \Mail::to($user->email)->send(new \App\Mail\OTP($otp->token,$user,$message,$mailType));
        return $otp;
    }

    public function resend_otp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();
            if($user){
               $otp = $this->send_otp($user);
                return $this->success('OTP send successfully.',['otp' => $otp->token],201);
            }else{
                return $this->error('Email not found',404);
            }
        }catch (Exception $exception){
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function verify_email(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'otp' => 'required|string|digits:4',
        ]);
        try {
            $user = User::where('email', $request->email)->first();
            if(!$user){
                return $this->error('Email not found',404);
            }

            if($user->email_verified_at !== null){
                return $this->error('Email already verified',404);
            }

            $verify = (new Otp)->validate($request->email, $request->otp);
            if($verify->status){
                $user->email_verified_at = now();
                $user->save();
                return $this->ok('Email verified successfully',[
                        'token' => $user->createToken('auth_token')->plainTextToken,
                        'token_type' => 'Bearer',
                        'user' => $user->load(['skills', 'portfolios']),
                    ]);
            }else{
                return $this->error($verify->message,404);
            }
        }catch (Exception $exception){
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function forgot_password(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        try {
            $user = User::where('email', $request->email)->first();
            if(!$user){
                return $this->error('Email not found',404);
            }
            $otp = $this->send_otp($user,'forget');
            return $this->success('OTP send successfully.',['otp' => $otp->token],201);
        }catch (Exception $exception){
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function verify_otp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'otp' => 'required|string|digits:4',
        ]);

        $verify = (new Otp)->validate($request->email, $request->otp);
        if($verify->status){
            $user = User::where('email', $request->email)->first();
            if(!$user){
                return Helper::jsonErrorResponse('Email not found',404);
            }

            $token = Str::random(60);
            DB::table('password_reset_tokens')->updateOrInsert([
                'email' => $user->email,
            ],['token' => $token, 'created_at' => Carbon::now()]);

            $user->save();
            return $this->success('Email verified successfully',[
                'token' => $token,
            ],201);
        }else{
          return $this->error($verify->message,404);
        }
    }

    public function reset_password(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed',
        ]);

        try {
            $user = User::where('email', $request->email)->latest()->first();
            if(!$user){
                return $this->error('Email not found',404);
            }
            $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

            if (!$record || $record->token !== $request->token) {
                return response()->json(['message' => 'Invalid token'], 400);
            }
            $expirationTime = Carbon::parse($record->created_at)->addMinutes(60);
            if (Carbon::now()->greaterThan($expirationTime)) {
                return response()->json(['message' => 'Token has expired'], 400);
            }
            $user->password = bcrypt($request->password);
            $user->save();
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->ok('Password reset successfully');
        }catch (Exception $exception){
            return $this->error($exception->getMessage(),404);
        }
    }
}
