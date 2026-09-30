<?php

namespace App\Enums;

// app/Enums/RentalStatus.php
enum RentalStatus: string
{
    case Reserve = 'reserve';
    case EnCours = 'en_cours';
    case Retourne = 'retourne';
    case EnRetard = 'en_retard';
    case Annule = 'annule';
}