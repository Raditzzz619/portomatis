<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 16)->default('user')->index();
        });
        Schema::create('builder_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('content');
            $table->timestamp('completed_at')->nullable();
            $table->uuid('published_token')->nullable()->unique();
            $table->timestamps();
        });
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 80);
            $table->string('step', 30)->nullable();
            $table->timestamp('created_at')->index();
            $table->index(['user_id', 'created_at']);
        });
        DB::table('users')->updateOrInsert(['email' => 'admin@gmail.com'], [
            'name' => 'Administrator', 'password' => Hash::make('admin123'), 'role' => 'admin',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('builder_portfolios');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
