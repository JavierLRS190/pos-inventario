<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['name', 'location', 'description', 'active'];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}