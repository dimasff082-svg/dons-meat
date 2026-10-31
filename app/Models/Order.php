<?php

namespace App\Models;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'order_code',
    'public_tracking_token',
    'channel',
    'created_by_user_id',
    'customer_name',
    'customer_phone',
    'customer_email',
    'fulfillment_method',
    'payment_method',
    'delivery_zone_id',
    'delivery_address',
    'requested_fulfillment_at',
    'subtotal',
    'delivery_fee',
    'total_amount',
    'order_status',
    'payment_status',
    'customer_note',
    'internal_note',
    'payment_deadline_at',
    'paid_at',
    'expired_at',
    'completed_at',
])]
class Order extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'channel' => OrderChannel::class,
            'fulfillment_method' => FulfillmentMethod::class,
            'order_status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'requested_fulfillment_at' => 'datetime',
            'payment_deadline_at' => 'datetime',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('id');
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function isCod(): bool
    {
        return $this->payment_method === 'cod';
    }

    public function isMidtrans(): bool
    {
        return $this->payment_method === 'midtrans' || empty($this->payment_method);
    }

    public function isPendingPayment(): bool
    {
        return $this->payment_status === PaymentStatus::Unpaid
            && $this->order_status === OrderStatus::PendingPayment;
    }

    public function isExpired(): bool
    {
        return $this->payment_status === PaymentStatus::Expired
            || $this->order_status === OrderStatus::Expired;
    }

    public function canBeCancelled(): bool
    {
        // PRD: pesanan yang sudah berstatus dibayar tidak dapat dibatalkan melalui alur normal sistem
        return ! $this->isPaid() && ! $this->isExpired() && $this->order_status !== OrderStatus::Cancelled;
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        if ($this->order_status === $newStatus) {
            return false;
        }

        return match ($this->order_status) {
            OrderStatus::PendingPayment => in_array($newStatus, [OrderStatus::Processing, OrderStatus::Expired, OrderStatus::Cancelled], true),
            OrderStatus::Processing => $this->fulfillment_method === FulfillmentMethod::Pickup
                ? in_array($newStatus, [OrderStatus::ReadyForPickup, OrderStatus::Completed], true)
                : in_array($newStatus, [OrderStatus::OnDelivery, OrderStatus::Completed], true),
            OrderStatus::ReadyForPickup => $newStatus === OrderStatus::Completed,
            OrderStatus::OnDelivery => $newStatus === OrderStatus::Completed,
            default => false,
        };
    }

    public function scopePendingPayment(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::Unpaid)
            ->where('order_status', OrderStatus::PendingPayment);
    }
}
