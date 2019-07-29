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
class OpenAirController extends Controller
{
	public function GetResources(Request $request)
	{
		if($request->project_id == null)
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'Project Id missing'
			);
			return Response::json($returnData, 500);
		}
		$project = Project::where('id',$request->project_id)->first();
		if($project == null)
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'Project Not Found'
			);
			return Response::json($returnData, 500);
		}
		$projecttree = new ProjectTree($project);
		if(isset($projecttree->tree->oa))
		{
			return $projecttree->tree->oa->users;
		}
		return [];
	}
}
