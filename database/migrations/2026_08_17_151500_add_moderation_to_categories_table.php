<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])
                ->default('approved')
                ->after('seo_description');

            $table->foreignId('created_by')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('moderation_reason')
                ->nullable()
                ->after('created_by');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'created_by',
                'moderation_reason',
            ]);
        });
    }
};
