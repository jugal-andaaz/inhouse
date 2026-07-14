<?php

namespace Vanguard\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Vanguard\Models\Order;
use Vanguard\Models\OrderMsrmtAppsheetSQL;

class MeasurementQueryNotification extends Mailable
{
    use SerializesModels;

    public string     $customerFirstName;
    public string     $customerEmail;
    public ?string    $chestSize;
    public ?string    $height;
    public ?string    $sleeveEstimate;
    public ?string    $occasion;
    public ?string    $productDesign;
    public Collection $heightSleevePairs;
    public bool       $shouldSend  = true;
    public bool       $isFatherSon = false;

    public function __construct(
        public string $orderId,
        public Collection $items,
        bool $isFatherSon = false,
    ) {
        $this->isFatherSon = $isFatherSon;

        /*if ($items->contains(fn($item) => strtoupper(trim($item->product_sku ?? '')) === 'DIY')) */
        if ($items->contains(fn($item) => strtoupper(trim($item->product_sku ?? '')) === 'DIY' && empty($item->mmtid))) 
        {
            $this->shouldSend = false;
            return;
        }   

        // Parse chest sizes and heights from ALL items
        $chestSizes = collect();
        $heights    = collect();

        foreach ($items as $item) {
            $sizeData = $this->parseSizeField($item->product_size ?? '');
            if ($sizeData['chest']) {
                $chestSizes->push($sizeData['chest']);
            }
            if ($sizeData['height']) {
                $heights->push($sizeData['height']);
            }
        }

        $chestSizes = $chestSizes->unique()->values();
        $heights    = $heights->unique()->values();

        // Fall back to OrderMsrmtAppsheetSQL if product_size fields are empty
        $msrmt = null;
        if ($chestSizes->isEmpty() || $heights->isEmpty()) {
            $msrmt = OrderMsrmtAppsheetSQL::where('increment_id', $orderId)->first();
        }

        $this->chestSize = $chestSizes->isNotEmpty()
            ? $chestSizes->join(' & ')
            : ($msrmt?->bust ?: null);

        if ($heights->isEmpty() && $msrmt?->height) {
            $heights = collect([$msrmt->height]);
        }

        $this->height          = $heights->first();
        $this->sleeveEstimate  = $this->estimateSleeve($this->height);
        $this->heightSleevePairs = $heights->map(fn($h) => [
            'height' => $h,
            'sleeve' => $this->estimateSleeve($h),
        ])->values();

        $this->occasion = $msrmt?->occation ?: null;

        // Customer name & email from andaaz_order
        $order = Order::where('increment_id', $orderId)->first();
        $this->customerEmail = $order?->customer_email ?: 'dhiren@andaazfashion.com';

        if (!$msrmt) {
            $msrmt = OrderMsrmtAppsheetSQL::where('increment_id', $orderId)->first();
        }
        $firstName = trim($order?->customer_firstname ?? $msrmt?->customer_firstname ?? '');
        $this->customerFirstName = $firstName ?: 'Customer';

        $this->productDesign = $items->first()?->product_name
                            ?? $items->first()?->product_sku
                            ?? null;
    }

    public function build(): self
    {
        return $this
            ->from('service@andaazfashion.com', 'Andaaz Fashion')
            ->to($this->customerEmail, $this->customerFirstName)
            /*->to('dhiren@andaazfashion.com', $this->customerFirstName)*/
            ->bcc('shivanindbhatt@gmail.com')
            ->subject("Measurement Query — Order {$this->orderId}")
            ->view('emails.measurement-query');
    }

    /**
     * Parse pipe-separated product_size string.
     * Returns ['chest' => '46', 'height' => '175 CM - 5 Feet 9 Inches', ...]
     */
    private function parseSizeField(string $size): array
    {
        $result = ['chest' => null, 'height' => null];
        $size   = trim($size, '"\'');

        foreach (explode('|', $size) as $part) {
            $part = trim($part);
            if (preg_match('/Body\s+Chest\s+Size\s*[:\-]\s*(.+)/i', $part, $m)) {
                $result['chest'] = trim($m[1]);
            } elseif (preg_match('/Body\s+Height\s*[:\-]\s*(.+)/i', $part, $m)) {
                $result['height'] = trim($m[1]);
            }
        }

        return $result;
    }

    /**
     * Estimate sleeve length from height string.
     * Handles: "175 CM - 5 Feet 9 Inches", "5.9", "5'9\"", "69" (total inches)
     */
    private function estimateSleeve(?string $height): ?string
    {
        if (empty($height)) return null;

        $h = trim($height);
        $totalInches = null;

        // "175 CM - 5 Feet 9 Inches" or "5 Feet 9 Inches"
        if (preg_match('/(\d+)\s*feet?\s*(\d+)\s*inch/i', $h, $m)) {
            $totalInches = (int)$m[1] * 12 + (int)$m[2];
        }
        // "5'9" or "5'9\""
        elseif (preg_match('/^(\d+)[\'′`](\d+)/', $h, $m)) {
            $totalInches = (int)$m[1] * 12 + (int)$m[2];
        }
        // "5.9" feet.inches decimal notation
        elseif (preg_match('/^(\d+)\.(\d{1,2})$/', $h, $m)) {
            $totalInches = (int)$m[1] * 12 + (int)$m[2];
        }
        // Pure CM e.g. "175 CM" or "175"
        elseif (preg_match('/^(\d{3})\s*cm/i', $h, $m) || (is_numeric($h) && $h > 100)) {
            $totalInches = (int) round((float)$h / 2.54);
        }
        // Pure inches e.g. "69"
        elseif (is_numeric($h) && $h > 20 && $h < 100) {
            $totalInches = (int)$h;
        }

        if (!$totalInches) return null;

        // Sleeve lookup table: [min_inches, max_inches, sleeve]
        // 5'0–5'1=60–61  5'2–5'3=62–63  5'4–5'5=64–65  5'6–5'7=66–67
        // 5'8–5'9=68–69  5'10–5'11=70–71 6'0–6'1=72–73  6'2–6'3=74–75
        // 6'4–6'5=76–77  6'6–6'7=78–79  6'8–6'9=80–81  6'10–6'11=82–83
        // Height (feet.inches) → sleeve lookup
        // 5.0–5.1 | 5.2–5.3 | 5.4–5.5 | 5.6–5.7 | 5.8–5.9 | 5.10–5.11
        // 6.0–6.1 | 6.2–6.3 | 6.4–6.5 | 6.6–6.7 | 6.8–6.9 | 6.10–6.11
        $table = [
            [60, 61, '22.5'],  // 5.0 & 5.1
            [62, 63, '23.5'],  // 5.2 & 5.3
            [64, 65, '24.5'],  // 5.4 & 5.5
            [66, 67, '25'],    // 5.6 & 5.7
            [68, 69, '26'],    // 5.8 & 5.9
            [70, 71, '26.5'],  // 5.10 & 5.11
            [72, 73, '27'],    // 6.0 & 6.1
            [74, 75, '27'],    // 6.2 & 6.3
            [76, 77, '27.5'],  // 6.4 & 6.5
            [78, 79, '28'],    // 6.6 & 6.7
            [80, 81, '28.5'],  // 6.8 & 6.9
            [82, 83, '29'],    // 6.10 & 6.11
        ];

        foreach ($table as [$min, $max, $sleeve]) {
            if ($totalInches >= $min && $totalInches <= $max) {
                return $sleeve;
            }
        }

        return null;
    }
}
