<?php

namespace App\Http\Controllers\API;

use App\Comment;
use App\Event;
use App\Http\Resources\CommentResource;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin')->except(['show', 'store', 'event']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return CommentResource::collection(Comment::paginate(25));
    }

    /**
     * Display a listing of comments for a supplied event.
     *
     * @param Request $request
     * @param Event $event
     * @return AnonymousResourceCollection
     */
    public function event(Request $request, Event $event)
    {
        if (!$request->user()->isAdmin() && $event->status !== config('constants.event_status.published')) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }
        return CommentResource::collection($event->comments()->paginate(25));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Event $event
     * @return CommentResource
     */
    public function store(Request $request)
    {
        $event = Event::findOrFail($request->event_id);

        if (!$request->user()->isAdmin() && $event->status !== config('constants.event_status.published')) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'event_id' => $request->event_id,
            'comment' => $request->comment
        ]);

        return new CommentResource($comment);
    }

    /**
     * Display the specified resource.
     *
     * @param Comment $comment
     * @return Response
     */
    public function show(Comment $comment)
    {
        return new CommentResource($comment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Comment $comment
     * @return Response
     */
    public function update(Request $request, Comment $comment)
    {
        // check if currently authenticated user is the owner of the comment
        if ($request->user()->id !== $comment->user_id) {
            return response()->json(['error' => 'You can only edit your own comments.'], 403);
        }

        $comment->update($request->only(['comment']));

        return new CommentResource($comment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CRequest $request
     * @param omment $comment
     * @return Response
     * @throws Exception
     */
    public function destroy(Request $request, Comment $comment)
    {
        // check if currently authenticated user is the owner of the comment
        if ($request->user()->id !== $comment->user_id) {
            return response()->json(['error' => 'You can only delete your own comments.'], 403);
        }
        $comment->delete();

        return response()->json(null, 204);
    }
}
