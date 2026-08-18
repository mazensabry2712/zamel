<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('requests') && ! Schema::hasTable('marketplace_requests')) {
            Schema::rename('requests', 'marketplace_requests');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('marketplace_requests') && ! Schema::hasTable('requests')) {
            Schema::rename('marketplace_requests', 'requests');
        }
    }
};
