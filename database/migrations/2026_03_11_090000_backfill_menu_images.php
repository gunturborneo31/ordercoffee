<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menus')
            ->where(function ($query) {
                $query->whereNull('image_url')->orWhere('image_url', '');
            })
            ->where('category', 'coffee')
            ->update(['image_url' => '/images/menu/default-coffee.svg']);

        DB::table('menus')
            ->where(function ($query) {
                $query->whereNull('image_url')->orWhere('image_url', '');
            })
            ->where('category', 'non-coffee')
            ->update(['image_url' => '/images/menu/default-non-coffee.svg']);

        DB::table('menus')
            ->where(function ($query) {
                $query->whereNull('image_url')->orWhere('image_url', '');
            })
            ->where('category', 'food')
            ->update(['image_url' => '/images/menu/default-food.svg']);

        DB::table('menus')
            ->where(function ($query) {
                $query->whereNull('image_url')->orWhere('image_url', '');
            })
            ->update(['image_url' => '/images/menu/default-food.svg']);
    }

    public function down(): void
    {
        // No rollback required for backfill migration.
    }
};
