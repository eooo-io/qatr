<?php

namespace App\Models;

use Database\Factories\TestCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TestCase extends Model
{
    /** @use HasFactory<TestCaseFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'steps',
        'expected_result',
        'priority',
        'type',
        'automation_status',
        'automation_framework',
        'automation_script_path',
        'sort_order',
        'test_scenario_id',
    ];

    protected function casts(): array
    {
        return [
            'steps' => 'array',
        ];
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class, 'test_scenario_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'test_case_tag');
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'test_case_dependencies', 'test_case_id', 'depends_on_id');
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'test_case_dependencies', 'depends_on_id', 'test_case_id');
    }
}
