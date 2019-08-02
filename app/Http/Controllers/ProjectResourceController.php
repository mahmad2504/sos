<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utility;
use App\ProjectResource;
use App\Project;
use App\ProjectTree;
use Auth;
use Redirect,Response;

class ProjectResourceController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
	
	public function updateprojectresource($id,Request $request)
	{
		$presource = ProjectResource::where('id',$id)->first();
		if($presource == null)
			return Utility::Error('Project Resource Not Found');
		$presource->Modify($request->all());
		
		$presource->save();
		return $presource;
	}
	public function deleteprojectresource($id)
	{
		$presource = ProjectResource::where('id',$id)->first();
		if($presource == null)
			return Utility::Error('Project Resource Not Found');
		
		if($presource->delete())
			return $id;
		return Utility::Error('Unknown Error');
	}
	public function Show(Request $request)
	{
		$user = Auth::user();
		if($request->project_id == null)
			abort(403, 'Missing Parameters - ResourceController@Show(project_id)');
		$project = Project::where('id',$request->project_id)->first();
		
		if($project == null)
			abort(403, 'Project Not Found');
		
		$user = $project->user()->first();
		if(($user->id == Auth::user()->id)||(Auth::user()->role=='admin'))
		{
			$presources = $project->resources()->get();
			foreach($presources as $presource)
			{
				$presource->profile = $presource->resource()->first();
			}
			$allcountryinfo = Utility::GetAllCountryInfo();
			$countryinfo = [];
			foreach($allcountryinfo as $timezone=>$value)
			{
				$countryinfo[$value[0]]=$value[1];
			}
			$projecttree = new ProjectTree($project);
			$oa_users = [];
			if(isset($projecttree->tree->oa))
				$oa_users =  $projecttree->tree->oa->users;
			return view('presources',compact('project','user','presources','countryinfo','oa_users'));
		}
		else
			abort(403, 'Unauthorized');
	}
}
