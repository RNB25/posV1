<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    protected $fillable = ['sale_id', 'product_id', 'product_name', 'price', 'qty', 'subtotal'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
