<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table)
        {
            $table->id()->startingValue(rand(777, 999));

            $table->string('name');
            $table->string('address');
            $table->string('phone');

            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table)
        {
            $table->foreignId('store_id')->nullable()->constrained('stores')->after('id');
        });

        Schema::create('categories', function (Blueprint $table)
        {
            $table->id()->startingValue(rand(777, 999));

            $table->string('name');
            $table->string('description');

            $table->timestamps();
        });

        Schema::create('sizes', function (Blueprint $table)
        {
            $table->id()->startingValue(rand(777, 999));

            $table->string('name');
            $table->string('description');

            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table)
        {
            $table->id()->startingValue(rand(777, 999));

            $table->string('name');
            $table->string('description');
            $table->decimal('price');
            $table->decimal('special_price')->nullable();

            $table->timestamps();
        });

        Schema::create('product_sizes', function (Blueprint $table)
        {
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('size_id')->constrained('sizes');
        });

        Schema::create('product_categories', function (Blueprint $table)
        {
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('category_id')->constrained('categories');
        });

        Schema::create('product_stock_stores', function (Blueprint $table)
        {
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('product_id')->constrained('products');

            $table->integer('stock');

            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table)
        {
            $table->id()->startingValue(rand(777, 999));
            $table->boolean('special')->default(false);

            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('tributary_number')->default('C/F');

            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table)
        {
            $table->id()->startingValue(rand(777, 999));
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('customer_id')->constrained('customers');

            $table->dateTime('date');
            $table->decimal('total');

            $table->timestamps();
        });

        Schema::create('product_sales', function (Blueprint $table)
        {
            $table->foreignId('sale_id')->constrained('sales');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
        });

        Schema::create('store_transactions', function (Blueprint $table)
        {
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_transactions');
        Schema::dropIfExists('product_sales');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('product_stock_stores');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_sizes');
        Schema::dropIfExists('products');
        Schema::dropIfExists('sizes');
        Schema::dropIfExists('categories');

        Schema::table('users', function (Blueprint $table)
        {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::dropIfExists('stores');
    }
};
