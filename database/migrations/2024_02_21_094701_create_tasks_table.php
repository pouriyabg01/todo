<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->enum('repeat' , ['daily' , 'weekly' , 'monthly' , 'yearly'])->nullable();
            $table->boolean('completed')->default(0);
            $table->tinyInteger('priority')->nullable();
            $table->dateTime('last_repeat')->nullable();
            $table->dateTime('due')->nullable();
            $table->dateTime('reminder')->nullable();
            $table->foreignIdFor(User::class , 'user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
