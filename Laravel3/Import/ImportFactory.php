<?php

namespace App\Import;

use App\Import\Extract\JsonClients;
use App\Import\Extract\JsonCups;
use App\Import\Extract\JsonSellers;
use App\Import\Extract\JsonShops;
use App\Import\Extract\JsonUsers;
use App\Import\IdMap\IdMapRepository;
use App\Import\Loader\ClientLoader;
use App\Import\Loader\CupLoader;
use App\Import\Loader\SellerLoader;
use App\Import\Loader\ShopLoader;
use App\Import\Loader\UserLoader;
use App\Import\Source\DataSource;
use App\Import\Validation\BaseDataValidator;
use App\Import\Validation\ClientValidator;
use App\Import\Validation\SellerValidator;
use App\Import\Validation\UserValidator;

/**
 * Import factory.
 *
 * @package App\Import
 */
class ImportFactory
{

    /**
     * @var IdMapRepository
     */
    private $idMapRepository;

    public function __construct(IdMapRepository $idMapRepository)
    {
        $this->idMapRepository = $idMapRepository;
    }

    /**
     * Creates the shops import from the JSON source.
     *
     * @param DataSource $source
     *
     * @return Import
     */
    public function shopsFromJson(DataSource $source): Import
    {
        /** @var JsonShops $extract */
        $extract = app(JsonShops::class);
        $extract->setSource($source);

        return new Import(
            $extract,
            app(ShopLoader::class),
            $this->idMapRepository,
            app(BaseDataValidator::class)
        );
    }

    /**
     * Creates the sellers import from the JSON source.
     *
     * @param DataSource $source
     *
     * @return Import
     */
    public function sellersFromJson(DataSource $source): Import
    {
        /** @var JsonSellers $extract */
        $extract = app(JsonSellers::class);
        $extract->setSource($source);

        return new Import(
            $extract,
            app(SellerLoader::class),
            $this->idMapRepository,
            app(SellerValidator::class)
        );
    }

    /**
     * Creates the clients import from the JSON source.
     *
     * @param DataSource $source
     *
     * @return Import
     */
    public function clientsFromJson(DataSource $source): Import
    {
        /** @var JsonClients $extract */
        $extract = app(JsonClients::class);
        $extract->setSource($source);

        return new Import(
            $extract,
            app(ClientLoader::class),
            $this->idMapRepository,
            app(ClientValidator::class)
        );
    }

    /**
     * Creates the cups import from the JSON source.
     *
     * @param DataSource $source
     *
     * @return Import
     */
    public function cupsFromJson(DataSource $source): Import
    {
        /** @var JsonCups $extract */
        $extract = app(JsonCups::class);
        $extract->setSource($source);

        return new Import(
            $extract,
            app(CupLoader::class),
            $this->idMapRepository,
            app(BaseDataValidator::class)
        );
    }

    /**
     * Creates the users import from the JSON source.
     *
     * @param DataSource $source
     *
     * @return Import
     */
    public function usersFromJson(DataSource $source): Import
    {
        /** @var JsonUsers $extract */
        $extract = app(JsonUsers::class);
        $extract->setSource($source);

        return new Import(
            $extract,
            app(UserLoader::class),
            $this->idMapRepository,
            app(UserValidator::class)
        );
    }
}
