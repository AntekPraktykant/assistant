<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('symbol');
            $table->string('underlying');
            $table->datetime('trade_date');
            $table->date('expiry');
            $table->float('strike');
            $table->string('option_type');
            $table->string('transaction_type');
            $table->integer('quantity');
            $table->float('price');
            $table->float('proceeds');
            $table->float('commission');
            $table->string('code');

            $table->integer('user_id');
            $table->string('currency');
//            $table->string('id');
            $table->unsignedInteger('status_id')->references('id')->on('transaction_status');

            $table->string('matching_hash');
            $table->string('rolled_to')->default('null');
            $table->string('rolled_from')->default('null');


//            $table->foreign('status_id');//->references('id')->on('transaction_status');

            $table->unique([
                'symbol',
                'trade_date',
//                'settleDate',
//                'type',
//                'quantity',
//                'price',
                'proceeds',
//                'commission',
//                'code',
                'user_id',
                'currency',
            ]);
            $table->unique(['id']);
//            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
