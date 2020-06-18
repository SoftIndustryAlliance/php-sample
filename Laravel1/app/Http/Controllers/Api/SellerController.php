<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PurchaseCollection;
use App\Http\Resources\SellerResource;
use App\Models\Client;
use App\Models\Seller;
use App\Repositories\SellerPurchasesRepository;
use App\Services\SellerService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Implements the Seller API sales methods.
 */
class SellerController extends Controller
{
    /**
     * @var SellerService
     */
    protected $sellerService;

    /**
     * @var SellerPurchasesRepository
     */
    protected $sellerPurchases;

    /**
     * SellerController constructor.
     *
     * @param SellerService $sellerService
     * @param SellerPurchasesRepository $sellerPurchases
     */
    public function __construct(SellerService $sellerService, SellerPurchasesRepository $sellerPurchases)
    {
        $this->sellerService = $sellerService;
        $this->sellerPurchases = $sellerPurchases;
    }

    /**
     * Adds a cup to the client.
     *
     * @param string $clientSlug
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \App\Exceptions\SellerInactiveException
     * @throws \App\Exceptions\UserRoleRequiredException
     */
    public function addCup(string $clientSlug)
    {
        $client = Client::findBySlug($clientSlug);
        if (! $client) {
            return response()->json([
                'status' => 'not_found',
            ]);
        }

        $seller = Seller::getFromAuth();

        $purchase = $this->sellerService->addCups($client, $seller);

        return response()->json([
            'status' => $purchase->is_free ? 'give_free' : 'added',
        ]);
    }

    /**
     * Gets the seller cups purchase history.
     *
     * @param Request $request The request accepts the following parameters:
     * limit - the limit of the history entries
     * from  - the timestamp from which to query the history
     *
     * @return PurchaseCollection
     *
     * @throws \App\Exceptions\UserRoleRequiredException
     * @throws \App\Exceptions\SellerInactiveException
     */
    public function history(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|max:100|min:1',
            'from' => 'nullable|integer',
            'date' => 'nullable|integer',
        ]);

        $from = $request->from;
        $date = $request->date;
        if ($from && $date) {
            throw new \RuntimeException('Only "from" or "date" is allowed.');
        }

        $seller = Seller::getFromAuth();
        $limit = $request->input('limit', 0);

        if ($from) {
            $fromDate = Carbon::createFromTimestamp($from);
            $resources = $this->sellerPurchases->all($seller, $limit, $fromDate);
        } elseif ($date) {
            $onDate = Carbon::createFromTimestamp($date);
            $resources = $this->sellerPurchases->onDate($seller, $onDate);
        } else {
            $resources = $this->sellerPurchases->all($seller, $limit);
        }

        return new PurchaseCollection($resources);
    }

    /**
     * Gets dates for the seller cups purchase history.
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws \App\Exceptions\UserRoleRequiredException
     * @throws \App\Exceptions\SellerInactiveException
     */
    public function historyDates(Request $request)
    {
        $seller = Seller::getFromAuth();
        return [
            'dates' => $this->sellerService->getPurchaseDates($seller),
        ];
    }

    /**
     * Returns a list of sellers.
     *
     * @return array
     */
    public function sellers()
    {
        SellerResource::withoutWrapping();
        return SellerResource::collection($this->sellerService->getSellers());
    }

    /**
     * Returns a list of the seller purchase dates.
     *
     * @param Seller $seller
     *
     * @return array
     */
    public function salesDates(Seller $seller)
    {
        return [
            'dates' => $this->sellerService->getPurchaseDates($seller),
        ];
    }

    /**
     * Returns a list of the sold cups for the specified seller.
     *
     * @param Seller $seller
     * @param int $timestamp
     *
     * @return PurchaseCollection
     *
     * @throws \Exception
     */
    public function salesCups(Seller $seller, int $timestamp)
    {
        return new PurchaseCollection(
            $this->sellerPurchases->onDate($seller, Carbon::createFromTimestamp($timestamp))
        );
    }

    /**
     * Returns statistics for a range of dates.
     *
     * @param Seller $seller
     * @param int $from
     * @param int $to
     *
     * @return PurchaseCollection
     *
     * @throws \Exception
     */
    public function salesFull(Seller $seller, int $from, int $to)
    {
        return new PurchaseCollection(
            $this->sellerPurchases->onDates(
                $seller,
                Carbon::createFromTimestamp($from),
                Carbon::createFromTimestamp($to)
            )
        );
    }
}
