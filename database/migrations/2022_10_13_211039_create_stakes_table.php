<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stakes', function (Blueprint $table) {
            $table->id();
            $table->string('pubkey');
            $table->string('withdrawer');
            $table->string('staker');
            $table->string('activationEpoch');
            $table->bigInteger('initial_balance_lamports');
            $table->bigInteger('balance_lamports');
            $table->string('delegated_vote_account');
            $table->string('state');
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
        Schema::dropIfExists('stakes');
    }
};
