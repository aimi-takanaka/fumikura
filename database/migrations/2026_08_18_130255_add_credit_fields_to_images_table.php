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
        Schema::table('images', function (Blueprint $table) {
            $table->string('credit_type')->nullable()->after('title');
            $table->string('credit_author')->nullable()->after('credit_type');
            $table->string('credit_service')->nullable()->after('credit_author');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['credit_type', 'credit_author', 'credit_service']);
        });
    }
};
