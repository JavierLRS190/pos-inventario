<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('sku')->unique(); // código de producto
            $table->string('barcode')->nullable()->unique(); // código de barras
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // precio de venta
            $table->decimal('cost', 10, 2);  // precio de compra
            $table->integer('stock')->default(0);
            $table->integer('stock_min')->default(5); // alerta de mínimo
            $table->string('unit')->default('pieza'); // pieza, kg, litro, etc
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};