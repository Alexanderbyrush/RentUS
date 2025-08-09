<?php

namespace App\Http\Controllers;

use App\Models\Contract;

use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::included()->filter()->sort()->getOrPaginate();
        return response()->json($contracts);
    }

    public function show(Contract $contract)
    {
        //
    }

    public function edit(Contract $contract)
    {
        //
    }

    public function update(Request $request, Contract $contract)
    {
        //
    }

    public function destroy(Contract $contract)
    {
        //
    }
}
