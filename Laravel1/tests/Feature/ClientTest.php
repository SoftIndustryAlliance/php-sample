<?php

namespace Tests\Feature;

use App\Classes\Constants;
use App\Models\Purchase;
use App\Models\Shop;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:install');
    }

    /**
     * Client can get profile info.
     *
     * @return void
     */
    public function testClientProfile()
    {
        $this->seed('RolesSeeder');

        $client = factory(User::class)->create(['role_id' => Constants::ROLES['Client']]);
        factory(Purchase::class, config('params.free_cups', 7) - 1)->create([
            'client_id' => $client->id
        ]);

        Passport::actingAs(
            $client,
            [Constants::ROLES_PASSPORT_SCOPE['Client']]
        );

        $response = $this->get('/api/c/profile', ['Accept' => 'application/json']);

        $response
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'cups',
                'name',
                'user_id'
            ])
            ->assertJson([
                'cups' => 6
            ]);
    }

    /**
     * Client can get compact profile info.
     *
     * @return void
     */
    public function testClientProfileCompact()
    {
        $this->seed('RolesSeeder');

        Passport::actingAs(
            factory(User::class)->create(['role_id' => Constants::ROLES['Client']]),
            [Constants::ROLES_PASSPORT_SCOPE['Client']]
        );

        $server = $this->transformHeadersToServerVars(['Accept' => 'application/json']);
        $response = $this->call('GET', '/api/c/profile', ['compact' => 1], [], [], $server);

        $response
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonMissingExact([ // this is not working somehow...
                'name',
                'user_id'
            ]);
    }

    /**
     * Client can get stores list.
     *
     * @return void
     */
    public function testClientStores()
    {
        $this->seed('RolesSeeder');

        factory(Shop::class, 5)->create(['status' => 1, 'file_id' => null])->each(function ($translatable) {
            foreach (['ru'] as $locale) {
                $translatable->translateOrNew($locale)->name = $locale . ' ' . $this->faker->company;
                $translatable->translateOrNew($locale)->description = $locale . ' ' . $this->faker->paragraph;
                $translatable->save();
            }
        });

        Passport::actingAs(
            factory(User::class)->create(['role_id' => Constants::ROLES['Client']]),
            [Constants::ROLES_PASSPORT_SCOPE['Client']]
        );

        $server = $this->transformHeadersToServerVars(['Accept' => 'application/json']);
        $response = $this->call('GET', '/api/c/stores', ['lang' => 'en'], [], [], $server);

        $response
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                '*' => [
                    'name',
                    'details',
                    'thumbnail',
                    'coordinate'
                ]
            ]);
    }

    /**
     * Client can get total cups. Long poll request.
     *
     * @return void
     */
    public function testClientTotal()
    {
        $this->seed('RolesSeeder');

        $client = factory(User::class)->create(['role_id' => Constants::ROLES['Client']]);
        factory(Purchase::class, 5)->create([
            'client_id' => $client
        ]);

        Passport::actingAs(
            $client,
            [Constants::ROLES_PASSPORT_SCOPE['Client']]
        );

        $server = $this->transformHeadersToServerVars(['Accept' => 'application/json']);
        $response = $this->call('GET', route('client.check_total'), ['current' => 5], [], [], $server);

        $response
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'updated',
                'cups'
            ])
            ->assertJson([
                'updated' => false
            ]);
    }
}
