<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_resources', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->integer('project_id');
			$table->integer('resource_id');
			$table->string('oaid')->nullable();
			$table->float('efficiency')->default(1.0);
			$table->float('cost')->default(1.0);
			$table->string('team')->nullable();
			$table->string('cc')->nullable();
			$table->boolean('active')->default(1);
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
        Schema::dropIfExists('project_resources');
    }
}
