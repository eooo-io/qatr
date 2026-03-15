<?php

namespace App\Models;

use Database\Factories\TestScenarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestScenario extends Model
{
    /** @use HasFactory<TestScenarioFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'preconditions',
        'sort_order',
        'test_plan_id',
    ];

    public function testPlan(): BelongsTo
    {
        return $this->belongsTo(TestPlan::class);
    }

    public function testCases(): HasMany
    {
        return $this->hasMany(TestCase::class)->orderBy('sort_order');
    }
}
