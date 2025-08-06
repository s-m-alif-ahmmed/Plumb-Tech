<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    use ApiResponse;
    public function index()
    {
        return $this->success('Villages retrieved', Village::select(['id', 'name'])->get());
    }
}
