<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyConversionRateColumnInFormTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_types', function (Blueprint $table) {
            $table->decimal('conversion_rate', 8, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_types', function (Blueprint $table) {
            $table->decimal('conversion_rate', 5, 4)->nullable()->change();
        });
    }
}
