<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trip_passenger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // প্যাসেঞ্জার (User) আইডি
            $table->enum('status', ['checked_in', 'checked_out'])->default('checked_in');
            $table->timestamp('checked_in_at')->useCurrent();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();
            
            // একই ট্রিপে একই ইউজার যেন একাধিক একটিভ চেক-ইন না করতে পারে তার ইনডেক্সিং
            $table->unique(['trip_id', 'user_id', 'status'], 'unique_active_trip_passenger');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_passenger');
    }
};
