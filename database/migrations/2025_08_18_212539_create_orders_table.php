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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number')->unique();
            $table->enum('status', [
                'received',
                'preparing',
                'with_courier',
                'out_for_delivery',
                'delivered',
                'delivery_failed',
                'cancelled'
            ])->default('received');
            $table->enum('type', ['dine_in', 'takeaway', 'delivery'])->default('dine_in');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 8, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->text('special_instructions')->nullable();
            $table->dateTime('estimated_ready_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
