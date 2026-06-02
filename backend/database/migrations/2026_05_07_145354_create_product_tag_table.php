<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produit_tag', function (Blueprint $table) {
            $table->foreignId('produit_id')
                  ->constrained('produits')
                  ->cascadeOnDelete();

            $table->foreignId('tag_id')
                  ->constrained('tags')
                  ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['produit_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produit_tag');
    }
};
