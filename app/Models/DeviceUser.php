<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeviceUser extends Model
{
    protected $fillable = [
        'employee_code', 'full_name', 'email', 'mailbox',
        'print_code', 'location_id', 'user_id', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        // We don't fully hide it from serialization; the masking helper below is what UI uses.
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function printers(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'device_user_asset')
            ->withPivot('granted_at', 'granted_by')
            ->withTimestamps();
    }

    /**
     * Returns the print code masked for display when the viewer is not the
     * owner / admin / IT. Example: 1234 → ****1234, 12345 → *****2345
     */
    public function maskedPrintCode(): string
    {
        $code = (string) $this->print_code;
        if ($code === '') {
            return '—';
        }
        $visible = min(4, max(1, intdiv(strlen($code), 2)));
        $hidden = str_repeat('*', max(0, strlen($code) - $visible));
        return $hidden.substr($code, -$visible);
    }

    /**
     * Authoritative check: can $viewer see the unmasked code of this device user?
     */
    public function isVisibleTo(?User $viewer): bool
    {
        if (! $viewer) {
            return false;
        }
        if ($viewer->isAdmin() || $viewer->hasRole(['it', 'auditor'])) {
            return true;
        }
        return $this->user_id === $viewer->id;
    }
}
