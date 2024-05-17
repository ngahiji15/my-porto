<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class transactions extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->change();
            $table->string('payment_channel')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->string('type')->nullable()->change();
            $table->string('order_type')->nullable()->change();
            $table->timestamp('update_date')->nullable()->change();
            $table->string('payment_code')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('invoice_number')->change();
            $table->string('payment_channel')->change();
            $table->unsignedBigInteger('user_id')->change();
            $table->string('status')->change();
            $table->string('type')->change();
            $table->string('order_type')->change();
            $table->timestamp('update_date')->change();
            $table->string('payment_code')->change();
        });
    }
}



