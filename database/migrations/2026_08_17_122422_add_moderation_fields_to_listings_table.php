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
        Schema::table('listings', function (Blueprint $table) {
            $table->enum('moderation_status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending')->after('status');

            $table->text('moderation_reason')->nullable()->after('moderation_status');

            $table->timestamp('moderated_at')->nullable()->after('moderation_reason');

            $table->index('moderation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex(['moderation_status']);
            $table->dropColumn([
                'moderation_status',
                'moderation_reason',
                'moderated_at',
            ]);
        });
    }
};
