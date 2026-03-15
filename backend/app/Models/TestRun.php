<?php

namespace App\Models;

use Database\Factories\TestRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestRun extends Model
{
    /** @use HasFactory<TestRunFactory> */
    use HasFactory;

    protected $fillable = [
        'test_plan_id',
        'release_id',
        'executor_id',
        'status',
        'started_at',
        'completed_at',
        'environment',
    ];

    protected function casts(): array
    {
        return [
            'environment' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function testPlan(): BelongsTo
    {
        return $this->belongsTo(TestPlan::class);
    }

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(TestCaseResult::class);
    }
}
