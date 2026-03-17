<?php

namespace App\Models;

use Database\Factories\TestPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class TestPlan extends Model
{
    /** @use HasFactory<TestPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'created_by',
    ];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_test_plan')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scenarios(): HasMany
    {
        return $this->hasMany(TestScenario::class)->orderBy('sort_order');
    }

    public function testCases(): HasManyThrough
    {
        return $this->hasManyThrough(TestCase::class, TestScenario::class);
    }

    public function testRuns(): HasMany
    {
        return $this->hasMany(TestRun::class);
    }
}
