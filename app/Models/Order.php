<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    protected $fillable = ['customer_id', 'status', 'total', 'qris_string', 'delivery_location', 'qris_proof_path'];

    protected $appends = ['qris_proof_url'];

    protected $casts = [
        'total' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function getQrisProofUrlAttribute(): ?string
    {
        return $this->qris_proof_path ? Storage::url($this->qris_proof_path) : null;
    }
}
