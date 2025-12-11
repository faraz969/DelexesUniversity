<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressFieldsToAdmissionFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            $table->string('street_address')->nullable()->after('mailing_address');
            $table->string('post_code')->nullable()->after('street_address');
            $table->string('city')->nullable()->after('post_code');
            $table->string('country')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            $table->dropColumn(['street_address', 'post_code', 'city', 'country']);
        });
    }
}
