<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        try {
            $countries = Country::orderBy('name')->get();
            return response()->json($countries);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch countries',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $country = Country::with(['workers', 'clients'])->findOrFail($id);
            return response()->json($country);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Country not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
