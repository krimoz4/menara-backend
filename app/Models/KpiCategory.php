<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiCategory extends Model
{
    protected $fillable = ['name', 'icon'];

    public function kpis()
    {
        return $this->hasMany(Kpi::class);
    }
}
