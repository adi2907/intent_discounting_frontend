<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Jobs\CreateShopDiscountCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Carbon\Carbon;

class DiscountCodeGenerationTest extends TestCase
{
    use DatabaseTransactions;

    protected $demoShop;

    public function setUp(): void
    {
        parent::setUp();
        // Set up any necessary data or state here
        $this->demoShop = Shop::find(17);

        if (!$this->demoShop) {
            $this->fail('Demo shop with ID 17 does not exist.');
        }
    }

    /** @test */
    public function it_generates_discount_code_every_x_hours()
    {
        // Fake the queue to intercept job dispatching
        Queue::fake();

        // Dispatch the job
        CreateShopDiscountCode::dispatch($this->demoShop);

        // Manually execute the job
        $job = new CreateShopDiscountCode($this->demoShop);
        $job->handle();

        // Assert that a discount code was created
        $firstDiscountCode = $this->demoShop->getDiscountCode()->latest()->first();
        $this->assertNotNull($firstDiscountCode, 'The first discount code should be created.');

        // Simulate passing time by 2 hours
        Carbon::setTestNow(Carbon::now()->addHours(2));

        // Dispatch the job again
        CreateShopDiscountCode::dispatch($this->demoShop);

        // Manually execute the job again
        $job->handle();

        // Assert that a new discount code was created
        $secondDiscountCode = $this->demoShop->getDiscountCode()->latest()->first();
        $this->assertNotNull($secondDiscountCode, 'The second discount code should be created.');
        $this->assertNotEquals($firstDiscountCode->code, $secondDiscountCode->code, 'The second discount code should be different from the first one.');

        // Ensure both discount codes exist in the database
        $this->assertDatabaseHas('discount_codes', [
            'shop_id' => $this->demoShop->id,
            'code' => $firstDiscountCode->code,
        ]);
        $this->assertDatabaseHas('discount_codes', [
            'shop_id' => $this->demoShop->id,
            'code' => $secondDiscountCode->code,
        ]);
    }


    // /** @test */
    // public function it_stores_discount_code_in_database()
    // {
    //     Queue::fake();
        
    //     CreateShopDiscountCode::dispatch($this->demoShop);

    //     // You need to call the handle method directly to actually execute the job for testing purposes
    //     $job = new CreateShopDiscountCode($this->demoShop);
    //     $job->handle();
        
    //     // Assert the discount code was stored in the database
    //     $this->assertDatabaseHas('discount_codes', [
    //         'shop_id' => $this->demoShop->id,
    //         // Add other necessary fields here
    //     ]);
    // }

    // /** @test */
    // public function it_sets_discount_code_validity_to_2x_hours()
    // {
    //     Queue::fake();
        
    //     CreateShopDiscountCode::dispatch($this->demoShop);

    //     $job = new CreateShopDiscountCode($this->demoShop);
    //     $job->handle();
        
    //     $discountCode = $this->demoShop->getDiscountCode()->latest()->first();
    //     $this->assertNotNull($discountCode);
    //     $this->assertEquals(time() + (2 * 60 * 60), $discountCode->validity);
    // }

    // /** @test */
    // public function it_handles_overlapping_validity()
    // {
    //     Queue::fake();
        
    //     CreateShopDiscountCode::dispatch($this->demoShop);
        
    //     Carbon::setTestNow(Carbon::now()->addHours(1));
        
    //     CreateShopDiscountCode::dispatch($this->demoShop);

    //     $job = new CreateShopDiscountCode($this->demoShop);
    //     $job->handle();
        
    //     $discountCodes = $this->demoShop->getDiscountCode()->get();
    //     $this->assertCount(2, $discountCodes);

    //     $firstCode = $discountCodes->first();
    //     $secondCode = $discountCodes->last();
        
    //     $this->assertTrue($firstCode->validity > time());
    //     $this->assertTrue($secondCode->validity > time());
    // }

    // /** @test */
    // public function it_deactivates_code_after_2x_hours()
    // {
    //     Queue::fake();
        
    //     CreateShopDiscountCode::dispatch($this->demoShop);
        
    //     Carbon::setTestNow(Carbon::now()->addHours(4));
        
    //     CreateShopDiscountCode::dispatch($this->demoShop);

    //     $job = new CreateShopDiscountCode($this->demoShop);
    //     $job->handle();
        
    //     $discountCode = $this->demoShop->getDiscountCode()->latest()->first();
    //     $this->assertNull($discountCode); // Assuming the code gets deleted after 2X hours
    // }

    // /** @test */
    // public function it_applies_discount_code_only_to_specific_shop()
    // {
    //     // Create another shop for testing
    //     $anotherShop = Shop::factory()->create([
    //         'shop_url' => 'another-shop.myshopify.com',
    //         'access_token' => 'another_token',
    //     ]);
        
    //     Queue::fake();
        
    //     CreateShopDiscountCode::dispatch($this->demoShop);

    //     $job = new CreateShopDiscountCode($this->demoShop);
    //     $job->handle();
        
    //     $discountCode = $this->demoShop->getDiscountCode()->latest()->first();
    //     $this->assertNotNull($discountCode);
        
    //     // Assuming you have a method to check applicability
    //     $this->assertTrue($this->isDiscountCodeApplicableToShop($discountCode, $this->demoShop));
    //     $this->assertFalse($this->isDiscountCodeApplicableToShop($discountCode, $anotherShop));
    // }

    // private function isDiscountCodeApplicableToShop($discountCode, $shop)
    // {
    //     // Add logic to check if the discount code is applicable to the given shop
    //     return $discountCode->shop_id === $shop->id;
    // }
}
