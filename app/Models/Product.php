<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'barcode',
        'category_id',
        'plu',
    ];

    // Auto-generate PLU ketika membuat product baru
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->plu)) {
                $product->plu = static::generatePLU();
            }
        });
    }

    // Method untuk generate PLU unik
    public static function generatePLU()
    {
        do {
            $plu = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('plu', $plu)->exists());

        return $plu;
    }

    public function category()
    {
        return $this->belongsTo(Catergory::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
