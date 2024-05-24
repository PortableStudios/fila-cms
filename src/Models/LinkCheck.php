<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'timeout',
        'batch_id',
    ];

    public function latestBatch()
    {
        $batch = (new LinkCheck)->orderBy('created_at', 'DESC')->limit(1)->first();

        return optional($batch)->batch_id;
    }

    public function batchStatus($batchId)
    {
        $total = (new LinkCheck)->where('batch_id', $batchId)->count();
        $scanned = (new LinkCheck)
            ->where('batch_id', $batchId)
            ->where('status_code', '!=', 0)
            ->count();
        
        return ($scanned / $total) * 100;
    }
}
