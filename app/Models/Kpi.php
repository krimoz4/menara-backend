<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    protected $fillable = [
        'kpi_category_id',
        'name',
        'description',
        'unit',
        'target_value',
        'is_higher_better',
    ];

    public function category()
    {
        return $this->belongsTo(KpiCategory::class, 'kpi_category_id');
    }

    public function records()
    {
        return $this->hasMany(KpiRecord::class);
    }
}
