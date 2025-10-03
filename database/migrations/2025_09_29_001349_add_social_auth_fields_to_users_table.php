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
            $table->string('provider')->nullable()->after('email');
            $table->string('provider_id')->nullable()->after('provider');
            $table->string('provider_token')->nullable()->after('provider_id');
            $table->string('provider_refresh_token')->nullable()->after('provider_token');
            $table->string('avatar')->nullable()->after('provider_refresh_token');
            
            // Make password nullable for social login users
            $table->string('password')->nullable()->change();
            
            // Add unique index for provider and provider_id combination
            $table->unique(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['provider', 'provider_id']);
            $table->dropColumn(['provider', 'provider_id', 'provider_token', 'provider_refresh_token', 'avatar']);
            $table->string('password')->nullable(false)->change();
        });
    }
};