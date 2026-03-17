<?php

namespace App\Models;

use Database\Factories\TestCaseResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestCaseResult extends Model
{
    /** @use HasFactory<TestCaseResultFactory> */
    use HasFactory;

    protected $fillable = [
        'test_run_id',
        'test_case_id',
        'status',
        'actual_result',
        'notes',
        'attachments',
        'duration_seconds',
        'executed_by',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'executed_at' => 'datetime',
        ];
    }

    public function testRun(): BelongsTo
    {
        return $this->belongsTo(TestRun::class);
    }

    public function testCase(): BelongsTo
    {
        return $this->belongsTo(TestCase::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class);
    }
}
