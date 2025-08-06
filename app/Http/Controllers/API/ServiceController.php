<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Traits\ApiResponse;
use App\Traits\HasFilter;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use ApiResponse, HasFilter;
    public function index(Request $request)
    {
        return $this->pagination('Services retrieved successfully', $this->search(Service::published(), $request)->paginate($this->limit($request)));
    }

    public function questions(string $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return $this->error('Service not found');
        }
        //my code
        return $this->ok('Questions retrieved successfully', [
            'service_id' => $service->id,
            'service' => $service->title,
            'questions' => $service->load('questions.answers')->questions
        ]);
        //Rasels code
        // return $this->ok('Questions retrieved successfully', $service->load('questions.answers')->questions);
    }

    public function skills(string $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return $this->error('Service not found');
        }

        return $this->ok('Skills retrieved successfully', $service->load('skills')->skills);
    }
}
