<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('location');
            $table->string('status')->default('active'); // active, sold, inactive
            $table->integer('views')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('category_id');
        });

        // PostgreSQL full-text search index (only on PostgreSQL)
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX listings_search_idx ON listings USING GIN (to_tsvector(\'french\', title || \' \' || description))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
