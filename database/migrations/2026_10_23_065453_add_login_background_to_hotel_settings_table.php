
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hotel_settings', 'login_background')) {
            Schema::table('hotel_settings', function (Blueprint $table) {
                // Image displayed on the right side of Login Page
                $table->string('login_background')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_settings', 'login_background')) {
            Schema::table('hotel_settings', function (Blueprint $table) {
                $table->dropColumn('login_background');
            });
        }
    }
};
