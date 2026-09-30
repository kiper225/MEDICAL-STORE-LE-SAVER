<?php

namespace App\Enums;

// app/Enums/ProductAvailability.php
enum ProductAvailability: string
{
    case Vente = 'vente';
    case Location = 'location';
    case LesDeux = 'les_deux';
}