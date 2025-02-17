<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Http\Requests\StoreAddressRequest;

class AddressController extends Controller
{
    public function store(StoreAddressRequest $request)
    {

        $existingAddress = Address::where('street', $request->street)
            ->where('number', $request->number)
            ->where('city', $request->city)
            ->where('cp', $request->cp)
            ->first();

        if ($existingAddress) {
            return response()->json([
                'message' => 'Address already exists',
                'address' => $existingAddress
            ], 200);
        }


        $address = new Address();
        $address->street = $request->street;
        $address->number = $request->number;
        $address->city = $request->city;
        $address->cp = $request->cp;
        $address->save();

        return response()->json([
            'message' => 'Address created successfully',
            'address' => $address
        ], 201);
    }

    public function show($id)
    {
        $address = Address::where('id', $id)->first();

        if (!$address) {
            return response()->json([
                'error' => 'Address not found'
            ], 404);
        }

        return response()->json($address);
    }

    public function check(StoreAddressRequest $request)
    {
        $address = Address::where('street', $request->street)
            ->where('number', $request->number)
            ->where('city', $request->city)
            ->where('cp', $request->cp)
            ->first();

        if (!$address) {
            return response()->json([
                'message' => 'Address not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Address found',
            'address' => $address
        ]);
    }
}
