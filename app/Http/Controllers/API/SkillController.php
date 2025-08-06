<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    use ApiResponse;

    public function index(){
        $skills = Skill::where('type','default')->select(['id', 'name'])->get();
        return $this->success('Skill Retrieved Successfully',$skills);
    }
}
