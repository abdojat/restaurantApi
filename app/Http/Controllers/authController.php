<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     * Body: name, email, password, password_confirmation
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:20', 'unique:users,phone_number'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone_number' => $data['phone_number'],
            'password' => Hash::make($data['password']),
            'role_id'  => \App\Models\Role::where('name', 'customer')->first()->id,
        ]);

        // Assign customer role
        $customerRole = \App\Models\Role::where('name', 'customer')->first();
        $user->roles()->attach($customerRole->id);

        // If you're using email verification, fire event and optionally send verification email
        event(new Registered($user));

        // Create Sanctum token for API use
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Registered successfully.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }
    // LOGIN (replace your method with this)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'        => ['required_without:phone_number', 'email', 'nullable'],
            'phone_number' => ['required_without:email', 'string', 'nullable'],
            'password'     => ['required', 'string'],
            'remember'     => ['sometimes', 'boolean'],
        ]);

        // Find user by email or phone_number
        $user = null;
        if (!empty($credentials['email'])) {
            $user = \App\Models\User::where('email', $credentials['email'])->first();
        } elseif (!empty($credentials['phone_number'])) {
            $user = \App\Models\User::where('phone_number', $credentials['phone_number'])->first();
        }

        if (!$user || !\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'credentials' => ['The provided credentials are incorrect.'],
            ]);
        }

        // If this request came through 'web' middleware, a session exists; otherwise it's an API call.
        if ($request->hasSession()) {
            \Illuminate\Support\Facades\Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    // LOGOUT (replace your method with this)
    public function logout(Request $request)
    {
        $request->validate([
            'all_devices' => ['sometimes', 'boolean'],
        ]);

        // Invalidate session only if it exists (web routes)
        if ($request->hasSession()) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Revoke Sanctum tokens (api routes)
        if ($request->user()) {
            if ($request->boolean('all_devices')) {
                $request->user()->tokens()->delete();
            } else {
                $request->user()->currentAccessToken()?->delete();
            }
        }

        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * POST /api/auth/change-locale
     * Body: locale
     */
    public function changeLocale(Request $request)
    {
        $request->validate([
            'locale' => ['required', 'string', 'in:en,ar'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->update(['preferred_locale' => $request->locale]);

        return response()->json([
            'message' => __('app.updated_successfully'),
            'locale' => $request->locale,
        ]);
    }

    /**
     * GET /api/auth/me
     * Auth: sanctum
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * PUT /api/auth/profile
     * Body: name (optional), email (optional, unique), avatar (optional file)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

       
        $data = $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email:rfc,dns', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['sometimes', 'string', 'max:20', 'unique:users,phone_number,' . $user->id],
            'avatar' => ['sometimes', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_path'] = $path;
        }

        // Remove avatar from data if it exists (since we're using avatar_path)
        unset($data['avatar']);

        // Update user data
        $user->fill($data);
        $saved = $user->save();
        
        return response()->json([
            'message' => 'Profile updated.',
            'user'    => $user->fresh(),
        ]);
    }

    /**
     * PUT /api/auth/phone
     * Body: phone_number
     */
    public function updatePhone(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'phone_number' => ['required', 'string', 'max:20', 'unique:users,phone_number,' . $user->id],
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Phone number updated successfully.',
            'user'    => $user->fresh(),
        ]);
    }

    /**
     * PUT /api/auth/password
     * Body: current_password, password, password_confirmation
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return response()->json(['message' => 'Password changed successfully.']);
    }

    /**
     * POST /api/auth/email/verification-notification
     * Sends email verification link (for unverified users).
     */
    public function sendEmailVerification(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent.']);
    }

    /**
     * GET /api/auth/email/verify?expires=...&signature=...
     * Usually handled by built-in routes. Expose if you need manual endpoint.
     */
    public function verifyEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        if ($request->user()->markEmailAsVerified()) {
            // Optionally fire event
            // event(new Verified($request->user()));
        }

        return response()->json(['message' => 'Email verified.']);
    }

    /**
     * POST /api/auth/password/forgot
     * Body: email
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    /**
     * POST /api/auth/password/reset
     * Body: token, email, password, password_confirmation
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    /**
     * OPTIONAL: Social login placeholders (Google, Facebook, etc.)
     * You can wire these with Laravel Socialite.
     */
    public function socialRedirect(string $provider)
    {
        // return Socialite::driver($provider)->stateless()->redirect();
        return response()->json(['message' => "Redirect to {$provider} not implemented in this stub."], 501);
    }

    public function socialCallback(string $provider)
    {
        // $socialUser = Socialite::driver($provider)->stateless()->user();
        // Find or create local user, then issue Sanctum token.
        return response()->json(['message' => "Callback for {$provider} not implemented in this stub."], 501);
    }
}
