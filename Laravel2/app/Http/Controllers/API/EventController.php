<?php

namespace App\Http\Controllers\API;

use App\Event;
use App\Http\Resources\EventResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ParticipantResource;
use App\Http\Resources\UserResource;
use App\Participant;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin')->except(['index', 'show', 'accept', 'decline', 'participants']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        if ($request->user()->isAdmin()) {
            return EventResource::collection(Event::paginate(25));
        } else {
            return  EventResource::collection(
                Event::where('status', config('constants.event_status.published'))->paginate(25)
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $eventData = [
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'location' => $request->location,
            'description' => $request->description,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'status' => $request->status,
        ];

        $images = ['image', 'icon'];
        array_walk($images, function ($item, $key) use ($request) {
            if ($request->get($item)) {
                $image = $request->get($item);
                $name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
                Image::make($request->get($item))->save(public_path('images/') . $name);
                $eventData[$item] = $name;
            }
        });
        $event = Event::create($eventData);

        return new EventResource($event);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Event $event)
    {
        if (!$request->user()->isAdmin() && $event->status !== config('constants.event_status.published')) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }
        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        $event->update($request->only(['name', 'location', 'description', 'date_start', 'date_end']));

        return new CommentResource($event);
    }

    /**
     * Publish the specified event.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function publish(Event $event)
    {
        $event->update(['status' => config('constants.event_status.published')]);

        return new EventResource($event);
    }

    /**
     * Unpublish the specified event.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function unpublish(Event $event)
    {
        $event->update(['status' => config('constants.event_status.draft')]);

        return new EventResource($event);
    }

    /**
     * Accept the specified event.
     *
     * @param Request $request
     * @param \App\Event $event
     * @return EventResource
     */
    public function accept(Request $request, Event $event)
    {
        $event->participants()
            ->attach($request->user()->id, ['status' => config('constants.participant_status.accepted')]);

        return new EventResource($event);
    }

    /**
     * Decline the specified event.
     *
     * @param Request $request
     * @param \App\Event $event
     * @return EventResource
     */
    public function decline(Request $request, Event $event)
    {
        $event->participants()
            ->attach($request->user()->id, ['status' => (int) config('constants.participant_status.declined')]);

        return new EventResource($event);
    }

    /**
     * Get participants of specified event.
     *
     * @param Request $request
     * @param \App\Event $event
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function participants(Event $event)
    {
        return UserResource::collection($event->participants()->paginate(25));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Event $event
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(null, 204);
    }
}
