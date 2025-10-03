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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id')->nullable();

            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('category')->nullable();

            $table->string('material')->nullable();
            $table->string('care_method')->nullable();
            $table->string('weight')->nullable();
            $table->string('sku')->nullable();
            $table->integer('stock')->default(0);


            $table->enum('type', ['rental', 'formal'])->default('rental');
            $table->decimal('buy_price', 10, 2)->nullable();



            $table->decimal('base_price', 10, 2)->nullable();
            $table->decimal('deposit', 10, 2)->nullable();
            $table->decimal('late_fee', 10, 2)->nullable();
            $table->decimal('replacement_value', 10, 2)->nullable();
            $table->string('extensions')->nullable();

            $table->string('prep_buffer')->nullable();
            $table->string('min_rental')->nullable();
            $table->string('max_rental')->nullable();
            $table->date('blackout_date')->nullable();


            $table->string('location')->nullable();

            $table->string('fit_category')->nullable();
            $table->enum('length_unit', ['cm', 'inch'])->default('cm');
            $table->string('length')->nullable();
            $table->string('chest')->nullable();
            $table->string('sleeve')->nullable();


            $table->string('condition_grade')->nullable();
            // $table->string('status')->nullable();
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');

            $table->text('note')->nullable();
            $table->boolean('is_verified')->default(false)->nullable();
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
