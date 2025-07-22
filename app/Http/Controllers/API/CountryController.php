<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        // Return all countries as JSON
        return Country::select('id', 'shotname', 'name', 'phonecode')->orderBy('name')->get();
    }
}
