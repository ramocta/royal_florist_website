<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $primaryKey = 'id_ctg';
    public $timestamps = false;

    protected $fillable = ['category_name'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_ctg', 'id_ctg');
    }
}
