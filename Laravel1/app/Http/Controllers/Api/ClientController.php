<?php

namespace App\Http\Controllers\Api;

use App\Models\News;
use App\Models\Shop;
use App\Services\ClientService;
use App\Http\Controllers\Controller;
use App\Services\Media\ImageServiceInterface;
use App\Repositories\NewsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Implements the Client API methods.
 */
class ClientController extends Controller
{

    /**
     * @var ClientService
     */
    protected $clientService;

    /**
     * ClientController constructor.
     *
     * @param ClientService $clientService
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * The client's statistics.
     *
     * @return array
     */
    public function stat()
    {
        $client = Auth::user();

        return [
            'purchased' => $this->clientService->getCupsCount($client, ClientService::COUNT_ONLY_PURCHASED),
            'free' => $this->clientService->getCupsCount($client, ClientService::COUNT_ONLY_FREE),
            'total' => $this->clientService->getCupsCount($client),
        ];
    }

    /**
     * The client's total.
     *
     * @return array
     */
    public function checkTotal(Request $request)
    {
        $client = Auth::user();
        $current = $request->input('current', 0);
        $total = $this->clientService->getFreeCupReach($client);

        /**
         * Close session, otherwise it will block consequent requests to this session while sleeping.
         */
        session_write_close();
        $time = 0;
        $time_limit = 10;

        while ($total == $current && $time < $time_limit) {
            sleep(1);
            $time++;
            $total = $this->clientService->getFreeCupReach($client);
        }

        return [
            'updated' => $total != $current,
            'cups' => $total
        ];
    }

    /**
     * The client's profile.
     *
     * @param Request $request
     * @return array
     */
    public function profile(Request $request)
    {
        $client = Auth::user();

        $cups = $this->clientService->getFreeCupReach($client);

        return ($request->has('compact')) ? ['cups' => $cups] : [
            'cups' => $cups,
            'name' => $client->name,
            'user_id' => $client->slug(),
        ];
    }

    /**
     * A list of stores for client.
     *
     * @param ImageServiceInterface $imageService
     * @return array
     */
    public function stores(ImageServiceInterface $imageService)
    {
        $results = [];
        Shop::where('status', 1)->chunk(50, function ($shops) use (&$results, $imageService) {
            foreach ($shops as $shop) {
                $results[] = [
                    'name' => $shop->translate(App::getLocale(), true)->name,
                    'details' => (string)$shop->translate(App::getLocale(), true)->description,
                    'thumbnail' => $imageService->getUrl($shop->file),
                    'coordinate' => [
                        'latitude' => $shop->lat,
                        'longitude' => $shop->lng,
                    ],
                ];
            }
        });
        return $results;
    }

    /**
     * A list of news for client.
     *
     * @param ImageServiceInterface $imageService
     * @param NewsRepository $newsRepository
     * @return array
     */
    public function news(
        Request $request,
        ImageServiceInterface $imageService,
        NewsRepository $newsRepository
    ) {
        $request->validate([
            'offset' => 'sometimes|integer',
            'count' => 'sometimes|integer',
        ]);
        $results = [
            'news' => [],
            'has_more' => false
        ];
        $offset = $request->input('offset', 0);
        $count = $request->input('count', 10);
        $now = Carbon::now()->toDateTimeString();
        $user = auth()->user();

        $newsCount = $newsRepository->newsCount($now, $offset, $count);

        $results['has_more'] = $newsCount > $count;

        $news = $newsRepository->newsItems($now, $offset, $count);

        foreach ($news as $newsItem) {
            $timeStart = \DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $newsItem->date_start
            );

            $results['news'][] = [
                'title' => $newsItem->translate($user->locale, true)->name,
                'short_description' => Str::limit(
                    (string)$newsItem->translate($user->locale, true)->description,
                    200
                ),
                'image' => $imageService->getThumbUrl($newsItem->file, 'preview'),
                'date' => $timeStart->getTimestamp(),
                'id' => $newsItem->id
            ];
        }

        return $results;
    }

    public function newsItem(News $newsItem, ImageServiceInterface $imageService)
    {
        $timeStart = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $newsItem->date_start
        );

        return [
            'title' => $newsItem->translate(App::getLocale(), true)->name,
            'full_description' => (string)$newsItem->translate(App::getLocale(), true)->description,
            'image' => $imageService->getThumbUrl($newsItem->file, 'preview'),
            'date' => $timeStart->getTimestamp(),
        ];
    }
}
