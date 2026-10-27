
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {

            // Primary key
            $table->id();

            // Hotel guest information
            $table->string('full_name');

            $table->string('email')->nullable();

            $table->string('phone', 30);

            $table->string('nationality')->nullable();

            $table->text('address')->nullable();

            // Record creation and update dates
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
