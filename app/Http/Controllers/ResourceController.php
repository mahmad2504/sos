<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utility;
use App\ProjectResource;
use App\Project;
use Redirect,Response;

class ResourceController extends Controller
{
	public function Show(Request $request)
	{
		if($request->project_id == null)
			abort(403, 'Missing Parameters - ResourceController@Show(project_id)');
		$project = Project::where('id',$request->project_id)->first();
		
		if($project == null)
			abort(403, 'Project Not Found');
		
		$user = $project->user()->first();
		$presources = $project->resources()->get();
		foreach($presources as $presource)
		{
			$presource->profile = $presource->resource()->first();
		}
		return view('presources',compact('project','user','presources'));
		//$presources = ProjectResource::where('project_id',$request->project_id)->get();
		//foreach($presources as $presource)
		//{
		//	$presource->details = $presource->resource()->first();
		//}
		//return view('presources',compact('project','user','presources'));
		//return Response::json($presources);
	}
}
