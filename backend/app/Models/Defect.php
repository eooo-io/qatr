<?php

namespace App\Models;

use Database\Factories\DefectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Defect extends Model
{
    /** @use HasFactory<DefectFactory> */
    use HasFactory;

    protected $fillable = [
        'test_case_result_id',
        'title',
        'description',
        'severity',
        'status',
        'external_tracker_url',
        'reported_by',
    ];

    public function result(): BelongsTo
    {
        return $this->belongsTo(TestCaseResult::class, 'test_case_result_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
