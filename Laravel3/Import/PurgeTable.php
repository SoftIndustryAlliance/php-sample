<?php

namespace App\Import;

use Illuminate\Support\Facades\DB;

/**
 * Purge table.
 *
 * @package App\Import
 */
class PurgeTable
{

    /**
     * Purges 'shop' table.
     */
    public function shops()
    {
        DB::table('shops')->delete();
    }
}
