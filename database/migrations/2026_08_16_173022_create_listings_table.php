<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->decimal('price', 10, 2);

            $table->enum('condition', [
                'new',
                'like_new',
                'good',
                'fair',
            ]);

            $table->enum('status', [
                'draft',
                'published',
                'paused',
                'sold',
            ])->default('draft');

            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('category_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
