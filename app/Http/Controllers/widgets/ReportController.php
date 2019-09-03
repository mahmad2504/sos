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

class ReportController extends Controller
{
    public function ShowWeeklyReport(Request $request,$user, $project)
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
        $year = $request->year;
        $weekno = $request->weekno;
        $key = $request->key;

        if($year == null)
        {
            $year = date("Y");

        }
        if($weekno == null)
        {
            $weekno = date("W");
            $weekno =  (int)$weekno;
        }
        if($key == null)
        {
            $key = (string)1;
        }
        
        $projecttree = new ProjectTree($project);
        
        if(array_key_exists($key,$projecttree->tasks))
             $head = $projecttree->tasks[$key];
        else
            abort(403, 'Key '.$key.' Not Found');

        $wlogs = $projecttree->GetWeeklyWorkLog($head);
       
       // if(!array_key_exists($year,$wlogs))
       //     abort(403, 'No Report Found For year '.$year);
       // if(!array_key_exists($weekno,$wlogs[$year]))
       //     abort(403, 'No Report Found For week '.$weekno." of ".$year);

        $data = [];
        foreach($wlogs as $year1=>$wlog)
        {
            foreach($wlog as $week1=>$wlg)
            {
                $data['lists'][$year1][$week1] = $week1;
            }
        }
        
        if((!array_key_exists($year,$wlogs))||(!array_key_exists($weekno,$wlogs[$year])))
        {
           // $data['worklogs'] = [];
           $data['lists'][$year][$weekno] = $weekno;
        }
        else
        {
            
            $data['year']= $year;
            $data['week']= $weekno;
            foreach($wlogs[$year][$weekno] as $date=>$wlgs)
            {
                foreach($wlgs as $wlg)
                {
                    $data['worklogs'][$wlg->jira][$date] = $wlg;
                }
            }
        }
       
        $isloggedin = Auth::check();
		if($isloggedin)
			$isloggedin = 1;
		else
            $isloggedin = 0;
		return View('widgets.report',compact('key','user','project','isloggedin','data'));
    }
    function GetWeeklyReport(Request $request,$user, $project)
    {
        
        $user = User::where('name',$user)->first();
    	if($user==null)
    	{
            $returnData = array(
				'status' => 'error',
				'message' => 'Invalid User'
			);
			return Response::json($returnData, 500);
    	}
		$project = $user->projects()->where('name',$project)->first();
		if($project==null)
    	{
            $returnData = array(
				'status' => 'error',
				'message' => 'Invalid Project'
			);
			return Response::json($returnData, 500);
        }
        $year = $request->year;
        $weekno = $request->weekno;
        $key = $request->key;

        if($year == null)
        {
            $year = date("Y");

        }
       
        if($weekno == null)
        {
            $weekno = date("W");
            $weekno =  (int)$weekno;
        }
        if($key == null)
        {
            $key = (string)1;
        }
        
        $projecttree = new ProjectTree($project);
        if(array_key_exists($key,$projecttree->tasks))
             $head = $projecttree->tasks[$key];
        else
        {
            $returnData = array(
				'status' => 'error',
				'message' => 'Invalid Key'
			);
			return Response::json($returnData, 500);
        }

        $wlogs = $projecttree->GetWeeklyWorkLog($head);
        
        if(!array_key_exists($year,$wlogs))
        {
            $returnData = array(
				'status' => 'error',
				'message' => 'No Report Found For year '.$year
			);
			return Response::json($returnData, 500);
        }
        if(!array_key_exists($weekno,$wlogs[$year]))
        {
            $returnData = array(
				'status' => 'error',
				'message' => 'No Report Found For week '.$weekno." of ".$year
			);
			return Response::json($returnData, 500);
        }
       
        $data = [];
        foreach($wlogs as $yar=>$wlog)
        {
            foreach($wlog as $week=>$wlg)
            {
                $data['lists'][$yar][$week] = $week;
            }
        }
        $data['year']= $year;
        $data['week']= $weekno;
       
        foreach($wlogs[$year][$weekno] as $date=>$wlgs)
        {
            foreach($wlgs as $wlg)
            {
                $data['worklogs'][$wlg->jira][$date] = $wlg;
              
            }
        }
        return $data;
    }
}
