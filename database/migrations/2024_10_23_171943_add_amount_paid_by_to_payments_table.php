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
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('amount_paid_1')->after('reserved_by');
            $table->unsignedBigInteger('amount_paid_2')->nullable()->after('reserved_by');
            $table->enum('payment_method_2', ['cash', 'bank_transfer', 'credit_card'])->nullable()->after('reserved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('amount_paid_1');
            $table->dropColumn('amount_paid_2');
            $table->dropColumn('payment_method_2', ['cash', 'bank_transfer', 'credit_card']);
        });
    }
};
