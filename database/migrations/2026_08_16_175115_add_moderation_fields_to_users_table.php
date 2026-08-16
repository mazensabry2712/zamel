<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', [
                'active',
                'suspended',
                'banned',
            ])->default('active')->after('password');

            $table->timestamp('suspended_until')->nullable();
            $table->text('moderation_reason')->nullable();
            $table->timestamp('moderated_at')->nullable();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);

            $table->dropColumn([
                'status',
                'suspended_until',
                'moderation_reason',
                'moderated_at',
            ]);
        });
    }
};
