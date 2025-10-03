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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
              $table->string('refund_id')->unique();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('traveler_id')->nullable()->constrained('travelers')->onDelete('set null');
            $table->foreignId('partner_id')->nullable()->constrained('partners')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['Pending', 'Processed', 'Rejected'])->default('Pending');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
