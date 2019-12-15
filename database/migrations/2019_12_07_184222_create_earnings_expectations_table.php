<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEarningsExpectationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('earnings_expectations', function (Blueprint $table) {
            $table->string('symbol');
            $table->string('quarter');
            $table->float('expectations')->nullable();
            $table->date('created_at');

            $table->primary(['symbol', 'quarter', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('earnings_expectations');
    }
}
