<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'has_seen_welcome')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('has_seen_welcome')->default(false);
            });

            DB::table('users')->update(['has_seen_welcome' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'has_seen_welcome')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('has_seen_welcome');
            });
        }
    }
};