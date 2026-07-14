<?php

namespace Vanguard\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Vanguard\Models\AndaazInhouseOrderItem;
use Vanguard\Models\ShipmentTracking;

class FedExTrackingUpdate extends Mailable
{
    use SerializesModels;

    public bool $isDelivered;
    public ?AndaazInhouseOrderItem $orderItem;
    public \Illuminate\Support\Collection $orderItems;

    public function __construct(
        public ShipmentTracking $tracking,
        public int $deliveredCount = 1,
        public int $totalItems = 1,
    ) {
        $this->isDelivered = strtoupper($tracking->status ?? '') === 'DL'
            || strtolower($tracking->status_description ?? '') === 'delivered';

        // For partial delivery show only delivered items; for full delivery show all shipment items
        if ($tracking->order_id) {
            $isFullyDelivered = $deliveredCount >= $totalItems;

            $shipmentQuery = ShipmentTracking::fedex()
                ->where('order_id', $tracking->order_id)
                ->whereNotNull('unique_id');

            // When partial: restrict to only the DL records so undelivered items are hidden
            if (!$isFullyDelivered) {
                $shipmentQuery->whereIn('status', ['DL', 'DELIVERED']);
            }

            $shipmentRecords = $shipmentQuery->get(['unique_id', 'product_sku']);

            $productItemIds = $shipmentRecords
                ->pluck('unique_id')
                ->map(fn($uid) => preg_replace('/^ANDFS_/i', '', $uid))
                ->filter()
                ->values();

            if ($productItemIds->isNotEmpty()) {
                $this->orderItems = AndaazInhouseOrderItem::where('order_id', $tracking->order_id)
                    ->whereIn('product_item_id', $productItemIds)
                    ->get();
            } else {
                // Fallback: match by product_sku from shipment records
                $skus = $shipmentRecords->pluck('product_sku')->filter()->values();
                $this->orderItems = $skus->isNotEmpty()
                    ? AndaazInhouseOrderItem::where('order_id', $tracking->order_id)
                        ->whereIn('product_sku', $skus)
                        ->get()
                    : AndaazInhouseOrderItem::where('order_id', $tracking->order_id)->get();
            }
        } else {
            $this->orderItems = collect();
        }

        // Primary item — used for the customer greeting name
        $productItemId = preg_replace('/^ANDFS_/i', '', $tracking->unique_id ?? '');
        $this->orderItem = null;

        if ($productItemId !== '') {
            $this->orderItem = $this->orderItems->firstWhere('product_item_id', $productItemId);
        }

        if (!$this->orderItem && $tracking->product_sku) {
            $this->orderItem = $this->orderItems->firstWhere('product_sku', $tracking->product_sku);
        }

        if (!$this->orderItem) {
            $this->orderItem = $this->orderItems->first();
        }
    }

    public function build(): self
    {
        return $this->subject($this->resolveSubject())
                    ->view('emails.epspl-tracking-update');
    }

    protected function resolveSubject(): string
    {
        $orderId = $this->tracking->order_id;

        if (!$this->isDelivered) {
            return "Shipment Update for Order {$orderId}";
        }

        if ($this->totalItems === 1) {
            return "Your Order Has Been Delivered — {$orderId}";
        }

        if ($this->deliveredCount >= $this->totalItems) {
            return "Your Order Has Been Fully Delivered ✅ — {$orderId}";
        }

        if ($this->deliveredCount === 1) {
            return "Your Order Has Been Partially Delivered — {$orderId}";
        }

        return "Another Item from Your Order Has Been Delivered — {$orderId}";
    }
}
