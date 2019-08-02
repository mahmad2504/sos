<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use App\Calendar;
use App\Tj;
use App\Project;
use App\ProjectTree;
use App\OA;
use Redirect,Response;
class MilestoneController extends Controller
{
    //
	public function Show($projectid)
	{
		 return view('milestone');
	}
	
}
