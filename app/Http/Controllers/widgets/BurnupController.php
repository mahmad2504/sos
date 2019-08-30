<?php

namespace App\Http\Controllers\Widgets;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;
use App\Project;
use Redirect,Response;
use Auth;

use App\Utility;
use App\ProjectTree;

class BurnupController extends Controller
{
    public function Show(Request $request, $user, $project)
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
		$key = $request->key;
		if($key==null)
		{
            $key = (string)1;
        }
		
        $projecttree = new ProjectTree($project);
        
        if(array_key_exists($key,$projecttree->tasks))
             $head = $projecttree->tasks[$key];
        else
            abort(403, 'Key '.$key.' Not Found');
       
       
       
        $isloggedin = Auth::check();
		if($isloggedin)
			$isloggedin = 1;
		else
			$isloggedin = 0;
		$data = $projecttree->GetBurnUpData($head);
		//dd($data);
		return View('widgets.burnup',compact('user','project','isloggedin','data','key'));
	}
	
}
