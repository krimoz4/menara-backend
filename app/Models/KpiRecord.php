<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiRecord extends Model
{
    protected $fillable = ['kpi_id', 'department_id', 'recorded_value', 'recorded_date', 'notes'];

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
