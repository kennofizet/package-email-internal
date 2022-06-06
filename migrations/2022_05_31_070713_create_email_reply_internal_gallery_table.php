<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailReplyInternalGalleryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_reply_internal_gallery', function (Blueprint $table) {
            $table->id();
            $table->string('path',255)->nullable();
            $table->string('type',255)->nullable();
            $table->string('dirname',255)->nullable();
            $table->string('basename',255)->nullable();
            $table->integer('timestamp')->nullable();
            $table->integer('size')->nullable();
            $table->string('extension',255)->nullable();
            $table->string('filename',255)->nullable();
            $table->integer('mail_id')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('email_reply_internal_gallery');
    }
}
