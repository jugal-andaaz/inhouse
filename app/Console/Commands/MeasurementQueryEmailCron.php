<?php
namespace Vanguard\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Vanguard\Mail\MeasurementQueryNotification;
use Vanguard\Models\AndaazInhouseOrderItem;
use Vanguard\Models\MeasurementQueryEmail;
use Carbon\Carbon;

class MeasurementQueryEmailCron extends Command
{
    protected $signature   = 'measurement:query-email-cron';
    protected $description = 'Fetch menswear items from andaaz_inhouse_new and store in measurement_query_email';

    private const MEN_PRODUCT_TYPES = [9389, 9396, 9406, 9397, 9407, 10162, 10164];

    public function handle(): int
    {
        $this->info('Measurement Query Email Cron Started: ' . Carbon::now());
        $lastId = MeasurementQueryEmail::max('order_inhouse_newid') ?? 0;
        $this->info("Resuming after andaaz_inhouse_new id: {$lastId}");

        $inserted = 0; 

        AndaazInhouseOrderItem::whereIn('product_type', self::MEN_PRODUCT_TYPES)
            ->where('id', '>', $lastId)
            ->where(function ($q) {
                $q->whereNull('mmtid')->orWhere('mmtid', '');
            })   
            ->orderBy('id', 'asc')
            ->chunk(500, function ($items) use (&$inserted) {
                foreach ($items as $item) {
                    $existing = MeasurementQueryEmail::where('order_id', $item->order_id)->first();

                    if (!$existing) {
                        MeasurementQueryEmail::create([
                            'order_id'            => $item->order_id,
                            'order_inhouse_newid' => $item->id,
                            'product_item_id'     => $item->product_item_id,
                            'product_type'        => $item->product_type,
                            'product_sku'         => $item->product_sku,
                            'email_sent'          => 1,
                            'email_by'            => '',
                            'created_at'          => Carbon::now(),
                        ]);
                        $inserted++;
                        $this->info("Inserted: order_id={$item->order_id} inhouse_id={$item->id}");
                        $allItems = AndaazInhouseOrderItem::where('order_id', $item->order_id)
                            ->whereIn('product_type', self::MEN_PRODUCT_TYPES)
                            ->get();
                        $isFatherSon = $allItems->contains(fn($i) => (int) $i->product_type === 9396); 

                        try {
                            $mail = new MeasurementQueryNotification($item->order_id, $allItems, $isFatherSon);
                            if (!$mail->shouldSend) {
                                $this->info("Email skipped (DIY item) for order_id={$item->order_id}");
                            } else {
                                Mail::send($mail);                                
                                MeasurementQueryEmail::where('order_id', $item->order_id)
                                    ->update(['email_to' => $mail->customerEmail,
                                             'email_by' => 'service@andaazfashion.com',]);

                                $this->info("Email sent for order_id={$item->order_id} ({$allItems->count()} item(s))");
                            }
                        } catch (\Exception $e) {
                            $this->error("Email failed for order_id={$item->order_id}: {$e->getMessage()}");
                        }
                    } else {
                        MeasurementQueryEmail::where('order_id', $item->order_id)
                            ->update(['order_inhouse_newid' => $item->id]);
                        $this->info("Skipped duplicate order_id={$item->order_id}, cursor advanced to id={$item->id}");
                    }
                }
            });
        $this->info("Done. {$inserted} new record(s) inserted.");
        $this->info('Measurement Query Email Cron Completed: ' . Carbon::now());
        return 0;
    }
}