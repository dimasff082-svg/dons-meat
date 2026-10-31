<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $rawInput = trim((string) $this->input('email'));
        $resolvedEmail = match (strtolower($rawInput)) {
            'pemilik' => 'pemilik@donsmeat.local',
            'owner' => 'owner@donsmeat.local',
            'kasir' => 'kasir@donsmeat.local',
            'cashier' => 'cashier@donsmeat.local',
            'admin' => 'admin@donsmeat.local',
            default => str_contains($rawInput, '@') ? $rawInput : $rawInput . '@donsmeat.local',
        };

        $user = User::where('email', $resolvedEmail)->first();

        // Fallback for role aliases if user used another convention
        if (! $user && $resolvedEmail === 'pemilik@donsmeat.local') {
            $user = User::where('email', 'owner@donsmeat.local')->first();
            if ($user) {
                $resolvedEmail = 'owner@donsmeat.local';
            }
        } elseif (! $user && $resolvedEmail === 'owner@donsmeat.local') {
            $user = User::where('email', 'pemilik@donsmeat.local')->first();
            if ($user) {
                $resolvedEmail = 'pemilik@donsmeat.local';
            }
        } elseif (! $user && in_array($resolvedEmail, ['kasir@donsmeat.local', 'cashier@donsmeat.local'], true)) {
            $user = User::whereIn('email', ['kasir@donsmeat.local', 'cashier@donsmeat.local'])->first();
            if ($user) {
                $resolvedEmail = $user->email;
            }
        }

        if ($user && ! $user->isActive()) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi pemilik usaha.',
            ]);
        }

        if (! Auth::attempt(['email' => $resolvedEmail, 'password' => $this->input('password')], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
