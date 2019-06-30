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
    	$users = User::where('name',$user)->get();
    	if(count($users)==0)
    	{
			abort(403, 'Account Not Found');
    	}
    	$projects = Project::where('name',$project)->get();
    	if(count($projects)==0)
    	{
    		abort(403, 'Project Not Found');
    	}
    	$user = $users[0];
    	$project = $projects[0];
    	return View('dashboard',compact('user','project'));

    }
}