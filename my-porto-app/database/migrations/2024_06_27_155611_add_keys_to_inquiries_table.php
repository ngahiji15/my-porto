<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeysToInquiriesTable extends Migration
{
    public function up()
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('clientid')->nullable();
            $table->string('sharedkey')->nullable();
            $table->string('privatekey')->nullable();
            $table->string('dokupublickey')->nullable();
        });
    }

    public function down()
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn('clientid');
            $table->dropColumn('sharedkey');
            $table->dropColumn('privatekey');
            $table->dropColumn('dokupublickey');
        });
    }
}
