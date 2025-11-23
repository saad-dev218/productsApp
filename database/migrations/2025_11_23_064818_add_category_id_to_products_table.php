<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('stock')->constrained()->onDelete('set null');
            $table->index('category_id');
            $table->index(['category_id', 'price']);
            $table->index(['category_id', 'stock']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['category_id', 'price']);
            $table->dropIndex(['category_id', 'stock']);
            $table->dropColumn('category_id');
        });
    }
};
