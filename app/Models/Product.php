<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Product Model
 * Represents the product_master_list table
 */
class Product extends Model
{
    protected $fillable = [
        'product_id',
        'types',
        'brand',
        'model',
        'capacity',
        'quantity',
    ];
}
