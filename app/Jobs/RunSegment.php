<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunSegment implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use FunctionTrait, RequestTrait;
    public $row;
    /**
     * Create a new job instance.
     */
    public function __construct($row) {
        $this->row = $row;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $shop = Shop::where('id', $this->row->shop_id)->first();
        $data = $this->runSegment($shop, $this->row);
        $count = 0;
        if(isset($data['body']) && is_array($data['body']) && count($data['body']) > 0) {
            $count = count($data['body']);
        }
        $this->row->update(['no_of_users' => $count, 'users_measurement' => '']);
    }
}
