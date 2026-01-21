<?php

namespace App\Services;

class MathCaptchaService
{
    /**
     * Generate a new math captcha question
     * Returns array with 'question' and encrypted 'answer'
     */
    public static function generate(): array
    {
        $operators = ['+', '-'];
        $operator = $operators[array_rand($operators)];

        if ($operator === '+') {
            $num1 = rand(1, 20);
            $num2 = rand(1, 20);
            $answer = $num1 + $num2;
        } else {
            // Ensure result is positive
            $num1 = rand(10, 30);
            $num2 = rand(1, $num1 - 1);
            $answer = $num1 - $num2;
        }

        $question = "{$num1} {$operator} {$num2}";

        return [
            'question' => $question,
            'hash' => self::hashAnswer($answer),
        ];
    }

    /**
     * Verify the captcha answer
     */
    public static function verify(string $hash, mixed $userAnswer): bool
    {
        if (empty($hash) || $userAnswer === null || $userAnswer === '') {
            return false;
        }

        // Clean up user input
        $userAnswer = trim((string) $userAnswer);

        // Must be numeric
        if (!is_numeric($userAnswer)) {
            return false;
        }

        return self::hashAnswer((int) $userAnswer) === $hash;
    }

    /**
     * Create a secure hash of the answer
     */
    private static function hashAnswer(int $answer): string
    {
        // Include app key for security and timestamp for expiry
        $timestamp = floor(time() / 300); // Valid for 5-minute windows
        $data = $answer . '|' . $timestamp . '|' . config('app.key');

        return hash('sha256', $data);
    }
}
