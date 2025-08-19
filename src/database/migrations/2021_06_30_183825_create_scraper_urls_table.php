<?php

namespace Molitor\Scraper\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'scraper_urls',
            function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('scraper_id');
                $table->foreign('scraper_id')->references('id')->on('scrapers');

                $table->string('type')->nullable();
                $table->string('hash', 32)->index();
                $table->string('url', 512);
                $table->integer('priority')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->dateTime('downloaded_at')->nullable();
                $table->dateTime('expiration_at')->nullable();
                $table->json('meta_data')->nullable();
                $table->timestamps();

                $table->unique(['scraper_id', 'url']);
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scraper_urls');
    }
};
