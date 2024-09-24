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
        Schema::table('sales', function (Blueprint $table)
        {
            $table->integer('status')->default(1)->after('customer_id');
        });

        Schema::table('product_sales', function (Blueprint $table)
        {
            $table->foreignId('color_id')->after('product_id')->constrained('colors');
            $table->foreignId('size_id')->after('color_id')->constrained('sizes');
            $table->float('price')->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_sales', function (Blueprint $table)
        {
            $table->dropColumn('price');

            $table->dropForeign(['size_id']);
            $table->dropColumn('size_id');

            $table->dropForeign(['color_id']);
            $table->dropColumn('color_id');
        });

        Schema::table('sales', function (Blueprint $table)
        {
            $table->dropColumn('status');
        });
    }
};
