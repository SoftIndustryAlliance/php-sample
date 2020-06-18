<?php

namespace Tests\Feature;

use App\Comment;
use App\Event;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * User can comment a published event.
     *
     * @return void
     */
    public function testUserCommentEvent()
    {
        $user = factory(User::class)->create();

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.published'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->post(route(
                'comments.store',
                [
                    'user_id' => $user,
                    'event_id' => $event,
                    'comment' => $this->faker->paragraph
                ]
            ));

        $response->assertStatus(201)
            ->assertJsonPath('data.id', 1);
    }

    /**
     * User can't comment a draft event.
     *
     * @return void
     */
    public function testUserCommentDraftEvent()
    {
        $user = factory(User::class)->create();

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.draft'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->post(route(
                'comments.store',
                [
                    'user_id' => $user,
                    'event_id' => $event,
                    'comment' => $this->faker->paragraph
                ]
            ));

        $response->assertStatus(403);
    }

    /**
     * User can see an event's comments.
     *
     * @return void
     */
    public function testUserEventComments()
    {
        $user = factory(User::class)->create();

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.published'),
        ]);

        factory(Comment::class, 5)->create([
            'event_id' => $event,
        ]);

        $response = $this->actingAs($user, 'api')
            ->get(route('comments.event', $event));

        $response->assertStatus(200);
    }

    /**
     * User can't see a draft event's comments.
     *
     * @return void
     */
    public function testUserDraftEventComments()
    {
        $user = factory(User::class)->create();

        $event = factory(Event::class)->create([
            'status' => config('constants.event_status.draft'),
        ]);

        factory(Comment::class, 5)->create([
            'event_id' => $event,
        ]);

        $response = $this->actingAs($user, 'api')
            ->get(route('comments.event', $event));

        $response->assertStatus(403);
    }
}
