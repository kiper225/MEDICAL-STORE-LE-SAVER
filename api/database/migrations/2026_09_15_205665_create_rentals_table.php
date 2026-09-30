<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\RentalStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();

            $table->date('date_debut');
            $table->date('date_fin');
            $table->date('date_retour_effective')->nullable();

            $table->string('statut')->default(RentalStatus::Reserve->value);

            $table->decimal('montant_caution', 10, 2)->nullable();
            $table->decimal('montant_total', 10, 2);

            $table->timestamps();

            $table->index(['product_id', 'date_debut', 'date_fin']); // pour vérifier la disponibilité rapidement
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
