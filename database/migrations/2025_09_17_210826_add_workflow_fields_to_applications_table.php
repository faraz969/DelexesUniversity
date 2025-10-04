<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkflowFieldsToApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            // Add department_id to track which department the application is for
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            
            // Add workflow status fields
            $table->enum('hod_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('president_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('registrar_status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Add comments/notes from each approver
            $table->text('hod_comments')->nullable();
            $table->text('president_comments')->nullable();
            $table->text('registrar_comments')->nullable();
            
            // Add timestamps for each approval
            $table->timestamp('hod_reviewed_at')->nullable();
            $table->timestamp('president_reviewed_at')->nullable();
            $table->timestamp('registrar_reviewed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'department_id',
                'hod_status',
                'president_status', 
                'registrar_status',
                'hod_comments',
                'president_comments',
                'registrar_comments',
                'hod_reviewed_at',
                'president_reviewed_at',
                'registrar_reviewed_at'
            ]);
        });
    }
}
