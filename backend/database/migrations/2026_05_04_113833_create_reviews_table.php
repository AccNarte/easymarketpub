<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $this->addForeignKeys($table);
            $this->addRatings($table);
            $this->addContent($table);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['purchase_id', 'reviewer_id']);
            $table->index(['reviewed_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }

    private function addForeignKeys(Blueprint $table): void
    {
        $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
        $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('reviewed_user_id')->constrained('users')->cascadeOnDelete();
    }

    private function addRatings(Blueprint $table): void
    {
        $table->unsignedTinyInteger('rating_overall');
        $table->unsignedTinyInteger('rating_communication')->nullable();
        $table->unsignedTinyInteger('rating_product_state')->nullable();
        $table->unsignedTinyInteger('rating_shipping_speed')->nullable();
    }

    private function addContent(Blueprint $table): void
    {
        $table->text('comment')->nullable();
        $table->text('seller_response')->nullable();
        $table->timestamp('seller_response_at')->nullable();
        $table->string('status')->default('published');
        $table->timestamp('review_deadline');
    }
};
