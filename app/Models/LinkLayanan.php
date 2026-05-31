<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkLayanan extends Model
{
    protected $table = 'link_layanan';

    protected $guarded = ['id'];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }
}
