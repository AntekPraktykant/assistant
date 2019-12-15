<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEarningsSurprisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('earnings_surprises', function (Blueprint $table) {
            $table->string('symbol');
            $table->string('quarter');
            $table->float('actual');
            $table->float('estimate')->nullable();

            $table->primary(['symbol', 'quarter']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('earnings_surprises');
    }
}
