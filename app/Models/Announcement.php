<?php

namespace Pterodactyl\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property string $level
 * @property int $priority
 * @property bool $active
 * @property \Carbon\Carbon|null $starts_at
 * @property \Carbon\Carbon|null $ends_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Announcement extends Model
{
    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'announcement';

    /**
     * The table associated with the model.
     */
    protected $table = 'announcements';

    /**
     * Fields that are not mass assignable.
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Cast values to correct type.
     */
    protected $casts = [
        'priority' => 'integer',
        'active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Rules ensuring that the raw data stored in the database meets expectations.
     */
    public static array $validationRules = [
        'title' => 'required|string|max:191',
        'body' => 'required|string',
        'level' => 'required|string|in:info,success,warning,error',
        'priority' => 'integer|min:0',
        'active' => 'boolean',
        'starts_at' => 'nullable|date',
        'ends_at' => 'nullable|date',
    ];

    /**
     * Scope a query to only announcements that should currently be visible to
     * clients: active, and within their optional start/end window.
     */
    public function scopeVisible(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }
}
