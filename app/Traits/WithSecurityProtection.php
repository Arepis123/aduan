<?php

namespace App\Traits;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

trait WithSecurityProtection
{
    // Honeypot fields - these should remain empty
    public string $website = '';      // Fake field - bots fill this
    public string $honeypot_time = ''; // Timestamp when form loaded

    public function mountWithSecurityProtection(): void
    {
        $this->honeypot_time = encrypt(time());
    }

    protected function validateHoneypot(): void
    {
        // Check if bot filled the honeypot field
        if (!empty($this->website)) {
            // Log potential bot attempt
            logger()->warning('Honeypot triggered', [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            throw ValidationException::withMessages([
                'form' => 'Spam detected. Please try again.',
            ]);
        }

        // Check if form was submitted too quickly (less than 3 seconds)
        try {
            $loadTime = decrypt($this->honeypot_time);
            $timeDiff = time() - $loadTime;

            if ($timeDiff < 3) {
                logger()->warning('Form submitted too quickly', [
                    'ip' => request()->ip(),
                    'time_diff' => $timeDiff,
                ]);

                throw ValidationException::withMessages([
                    'form' => 'Please slow down and try again.',
                ]);
            }
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }
            // Invalid honeypot time - potential tampering
            throw ValidationException::withMessages([
                'form' => 'Invalid form submission. Please refresh and try again.',
            ]);
        }
    }

    protected function checkRateLimit(string $key, int $maxAttempts = 5, int $decayMinutes = 60): void
    {
        $rateLimitKey = $key . ':' . request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);

            throw ValidationException::withMessages([
                'form' => "Too many attempts. Please try again in {$minutes} minute(s).",
            ]);
        }

        RateLimiter::hit($rateLimitKey, $decayMinutes * 60);
    }

    protected function clearRateLimit(string $key): void
    {
        RateLimiter::clear($key . ':' . request()->ip());
    }
}
