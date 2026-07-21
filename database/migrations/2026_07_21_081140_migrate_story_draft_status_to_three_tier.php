<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('stories')
            ->where('status', 'draft')
            ->update(['status' => 'idea']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('stories')
            ->where('status', 'idea')
            ->update(['status' => 'draft']);
    }
};
