<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseInfosTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->longText('case_content')->nullable();
            $table->longText('image')->nullable();
            $table->string('bs_card')->nullable();
            $table->boolean('status')->default(true)->comment('是否啟用');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('case_infos');
    }
}
