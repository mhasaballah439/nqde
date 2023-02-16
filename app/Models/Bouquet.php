<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bouquet extends Model
{
    use HasFactory;

    public function getReportNameAttribute()
    {
        switch ($this->report_id) {
            case 1:
                return 'تقارير محدودة';
        }
    }
    public function getAppNameAttribute()
    {
        switch ($this->report_id) {
            case 1:
                return 'تطبيقات محدودة';
            case 2:
                return 'جميع التطبيقات';
        }
    }
}
