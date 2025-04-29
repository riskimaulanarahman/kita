<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblUseraccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_useraccess', function (Blueprint $table) {
            $table->id();
            $table->integer('module_id');
            $table->integer('user_id');
            $table->boolean('allowAdd')->default(false);
            $table->boolean('allowEdit')->default(false);
            $table->boolean('allowDelete')->default(false);
            $table->boolean('allowView')->default(false);
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
        Schema::dropIfExists('tbl_useraccess');
    }
}
