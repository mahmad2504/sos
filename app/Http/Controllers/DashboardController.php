<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Project;
use App\ProjectTree;
use App\Jira;
use App\Utility;
use Redirect,Response;
use Auth;

class DashboardController extends Controller
{
    //
    public function Show($user,$project)
    {
    	$user = User::where('name',$user)->first();
    	if($user==null)
    	{
			abort(403, 'Account Not Found');
    	}
		
		$project = $user->projects()->where('name',$project)->first();
		if($project==null)
    	{
    		abort(403, 'Project Not Found');
    	}
    	//dd($project);
    	return View('dashboard',compact('user','project'));

    }
}