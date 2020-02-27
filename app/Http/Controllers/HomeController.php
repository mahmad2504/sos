<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Utility;
use Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Widgets\MilestoneController;
use App\ProjectTree;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function olddashboard()
    {
		$user = Auth::user();
		$admin = 0;
		$projects = $user->projects()->get();
		if($user->role == 'admin')
			return redirect('/admin');
		return view('home',compact('projects','user','admin'));
	}
	public function showChangePasswordForm()
	{
        return view('auth.changepassword');
    }
	public function changePassword(Request $request)
	{
        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            // The passwords matches
            return redirect()->back()->with("error","Your current password does not matches with the password you provided. Please try again.");
        }
        if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
            //Current password and new password are same
            return redirect()->back()->with("error","New Password cannot be same as your current password. Please choose a different password.");
        }
        $validatedData = $request->validate([
            'current-password' => 'required',
            'new-password' => 'required|string|min:6|confirmed',
        ]);
        //Change Password
        $user = Auth::user();
        $user->password = bcrypt($request->get('new-password'));
        $user->save();
        return redirect()->back()->with("success","Password changed successfully !");
    }
	public function GetData(Request $request)
	{
		$admin=0;
		$loggeduser = Auth::user();
		if($loggeduser == null) // Public
		{
			if($request->username == null)
				return redirect()->route('login');
			else
			{
				$user=User::where('name',$request->username)->first();
				if($user == null)
				{
					$returnData = array(
					'status' => 'error',
					'message' => 'Resource Not found'
					);
					return Response::json($returnData, 500);
				}
			}
		}
		else // someone is loggedin
		{
			if($loggeduser->name == 'admin')
			{
				$admin=1;
				if($request->username == null) 
					return redirect()->route('adminhome');
				else
				{
					$user=User::where('name',$request->username)->first();
					if($user == null)
					{
						{
							$returnData = array(
							'status' => 'error',
							'message' => 'Account Not found'
							);
							return Response::json($returnData, 500);
						}
					}
				}
			}
			else
			{
				if($request->username == null)
				{
					$admin = 1;
					$user = $loggeduser;
				}
				else
				{
					$user=User::where('name',$request->username)->first();
					if($user == null)
					{
						{
							$returnData = array(
							'status' => 'error',
							'message' => 'Account Not found'
							);
							return Response::json($returnData, 500);
						}
					}	
					if($user->id == $loggeduser->id)
						$admin = 1;
				}
			}
				
		}
		
		//$states = ['ACTIVE','PAUSED','CANCELLED'];
		//$projects = $user->projects()->get()->whereIn('state',$states);
		if($admin == 1)
			$projects = $user->projects()->where('archive',0)->get();
		else
			$projects = $user->projects()->where('archive',0)->where('visible','true')->get();
		
		
		foreach($projects as $project)
		{
			$projecttree = new ProjectTree($project);
			$project->jiraurl = Utility::GetJiraURL($project);
			$ms = new MilestoneController();
			$status = $ms->GetStatus($project,"1");
			$burnupdata = $projecttree->GetBurnUpData($projecttree->tree);
			
			if($status != null)
			{
	
				if($burnupdata != null)
				{
					$status['cv'] = $burnupdata->cv;
					$status['rv'] = $burnupdata->rv;
				}
				$project->status = $status;
			}
		}
		$filter = $request->filter;
		$jiraservers = config('jira.servers');
		foreach($jiraservers as &$server)
		{
			 unset($server["username"]);
			 unset($server["password"]);
		}
		return $projects;
		
    }
	public function index(Request $request)
	{
		//header("Cache-Control: no-store, must-revalidate, max-age=0");
		//header("Pragma: no-cache");

		$admin=0;
		$loggeduser = Auth::user();
		if($loggeduser == null) // Public
		{
			if($request->username == null)
				return redirect()->route('login');
			else
			{
				$user=User::where('name',$request->username)->first();
				
				if($user == null)
					abort(403, 'Account Not Found');
			}
		}
		else // someone is loggedin
		{
			if($loggeduser->name == 'admin')
			{
				$admin=1;
				if($request->username == null) 
					return redirect()->route('adminhome');
				else
				{
					$user=User::where('name',$request->username)->first();
					if($user == null)
						abort(403, 'Account Not Found');
				}
			}
			else
			{
				if($request->username == null)
				{
					$admin = 1;
					$user = $loggeduser;
				}
				else
				{
					$user=User::where('name',$request->username)->first();
					if($user == null)
						abort(403, 'Account Not Found');
						
					if($user->id == $loggeduser->id)
						$admin = 1;
				}
			}
				
		}
		
		//$states = ['ACTIVE','PAUSED','CANCELLED'];
		//$projects = $user->projects()->get()->whereIn('state',$states);
		if($admin == 1)
			$projects = $user->projects()->where('archive',0)->get();
		else
			$projects = $user->projects()->where('archive',0)->where('visible','true')->get();
		foreach($projects as $project)
		{
			$projecttree = new ProjectTree($project);
			$project->jiraurl = Utility::GetJiraURL($project);
			$ms = new MilestoneController();
			$status = $ms->GetStatus($project,"1");
			$burnupdata = $projecttree->GetBurnUpData($projecttree->tree);
			
			if($status != null)
			{
	
				if($burnupdata != null)
				{
					$status['cv'] = $burnupdata->cv;
					$status['rv'] = $burnupdata->rv;
				}
				$project->status = $status;
			}
		}
		$filter = $request->filter;
		$jiraservers = config('jira.servers');
		foreach($jiraservers as &$server)
		{
			 unset($server["username"]);
			 unset($server["password"]);
		}
		return view('home',compact('projects','user','admin','loggeduser','filter','jiraservers'));
    }
	public function programview(Request $request)
	{
		//header("Cache-Control: no-store, must-revalidate, max-age=0");
		//header("Pragma: no-cache");

		$admin=0;
		$loggeduser = Auth::user();
		if($loggeduser == null) // Public
		{
			if($request->username == null)
				return redirect()->route('login');
			else
			{
				$user=User::where('name',$request->username)->first();
				
				if($user == null)
					abort(403, 'Account Not Found');
			}
		}
		else // someone is loggedin
		{
			if($loggeduser->name == 'admin')
			{
				$admin=1;
				if($request->username == null) 
					return redirect()->route('adminhome');
				else
				{
					$user=User::where('name',$request->username)->first();
					if($user == null)
						abort(403, 'Account Not Found');
				}
			}
			else
			{
				if($request->username == null)
				{
					$admin = 1;
					$user = $loggeduser;
				}
				else
				{
					$user=User::where('name',$request->username)->first();
					if($user == null)
						abort(403, 'Account Not Found');
						
					if($user->id == $loggeduser->id)
						$admin = 1;
				}
			}
				
		}
		
		//$states = ['ACTIVE','PAUSED','CANCELLED'];
		//$projects = $user->projects()->get()->whereIn('state',$states);
		if($admin == 1)
			$projects = $user->projects()->where('archive',0)->get();
		else
			$projects = $user->projects()->where('archive',0)->where('visible','true')->get();
		
		
		foreach($projects as $project)
		{
			$projecttree = new ProjectTree($project);
			$project->jiraurl = Utility::GetJiraURL($project);
			$ms = new MilestoneController();
			$status = $ms->GetStatus($project,"1");
			$burnupdata = $projecttree->GetBurnUpData($projecttree->tree);
			
			if($status != null)
			{
	
				if($burnupdata != null)
				{
					$status['cv'] = $burnupdata->cv;
					$status['rv'] = $burnupdata->rv;
				}
				$project->status = $status;
			}
		}
		$filter = $request->filter;
		$jiraservers = config('jira.servers');
		foreach($jiraservers as &$server)
		{
			 unset($server["username"]);
			 unset($server["password"]);
		}
		if($loggeduser != null)
			if($loggeduser->name == 'admin')
				return view('home',compact('projects','user','admin','loggeduser','filter','jiraservers'));
		return view('programview',compact('projects','user','admin','loggeduser','filter','jiraservers'));
    }
}