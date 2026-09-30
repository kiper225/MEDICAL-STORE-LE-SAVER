<?php

use App\Enums\ProductAvailability;
use App\Enums\ProductStatus;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique()->nullable();

            // Disponibilité : vente / location / les_deux
            $table->string('type_disponibilite')->default(ProductAvailability::Vente->value);

            $table->boolean('necessite_installation')->default(false);
            $table->boolean('necessite_certification')->default(false);

            // Vente
            $table->decimal('prix_vente', 10, 2)->nullable();

            // Location
            $table->decimal('prix_location_jour', 10, 2)->nullable();
            $table->decimal('prix_location_semaine', 10, 2)->nullable();
            $table->decimal('prix_location_mois', 10, 2)->nullable();
            $table->decimal('caution_location', 10, 2)->nullable();

            // Caractéristiques physiques
            $table->decimal('poids', 8, 2)->nullable();
            $table->string('dimensions')->nullable();

            $table->string('statut')->default(ProductStatus::Actif->value);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
