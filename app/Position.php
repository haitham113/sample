<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['name', 'target_commission_monthly', 'target_commission_quarterly', 'target_commission_yearly', 'target_sales_monthly', 'target_sales_quarterly', 'target_sales_yearly', 'created_at', 'updated_at'];
    
}
