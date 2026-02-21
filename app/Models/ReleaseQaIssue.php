<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ReleaseQaIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'release_qa_run_id',
        'website_id',
        'category',
        'severity',
        'title',
        'details',
        'url',
    ];

    /**
     * @return BelongsTo<ReleaseQaRun, $this>
     */
    public function run(): BelongsTo
    {
        return $this->belongsTo(ReleaseQaRun::class, 'release_qa_run_id');
    }

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
