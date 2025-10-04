<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmissionFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admission_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained('applications')->onDelete('cascade');

            // Personal Data
            $table->string('full_name');
            $table->date('dob')->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('passport_number')->nullable();
            $table->text('mailing_address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email');
            $table->boolean('hostel_required')->nullable();
            $table->boolean('has_disability')->nullable();
            $table->text('disability_details')->nullable();

            // Programmes and preferences
            $table->string('prog_eng')->nullable();
            $table->string('prog_eng_mode')->nullable();
            $table->string('prog_focis')->nullable();
            $table->string('prog_focis_mode')->nullable();
            $table->string('prog_business')->nullable();
            $table->string('prog_business_mode')->nullable();
            $table->string('pref1')->nullable();
            $table->string('pref2')->nullable();
            $table->string('pref3')->nullable();

            // Enrollment Options
            $table->boolean('entry_wassce')->default(false);
            $table->boolean('entry_sssce')->default(false);
            $table->boolean('entry_ib')->default(false);
            $table->boolean('entry_transfer')->default(false);
            $table->boolean('entry_other')->default(false);
            $table->string('other_entry_detail')->nullable();
            $table->string('preferred_session')->nullable();
            $table->string('preferred_campus')->nullable();
            $table->string('intake_option')->nullable();

            // Language
            $table->string('english_level')->nullable();
            $table->string('mother_tongue')->nullable();
            $table->string('other_languages')->nullable();

            // Repeating sections and uploads as JSON
            $table->json('institutions')->nullable();
            $table->json('employment')->nullable();
            $table->json('uploads')->nullable();
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
        Schema::dropIfExists('admission_forms');
    }
}
