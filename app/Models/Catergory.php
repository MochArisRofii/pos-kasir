<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catergory extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description', 
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
