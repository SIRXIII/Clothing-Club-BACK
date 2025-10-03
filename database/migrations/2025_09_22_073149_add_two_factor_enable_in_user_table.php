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
        Schema::table('users', function (Blueprint $table) {
             $table->enum('two_factor_method', ['none', 'totp', 'email'])->default('none');
           
            $table->string('two_factor_email_code')->nullable();
            $table->timestamp('two_factor_email_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
              $table->dropColumn([
                'two_factor_method',
                // 'two_factor_secret',
                'two_factor_email_code',
                'two_factor_email_expires_at'
            ]);
        });
    }
};
