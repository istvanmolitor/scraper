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
            'scrapers',
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('base_url');
                $table->boolean('enabled');
                $table->boolean('robots_txt');
                $table->boolean('follow_links');
                $table->unsignedInteger('chunk_size')->default(1000);
                $table->dateTime('blocked')->nullable();
                $table->timestamps();

                $table->unique(['base_url']);
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
        Schema::dropIfExists('scrapers');
    }
};
