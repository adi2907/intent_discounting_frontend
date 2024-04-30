<?php

namespace App\Console\Commands;

use App\Jobs\RunSegment;
use App\Models\SegmentRule;
use Illuminate\Console\Command;

class RunSegmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-segment-command';

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
        $rows = SegmentRule::get();
        foreach($rows as $row) {
            RunSegment::dispatch($row)->onConnection('sync');
        }
    }
}
