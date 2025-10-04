<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConversionRateToFormTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_types', function (Blueprint $table) {
            $table->decimal('conversion_rate', 5, 4)->nullable()->after('international_price')->comment('Exchange rate for currency conversion');
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
            $table->dropColumn('conversion_rate');
        });
    }
}
