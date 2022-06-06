<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailReplyInternalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_reply_internal', function (Blueprint $table) {
            $table->id();
            $table->longText('content');
            $table->string('mail_id',255);
            $table->string('sender_id',255);
            $table->string('sender_type',255);
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
        Schema::dropIfExists('email_reply_internal');
    }
}
