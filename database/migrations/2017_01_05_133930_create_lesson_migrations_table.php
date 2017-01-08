<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateDaysTable
 */
class CreateLessonMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('ebre_escool')->create('lesson_migration', function (Blueprint $table) {
            $table->integer('lesson_id');
            $table->integer('newlesson_id')->unsigned();
            $table->unique('lesson_id');
            $table->unique('newlesson_id');
            $table->timestamps();
            $table->foreign('lesson_id')->references('lesson_id')->on('lesson');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('ebre_escool')->dropIfExists('lesson_migration');
    }
}
