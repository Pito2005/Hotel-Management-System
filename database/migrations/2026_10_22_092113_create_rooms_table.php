
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {

            // Primary key
            $table->id();

            // Unique hotel room number
            // Example: 101, 102, 201
            $table->string('room_number')->unique();

            // Foreign key to room_types table
            $table->foreignId('room_type_id')
                ->constrained('room_types')
                ->restrictOnDelete();

            // Floor number
            $table->unsignedInteger('floor')->nullable();

            // Nightly room price
            $table->decimal('price_per_night', 10, 2);

            // available, occupied, maintenance
            $table->string('status')->default('available');

            // Room picture path
            $table->string('image')->nullable();

            // Room details
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
