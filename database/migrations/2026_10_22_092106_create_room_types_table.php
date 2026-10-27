
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create hotel room categories
        Schema::create('room_types', function (Blueprint $table) {

            // Primary key
            $table->id();

            // Example: Standard, Deluxe, VIP
            $table->string('name')->unique();

            // Description of the room category
            $table->text('description')->nullable();

            // Default price for this category
            $table->decimal('base_price', 10, 2);

            // Created and updated dates
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
