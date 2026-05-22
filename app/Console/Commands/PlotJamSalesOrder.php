<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use DateInterval;
use DatePeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class PlotJamSalesOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plot:jam-sales-order {bookid} {monthyear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Plot Jam Sales Order';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //

        $bookid = $this->argument('bookid');
        Session::put('book_journal_id', $bookid);
        $monthyear = $this->argument('monthyear');
        $startDate = createCarbon($monthyear . '-01')->startOfMonth();
        $endDate = createCarbon($monthyear . '-01')->endOfMonth();

        $period = new DatePeriod(
            $startDate,
            new DateInterval('P1D'), // interval 1 hari
            $endDate // supaya tanggal terakhir ikut
        );
        foreach ($period as $date) {
            $this->info('Plotting tanggal ' . $date->format('Y-m-d'));
            $countMax = SalesOrder::where('created_at', '>=', $date->format('Y-m-d 00:00:00'))->where('created_at', '<=', $date->format('Y-m-d 23:59:59'))->count();
            $countPerjam = round($countMax / 12);
            $this->info('count max ' . $countMax . ', count per jam ' . $countPerjam);
            $salesOrders = SalesOrder::where('created_at', '>=', $date->format('Y-m-d 00:00:00'))->where('created_at', '<=', $date->format('Y-m-d 23:59:59'))->orderBy('created_at')->get();
            $lastJam = -1;
            $iMinutes = 0;
            $randMinutes = [];
            foreach ($salesOrders as $index => $so) {
                $jamke = floor($index / $countPerjam);
                if ($jamke >= 10) {
                    $jamke += 1;
                }
                if ($lastJam != $jamke) {
                    $randMinutes = [];
                    for ($i = 0; $i < $countPerjam; $i++) {
                        $randMinutes[] = rand(0, 59);
                    }
                    $randMinutes = collect($randMinutes)->sort()->values()->all();
                    $iMinutes = 0;
                }
                $lastJam = $jamke;
                $jam = 8 + $jamke;
                $menit = $randMinutes[$iMinutes];
                $iMinutes++;
                $newCreatedAt = createCarbon($date->format('Y-m-d') . ' ' . str_pad($jam, 2, '0', STR_PAD_LEFT) . ':' . str_pad($menit, 2, '0', STR_PAD_LEFT) . ':00');
                $so->created_at = $newCreatedAt;
                $so->save();
                $this->info('update sales '.$so->sales_order_number.' to jam '.$jam.':'.$menit);

                foreach ($so->details as $detail) {
                    $detail->created_at = $newCreatedAt;
                    $detail->save();
                }
            }
        }
        return [
            'status' => 1,
            'msg' => $salesOrders
        ];
    }
}
