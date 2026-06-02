<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add balance to users
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 12, 2)->default(0)->after('password');
        });

        // Payment methods table
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // card, bank, crypto
            $table->string('provider'); // visa, mastercard, bitcoin, ethereum, paypal, etc.
            $table->string('label'); // "Visa •••• 4242", "Bitcoin Wallet"
            $table->json('details')->nullable(); // encrypted card details, wallet address, etc.
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Transactions table
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // deposit, withdrawal, purchase, sale, refund
            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 8, 2)->default(0);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('status')->default('pending'); // pending, completed, failed, cancelled
            $table->string('reference')->unique();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('payment_methods');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};
