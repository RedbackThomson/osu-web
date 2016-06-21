<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOsuIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('osu_incidents')) {
            return;
        }

        Schema::create('osu_incidents', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('incident_id');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->string('description', 150);
            $table->tinyInteger('status')->default(0);
            $table->integer('author_id')->unsigned()->nullable();
            $table->timestamp('date')->useCurrent();

            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('osu_incidents');
    }
}
