
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {

            // Primary key
            $table->id();

            // Foreign key: booked room
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->restrictOnDelete();

            // Foreign key: hotel guest
            $table->foreignId('guest_id')
                ->constrained('guests')
                ->restrictOnDelete();

            // Foreign key: Admin or Staff who created booking
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Reservation dates
            $table->date('check_in');
            $table->date('check_out');

            // Number of nights and guests
            $table->unsignedInteger('nights');
            $table->unsignedInteger('adults')->default(1);

            // Store booking price for receipt
            $table->decimal('price_per_night', 10, 2);
            $table->decimal('total_amount', 10, 2);

            // confirmed, checked_out, cancelled
            $table->string('status')->default('confirmed');

            // paid or unpaid
            $table->string('payment_status')->default('unpaid');

            // Date when payment was recorded
            $table->timestamp('paid_at')->nullable();

            // Additional booking notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
