<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInquiriesTable extends Migration
{
    public function up()
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('path_url')->nullable();
            $table->json('request_body')->nullable();
            $table->json('request_header')->nullable();
            $table->json('response_body')->nullable();
            $table->json('response_header')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('http_code_status')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('transaction_invoice_number')->nullable();
            $table->string('type')->nullable();
            $table->string('path_url_token')->nullable();
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['user_id', 'http_code_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inquiries');
    }
}
