<?php

namespace App\Console\Commands;

use App\Jobs\RunSegment;
use App\Models\SegmentRule;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use App\Traits\SegmentTrait;
use Illuminate\Console\Command;

class RunShopSegments extends Command
{
    use FunctionTrait, RequestTrait, SegmentTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-shop-segments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $shopSegments = SegmentRule::get();
            if($shopSegments !== null && $shopSegments->count() > 0) {
                foreach($shopSegments as $shopSegment) {
                    RunSegment::dispatch($shopSegment)->onConnection('sync');
                }
            }
            $this->info('Done');
        } catch (\Throwable $th) {
            $this->info($th->getMessage().' '.$th->getLine());
        }
    }
}
