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
        Schema::create('colors', function ($table)
        {
            $table->id()->startingValue(rand(777, 999));
            $table->string('name');
            $table->string('hex')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table)
        {
            $table->string('description')->nullable()->default(null)->change();
        });

        Schema::dropIfExists('product_categories');

        Schema::create('product_colors', function ($table)
        {
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('color_id')->constrained('colors');
        });

        Schema::table('products', function ($table)
        {
            $table->foreignId('category_id')->after('id')->constrained('categories');
            $table->dropColumn('name');
            $table->string('description')->nullable()->default(null)->change();
        });

        Schema::table('product_stock_stores', function (Blueprint $table)
        {
            $table->foreignId('color_id')->after('product_id')->constrained('colors');
            $table->foreignId('size_id')->after('color_id')->constrained('sizes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stock_stores', function (Blueprint $table)
        {
            $table->dropForeign(['color_id']);
            $table->dropColumn('color_id');

            $table->dropForeign(['size_id']);
            $table->dropColumn('size_id');
        });

        Schema::table('products', function ($table)
        {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            $table->string('name')->after('id');
            $table->string('description')->change();
        });

        Schema::dropIfExists('product_colors');

        Schema::create('product_categories', function (Blueprint $table)
        {
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('category_id')->constrained('categories');
        });

        Schema::table('categories', function (Blueprint $table)
        {
            $table->string('description')->change();
        });

        Schema::dropIfExists('colors');
    }
};
