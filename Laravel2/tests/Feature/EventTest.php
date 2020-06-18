<?php

namespace Tests\Feature;

use App\Event;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Admin can see unpublished events.
     *
     * @return void
     */
    public function testAdminDraftEventsView()
    {
        $adminUser = factory(User::class)->create(['roles' => 1]);

        factory(Event::class)->create([
            'status' => config('constants.event_status.draft'),
        ]);

        $response = $this->actingAs($adminUser, 'api')
            ->get('/api/events');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('data.*.status', [0]);
    }

    /**
     * User can't see unpublished events.
     *
     * @return void
     */
    public function testUserDraftEventsView()
    {
        $user = factory(User::class)->create();

        factory(Event::class)->create([
            'status' => config('constants.event_status.draft'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->get('/api/events');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('data', []);
    }

    /**
     * User can see published events.
     *
     * @return void
     */
    public function testUserPublishedEventsView()
    {
        $user = factory(User::class)->create();

        factory(Event::class)->create([
            'status' => config('constants.event_status.published'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->get('/api/events');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('data.*.status', [1]);
    }

    /**
     * User can't see unpublished event.
     *
     * @return void
     */
    public function testUserDraftEventView()
    {
        $user = factory(User::class)->create();

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.draft'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->get(route('events.show', $event));

        $response->assertStatus(403);
    }

    /**
     * Admin can see unpublished event.
     *
     * @return void
     */
    public function testAdminDraftEventView()
    {
        $adminUser = factory(User::class)->create(['roles' => 1]);

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.draft'),
        ]);

        $response = $this->actingAs($adminUser, 'api')
            ->get(route('events.show', $event));

        $response->assertStatus(200);
    }

    /**
     * User can't publish an event.
     *
     * @return void
     */
    public function testUserPublishEvent()
    {
        $user = factory(User::class)->create();

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.draft'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->put(route('events.publish', $event));

        $response->assertStatus(403);
    }

    /**
     * Admin can publish an event.
     *
     * @return void
     */
    public function testAdminPublishEvent()
    {
        $adminUser = factory(User::class)->create(['roles' => 1]);

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.draft'),
        ]);

        $response = $this->actingAs($adminUser, 'api')
            ->put(route('events.publish', $event));

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 1);
    }

    /**
     * User can't unpublish an event.
     *
     * @return void
     */
    public function testUserUnpublishEvent()
    {
        $user = factory(User::class)->create();

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.published'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->put(route('events.unpublish', $event));

        $response->assertStatus(403);
    }

    /**
     * Admin can unpublish an event.
     *
     * @return void
     */
    public function testAdminUnpublishEvent()
    {
        $adminUser = factory(User::class)->create(['roles' => 1]);

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.published'),
        ]);

        $response = $this->actingAs($adminUser, 'api')
            ->put(route('events.unpublish', $event));

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 0);
    }

    /**
     * User can see participants of an event.
     *
     * @return void
     */
    public function testUserParticipantsOfEvent()
    {
        $user = factory(User::class)->create();

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.published'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->get(route('events.participants', $event));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                        'roles'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    /**
     * User can accept an event.
     *
     * @return void
     */
    public function testUserAcceptEvent()
    {
        $user = factory(User::class)->create();

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.published'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->put(route('events.accept', $event));

        $response->assertStatus(200);
    }

    /**
     * User can decline an event.
     *
     * @return void
     */
    public function testUserDeclineEvent()
    {
        $user = factory(User::class)->create();

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.published'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->put(route('events.decline', $event));

        $response->assertStatus(200);
    }
}
