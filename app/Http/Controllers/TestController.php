<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use App\Calendar;
use App\Tj;
use App\Project;
use App\ProjectTree;
use Redirect,Response;
class TestController extends Controller
{
    //
	public function TJTest($projectid)
	{
		$project = Project::where('id',$projectid)->first();
		$projecttree = new ProjectTree($project);
		//dd($projecttree);
		$tj =  new Tj($projecttree);
		$tj->Execute();
	}
	
}
