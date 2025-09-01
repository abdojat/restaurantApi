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
        Schema::table('dishes', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->nullable()->comment('Discount percentage (0.00 to 100.00)');
            $table->datetime('discount_start_date')->nullable();
            $table->datetime('discount_end_date')->nullable();
            $table->boolean('is_on_discount')->default(false);
            
            // Add index for better performance when querying discounted dishes
            $table->index(['is_on_discount', 'discount_start_date', 'discount_end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn([
                'discount_percentage',
                'discount_start_date', 
                'discount_end_date',
                'is_on_discount'
            ]);
        });
    }
};
