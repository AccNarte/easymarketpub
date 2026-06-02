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
        Schema::table('produits', function (Blueprint $table) {
            $table->foreignId('categorie_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();
            $table->string('categorie_name');
            $table->string('categorie_icon')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
            $table->dropColumn(['categorie_id', 'categorie_name', 'categorie_icon']);
        });
    }
};
