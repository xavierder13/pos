<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Inventory is a ledger (pos-domain): every change is a stock_movements row, grouped under a
// stock_documents header (a receipt, an adjustment, a transfer, later a sale). stock_levels is a
// per-location cache of the sum, updated in the same transaction, never edited by hand.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_documents', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();                  // RCV-000001, ADJ-…, TRF-…
            $table->string('type', 20);                              // receipt | adjustment | transfer | opening
            $table->foreignId('location_id')->constrained();         // where stock moved (the source for a transfer)
            $table->foreignId('to_location_id')->nullable()->constrained('locations');
            $table->string('reason', 30)->nullable();                // adjustment: damaged | expired | lost | found | correction | count
            $table->string('supplier')->nullable();                  // receipt: who delivered (until Suppliers exist)
            $table->string('reference', 100)->nullable();            // supplier invoice / DR number
            $table->string('note', 1000)->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            $table->index(['type', 'created_at']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->decimal('quantity', 12, 3);                      // signed: + in, − out
            $table->decimal('balance_after', 12, 3);                 // on hand at this location after the move
            $table->unsignedBigInteger('unit_cost')->nullable();     // centavos (receipts)
            $table->timestamp('created_at')->useCurrent();
            $table->index(['product_id', 'location_id', 'id']);
            $table->index(['location_id', 'created_at']);
        });

        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_documents');
    }
};
