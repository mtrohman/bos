<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TambahStatusPadaKodeRekenings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kode_rekenings', function (Blueprint $table) {
            $table->tinyInteger('active')->after('jenis')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kode_rekenings', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
}
