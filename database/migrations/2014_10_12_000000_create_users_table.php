<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('fullname');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('passtxt')->nullable();
            $table->text('avatar')->nullable();
            $table->integer('theme_id')->default(false);
            $table->integer('isAdmin')->default(false);
            $table->timestamps();
        });

        // Insert Super Admin
        User::create(['fullname' => 'Administrator', 'username' => 'admin', 'email' => 'developer@pinday.co.id','password' => Hash::make('Password1'),'passtxt' => 'Password1','avatar' => 'avatar-1.jpg','created_at' => now()]);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
