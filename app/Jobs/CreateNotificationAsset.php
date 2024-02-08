<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateNotificationAsset implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user, $shop;
    /**
     * Create a new job instance.
     */
    public function __construct($user, $shop) {
        $this->user = $user;
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $builder = $this->shop->notificationAsset();
        if($builder->doesntExist()) {
            $saleNotificationPopup = view('sale_notification_popup', [
                'discountCode' => '{{DISCOUNT_CODE}}', 
                'discountValue' => '{{DISCOUNT_VALUE}}', 
                'discountExpiry' => '{{DISCOUNT_EXPIRY}}'
            ])->render();

            $contactCapturePopup = view('contact_capture_popup', [
                'settings' => [
                    'title' => '{{TITLE}}',
                    'description' => '{{DESCRIPTION}}'
                ]
            ])->render();

            $builder->create([
                'sale_notif_html' => $saleNotificationPopup,
                'contact_capture_html' => $contactCapturePopup
            ]);
        }
    }
}
