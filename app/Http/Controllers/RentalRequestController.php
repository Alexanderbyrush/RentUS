<?php

namespace App\Http\Controllers;

use App\Models\RentalRequest;
use Illuminate\Http\Request;

class RentalRequestController extends Controller
{
    public function index()
    {
        $rentalRequests = RentalRequest::included()->filter()->sort()->getOrPaginate();
        return response()->json($rentalRequests);
    }

    public function show(RentalRequest $rentalRequest)
    {
        //
    }

    public function edit(RentalRequest $rentalRequest)
    {
        //
    }

    public function update(Request $request, RentalRequest $rentalRequest)
    {
        //
    }

    public function destroy(RentalRequest $rentalRequest)
    {
        //
    }
}
