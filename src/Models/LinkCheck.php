<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinkCheck extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'origin_resource',
        'edit_url',
        'url',
        'status_code',
        'status_text',
        'timeout',
        'batch_id',
    ];

    public static function latestBatch()
    {
        $batch = static::orderBy('created_at', 'DESC')->limit(1)->first();

        return $batch?->batch_id;
    }

    public function batchComplete()
    {
        return static::unscanned()->where('batch_id', $this->batch_id)->count() == 0;
    }

    public function batchStatus($batchId)
    {
        $total = LinkCheck::where('batch_id', $batchId)->count();
        $scanned = LinkCheck::where('batch_id', $batchId)->where('status_code', '!=', 0)->count();

        return ($scanned / $total) * 100;
    }

    public function scopeSuccess(Builder $query): void
    {
        $query->whereBetween('status_code', [200, 399]);
    }

    public function scopeFailed(Builder $query): void
    {
        $query->whereNotBetween('status_code', [200, 399]);
    }

    public function scopeUnscanned(Builder $query): void
    {
        $query->where('status_code', 0);
    }

    public static function failedCount($batchId)
    {
        return LinkCheck::query()->failed()->where('batch_id', $batchId)->count();
    }
}
