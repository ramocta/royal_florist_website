<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $table = 'subcategories';
    protected $primaryKey = 'id_subctg';
    public $timestamps = false;

    protected $fillable = ['id_ctg', 'subcategory_name', 'pakai_stok'];

    protected $casts = ['pakai_stok' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_ctg');
    }
}
