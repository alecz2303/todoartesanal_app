<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'status',
        'payment_method',
        'total_cents',

        'name','phone','email',
        'delivery','address','notes',

        'mp_preference_id','mp_payment_id','mp_status',

        'transfer_proof_path',
        'transfer_submitted_at',
        'paid_at',
        'cancelled_at',
    ];

    protected $casts = [
        'total_cents' => 'integer',

        // Enums (Laravel soporta cast a enum por clase)
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,

        'transfer_submitted_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function markPaid(?string $mpPaymentId = null, ?string $mpStatus = null): void
    {
        $this->status = OrderStatus::Paid;
        $this->paid_at = now();

        if ($mpPaymentId) $this->mp_payment_id = $mpPaymentId;
        if ($mpStatus) $this->mp_status = $mpStatus;

        $this->save();
    }

    public function markFailed(?string $mpStatus = null): void
    {
        $this->status = OrderStatus::Failed;
        if ($mpStatus) $this->mp_status = $mpStatus;
        $this->save();
    }

    public function markCancelled(): void
    {
        $this->status = OrderStatus::Cancelled;
        $this->cancelled_at = now();
        $this->save();
    }

    public function recalcTotal(): void
    {
        $this->total_cents = $this->items()
            ->get()
            ->sum(fn ($i) => $i->price_cents_snapshot * $i->qty);

        $this->save();
    }
}