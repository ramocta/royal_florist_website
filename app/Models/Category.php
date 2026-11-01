<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id_ctg';
    public $timestamps = false;

    protected $fillable = ['category_name'];

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class, 'id_ctg');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'id_ctg');
    }
}
