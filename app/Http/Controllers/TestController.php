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
class TestController extends Controller
{
    //
	public function ShowTree($projectid)
	{
		$project = Project::where('id',$projectid)->first();
		if($project == null)
		{
			echo "Project id=".$projectid." not found";
			return;
		}
		$projecttree = new ProjectTree($project);
		dd($projecttree);
	}
	public function Test($task)
	{
		if(isset($task->twin))
			echo($task->twin->sch_start);
		for($i=0;$i<count($task->children);$i++)
			$this->Test($task->children[$i]);
	}
	public function TJTest($projectid)
	{
		$project = Project::where('id',$projectid)->first();
		$projecttree = new ProjectTree($project);
		//$this->Test($projecttree->tree);
		//exit();
		//dd($projecttree ->tree);
		$tj =  new Tj($projecttree);
		$tj->Execute();
	}
	public function OATest($projectid)
	{
		$project = Project::where('id',$projectid)->first();
		if($project == null)
		{
			echo "Project id=".$projectid." not found";
			return;
		}
		$projecttree = new ProjectTree($project);
		$oa = new OA($projecttree);
		$oa->sync();
	}
}
