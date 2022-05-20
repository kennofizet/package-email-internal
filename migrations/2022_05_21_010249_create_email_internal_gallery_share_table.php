<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailInternalGalleryShareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_internal_gallery_share', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_file')->nullable();
            $table->bigInteger('id_can')->nullable();
            $table->bigInteger('can_status')->nullable();
            // 1 = author
            // 2 = read only
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_internal_gallery_share');
    }
}
