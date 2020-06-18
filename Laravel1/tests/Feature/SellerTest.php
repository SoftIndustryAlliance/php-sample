<?php

namespace Tests\Feature;

use App\Classes\Constants;
use App\Models\Purchase;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $seller;
    private $boss;
    private $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:install');
        $this->seed('RolesSeeder');
        $this->seller = factory(User::class)->create(['role_id' => Constants::ROLES['Seller'], 'is_active' => 1]);
        $this->boss = factory(User::class)->create(['role_id' => Constants::ROLES['Boss']]);
        $this->client = factory(User::class)->create(['role_id' => Constants::ROLES['Client']]);
    }

    /**
     * Boss can get sellers list.
     *
     * @return void
     */
    public function testBossList()
    {
        factory(User::class, 5)->create(['role_id' => Constants::ROLES['Seller']]);

        Passport::actingAs(
            $this->boss,
            [Constants::ROLES_PASSPORT_SCOPE['Boss']]
        );

        $response = $this->get('/api/s/seller/list', ['Accept' => 'application/json']);

        $response
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Seller can't get sellers list.
     *
     * @return void
     */
    public function testSellerList()
    {
        factory(User::class, 5)->create(['role_id' => Constants::ROLES['Seller']]);

        Passport::actingAs(
            $this->seller,
            [Constants::ROLES_PASSPORT_SCOPE['Seller']]
        );

        $response = $this->get('/api/s/seller/list', ['Accept' => 'application/json']);

        $response
            ->assertStatus(403)
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Boss can get seller's statistics.
     *
     * @return void
     */
    public function testBossStatistics()
    {
        factory(Purchase::class, 5)->create(['seller_id' => $this->seller])->each(function (Purchase $purchase) {
            $purchase->created_at = $this->faker->dateTime();
            $purchase->save();
        });

        Passport::actingAs(
            $this->boss,
            [Constants::ROLES_PASSPORT_SCOPE['Boss']]
        );

        $response = $this->get(route('seller.statistics', $this->seller), ['Accept' => 'application/json']);

        $response
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Boss can get seller's full statistics.
     *
     * @return void
     */
    public function testBossFullStatistics()
    {
        $from = $this->faker->unixTime('-3 days');
        $middle = $this->faker->unixTime('-2 days');
        $to = $this->faker->unixTime('-1 days');

        factory(Purchase::class, 3)->create([
            'seller_id' => $this->seller,
            'created_at' => $middle
        ]);

        Passport::actingAs(
            $this->boss,
            [Constants::ROLES_PASSPORT_SCOPE['Boss']]
        );

        $response = $this->get(
            route('seller.date_statistics_full', [$this->seller, $from, $to]),
            ['Accept' => 'application/json']
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'client_id',
                    'date',
                    'free'
                ]
            ])
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Boss can get seller's statistics for a date.
     *
     * @return void
     */
    public function testBossDateStatistics()
    {
        $timestamp = $this->faker->unixTime('-1 days');

        factory(Purchase::class, 3)->create([
            'seller_id' => $this->seller,
            'created_at' => $timestamp
        ]);

        Passport::actingAs(
            $this->boss,
            [Constants::ROLES_PASSPORT_SCOPE['Boss']]
        );

        $response = $this->get(
            route('seller.date_statistics', [$this->seller, $timestamp]),
            ['Accept' => 'application/json']
        );

        $response
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Seller can add a cup for client.
     *
     * @return void
     */
    public function testSellerAdd()
    {
        Passport::actingAs(
            $this->seller,
            [Constants::ROLES_PASSPORT_SCOPE['Seller']]
        );

        $response = $this->post(
            route('seller.add_cup', ['clientSlug' => $this->client->slug()]),
            [],
            ['Accept' => 'application/json']
        );

        $response
            ->assertStatus(200)
            ->assertExactJson([
                'status' => 'added'
            ])
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Inactive seller can't add a cup for a client.
     *
     * @return void
     */
    public function testInactiveSellerAdd()
    {
        Passport::actingAs(
            factory(User::class)->create(['role_id' => Constants::ROLES['Seller'], 'is_active' => 0]),
            [Constants::ROLES_PASSPORT_SCOPE['Seller']]
        );

        $response = $this->post(
            route('seller.add_cup', ['clientSlug' => $this->client->slug()]),
            [],
            ['Accept' => 'application/json']
        );

        $response
            ->assertStatus(403)
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Seller can see when a cup is free.
     *
     * @return void
     */
    public function testSellerAddFree()
    {
        $client = factory(User::class)->create(['role_id' => Constants::ROLES['Client']]);
        factory(Purchase::class, config('params.free_cups', 7) - 1)->create([
            'seller_id' => $this->seller->id,
            'client_id' => $client->id,
        ]);

        Passport::actingAs(
            $this->seller,
            [Constants::ROLES_PASSPORT_SCOPE['Seller']]
        );

        $response = $this->post(
            route('seller.add_cup', ['clientSlug' => $client->slug()]),
            [],
            ['Accept' => 'application/json']
        );

        $response
            ->assertStatus(200)
            ->assertExactJson([
                'status' => 'give_free'
            ])
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Seller can see sales history.
     *
     * @return void
     */
    public function testSellerHistory()
    {
        factory(Purchase::class, 5)->create([
            'seller_id' => $this->seller->id,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 days'))
        ]);
        factory(Purchase::class, 5)->create([
            'seller_id' => $this->seller->id,
            'created_at' => date('Y-m-d H:i:s', strtotime('-7 days'))
        ]);

        Passport::actingAs(
            $this->seller,
            [Constants::ROLES_PASSPORT_SCOPE['Seller']]
        );

        $timestamp = strtotime('-5 days');
        $server = $this->transformHeadersToServerVars(['Accept' => 'application/json']);
        $response = $this->call('GET', route('seller.history'), ['from' => $timestamp], [], [], $server);

        $response
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Seller can get own statistics.
     *
     * @return void
     */
    public function testSellerStatistics()
    {
        factory(Purchase::class, 5)->create([
            'seller_id' => $this->seller
        ])->each(function (Purchase $purchase) {
            $purchase->created_at = $this->faker->dateTime();
            $purchase->save();
        });

        Passport::actingAs(
            $this->seller,
            [Constants::ROLES_PASSPORT_SCOPE['Seller']]
        );

        $response = $this->get(route('seller.history_dates', $this->seller), ['Accept' => 'application/json']);

        $response
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/json');
    }
}
