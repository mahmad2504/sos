<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->integer('user_id');
			$table->string('name');
			$table->string('oaname')->nullable();
			$table->string('description')->default('No Description Given');
            $table->string('jiraquery',2000);
			$table->string('last_synced')->default("Never");
			$table->string('estimation')->default(0);
			$table->string('jirauri');
			$table->date('sdate');
			$table->date('edate');
			$table->boolean('jira_dependencies')->default(0);
			$table->boolean('dirty')->default(1);
			$table->string('progress')->default(0);
            $table->string('uri')->default('');
            $table->string('baseline')->default("");
			$table->boolean('task_description')->default(0);
			$table->string('state')->default('SYSTEM');
			$table->boolean('archive')->default(0);
			$table->string('visible')->default('true');
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
        Schema::dropIfExists('projects');
    }
}
