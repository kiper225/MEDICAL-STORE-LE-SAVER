<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\InstallationStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('installations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rental_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('technicien_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('adresse_installation');
            $table->date('date_prevue');
            $table->string('creneau_horaire'); // ex: "09:00-11:00"

            $table->string('statut')->default(InstallationStatus::Planifiee->value);

            $table->string('signature_client')->nullable(); // url ou chemin fichier
            $table->text('rapport_intervention')->nullable();

            $table->timestamps();

            $table->index('technicien_id');
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
