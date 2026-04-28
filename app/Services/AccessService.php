<?php

namespace App\Services;

use App\Models\Access;
use App\Models\AccessLog;
use App\Models\OtpToken;
use App\Models\User;
use App\Notifications\AccessOtpRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccessService
{
    public const REVEAL_WINDOW_SECONDS = 90;
    public const OTP_VALIDITY_MINUTES = 5;

    /**
     * Generate a random 6-digit OTP, hash it, and store the token.
     * Send the plain code via email.
     */
    public function requestRevealOtp(Access $access, User $user, Request $request, string $reason): OtpToken
    {
        $code = (string) random_int(100000, 999999);

        $token = OtpToken::create([
            'user_id' => $user->id,
            'access_id' => $access->id,
            'purpose' => 'access.reveal',
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_VALIDITY_MINUTES),
            'ip_address' => $request->ip(),
        ]);

        $this->logAction($access, $user, 'reveal_request', $request, [
            'reason' => $reason,
        ]);

        $user->notify(new AccessOtpRequested($code, $access));

        return $token;
    }

    /**
     * Validate the OTP code provided by the user.
     * Marks the token as used and returns true if valid.
     */
    public function validateOtp(Access $access, User $user, string $providedCode, Request $request): bool
    {
        $token = OtpToken::where('user_id', $user->id)
            ->where('access_id', $access->id)
            ->where('purpose', 'access.reveal')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()->first();

        if (! $token) {
            $this->logAction($access, $user, 'failed_otp', $request, ['reason' => 'No active token']);
            return false;
        }

        if (! Hash::check($providedCode, $token->code_hash)) {
            $this->logAction($access, $user, 'failed_otp', $request, ['reason' => 'Invalid code']);
            return false;
        }

        $token->update(['used_at' => now()]);

        // Set a session-based reveal window (server-side only)
        session([
            'access.reveal.'.$access->id => [
                'expires_at' => now()->addSeconds(self::REVEAL_WINDOW_SECONDS)->timestamp,
                'token_id' => $token->id,
            ],
        ]);

        return true;
    }

    /**
     * Returns true if the current session has a valid (non-expired) reveal window for this access.
     */
    public function hasActiveRevealWindow(Access $access): bool
    {
        $key = 'access.reveal.'.$access->id;
        $data = session($key);
        if (! $data || ! isset($data['expires_at'])) {
            return false;
        }
        if ($data['expires_at'] < now()->timestamp) {
            session()->forget($key);
            return false;
        }
        return true;
    }

    public function revealWindowSecondsLeft(Access $access): int
    {
        $key = 'access.reveal.'.$access->id;
        $data = session($key);
        if (! $data || ! isset($data['expires_at'])) {
            return 0;
        }
        return max(0, $data['expires_at'] - now()->timestamp);
    }

    /**
     * Reveal the password — only callable when there's an active reveal window.
     * Returns the plaintext or null if window expired.
     * Logs the actual reveal as separate action.
     */
    public function reveal(Access $access, User $user, Request $request, string $field = 'password'): ?string
    {
        if (! $this->hasActiveRevealWindow($access)) {
            return null;
        }

        $value = match ($field) {
            'password' => $access->password,
            'username' => $access->username,
            'notes' => $access->notes,
            default => null,
        };

        if ($value !== null) {
            $this->logAction($access, $user, 'reveal', $request, ['field' => $field]);
        }

        return $value;
    }

    public function rotatePassword(Access $access, string $newPassword, User $user, Request $request): void
    {
        $access->update([
            'password' => $newPassword,
            'last_rotated_at' => now(),
        ]);
        $this->logAction($access, $user, 'rotate', $request);
    }

    public function logAction(Access $access, User $user, string $action, ?Request $request, array $extras = []): AccessLog
    {
        return AccessLog::create([
            'user_id' => $user->id,
            'access_id' => $access->id,
            'action' => $action,
            'field' => $extras['field'] ?? null,
            'reason' => $extras['reason'] ?? null,
            'ip_address' => $request?->ip(),
            'user_agent' => substr((string) $request?->userAgent(), 0, 500),
        ]);
    }

    /**
     * Generate a strong random password.
     */
    public function generatePassword(int $length = 20): string
    {
        $alphabet = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $password;
    }
}
