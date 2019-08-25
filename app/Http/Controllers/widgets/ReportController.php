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
    public function ShowWeeklyReport($user, $project, $year=null, $weekno=null)
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
        if(($year == null)||($year=='default'))
        {
            $year = date("Y");

        }
        if(($weekno == null)||($weekno=='default'))
        {
            $weekno = date("W");
            $weekno =  (int)$weekno;
        }
        
        $projecttree = new ProjectTree($project);
        $wlogs = $projecttree->GetWeeklyWorkLog();
        
        if(!array_key_exists($year,$wlogs))
            abort(403, 'No Report Found For year '.$year);
        if(!array_key_exists($weekno,$wlogs[$year]))
            abort(403, 'No Report Found For week '.$weekno." of ".$year);

        $worklogs_by_task = [];
        $data = [];
        foreach($wlogs as $year=>$wlog)
        {
            foreach($wlog as $week=>$wlg)
            {
                $data['lists'][$year][$week] = $week;
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
       
        $isloggedin = Auth::check();
		if($isloggedin)
			$isloggedin = 1;
		else
            $isloggedin = 0;
		return View('widgets.report',compact('user','project','isloggedin','data'));
    }
    function GetWeeklyReport($user, $project, $year=null, $weekno=null)
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
        if(($year == null)||($year=='default'))
        {
            $year = date("Y");

        }
       
        if(($weekno == null)||($weekno=='default'))
        {
            $weekno = date("W");
            $weekno =  (int)$weekno;
        }
        
        $projecttree = new ProjectTree($project);
        $wlogs = $projecttree->GetWeeklyWorkLog();
        
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
