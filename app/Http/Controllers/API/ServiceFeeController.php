<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ServiceFee;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ServiceFeeController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $serviceFees = ServiceFee::all();
            return $this->ok('Service fees retrieved successfully!', $serviceFees);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
