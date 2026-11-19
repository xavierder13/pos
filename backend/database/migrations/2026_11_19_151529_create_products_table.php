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
            $table->string('sku', 64)->unique();
            $table->string('barcode', 64)->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit', 16)->default('pc');            // pc, kg, g, L, mL, m, box, pack
            // money: integer centavos (see pos-domain)
            $table->unsignedBigInteger('price');                  // selling price, VAT-inclusive when vatable
            $table->unsignedBigInteger('cost')->nullable();       // last / standard unit cost
            $table->string('tax_class', 20)->default('vatable');  // vatable | exempt | zero_rated
            $table->boolean('sc_pwd_eligible')->default(false);   // senior / PWD discount applies
            $table->boolean('track_stock')->default(true);        // false for services
            $table->decimal('reorder_level', 12, 3)->default(0);  // low-stock alert at or below this
            $table->boolean('active')->default(true);
            $table->index('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
