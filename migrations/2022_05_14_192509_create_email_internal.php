<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailInternal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_internal', function (Blueprint $table) {
            $table->id();
            $table->longText('subject');
            $table->longText('content');
            $table->longText('file')->nullable();
            $table->string('sender_type',255);
            $table->string('receiver_type',255);
            $table->bigInteger('sender_id');
            $table->bigInteger('receiver_id');
            $table->longText('token');

            
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('sender_read')->default(1);
            $table->tinyInteger('sender_trash')->default(1);
            $table->tinyInteger('sender_star')->default(0);
            $table->tinyInteger('receiver_read')->default(1);
            $table->tinyInteger('receiver_trash')->default(1);
            $table->tinyInteger('receiver_star')->default(0);
            
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
        Schema::dropIfExists('email_internal');
    }
}
