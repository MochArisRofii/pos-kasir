<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prodsus extends Model
{

    protected $table = 'prodsuses';
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'barcode',
        'barcode_path',
        'status',
        'created_by',
        'processed_by',
        'processed_at',
        'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    // Relationship dengan user yang membuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship dengan user yang memproses
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scope untuk status
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Generate barcode
    public function generateBarcode()
    {
        return 'PRDS-' . str_pad($this->id, 6, '0', STR_PAD_LEFT) . '-' . time();
    }

    // Cek apakah stok tersedia
    public function hasStock($quantity = 1)
    {
        return $this->stock >= $quantity;
    }

    // Kurangi stok
    public function decreaseStock($quantity = 1)
    {
        $this->decrement('stock', $quantity);
    }

    // Tambah stok
    public function increaseStock($quantity = 1)
    {
        $this->increment('stock', $quantity);
    }
}
