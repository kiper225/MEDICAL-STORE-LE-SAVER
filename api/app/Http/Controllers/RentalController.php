<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function myRentals(Request $request)
    {
        $rentals = Rental::with('product')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($rentals);
    }
}
