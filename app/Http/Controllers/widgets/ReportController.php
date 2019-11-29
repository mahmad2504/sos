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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
	private $row=1;
	private function PutTaskData($sheet,$task)
	{
		$row = $this->row++;
		$sheet->setCellValue('A'.$row, floatval($task->extid));
		if($task->extid != $task->key)
		$sheet->setCellValue('B'.$row, $task->key);
		$sheet->setCellValue('C'.$row, $task->isparent);
	
		$spaces = ' ';
		for($i=2;$i<$task->level;$i++)
		{
			$spaces .= "          ";
		}
		$sheet->setCellValue('D'.$row, $spaces.$task->summary);
		if($task->duplicate == 0)
		{
			$sheet->setCellValue('E'.$row,'-');
			if(isset($task->otimeestimate)&&$task->duplicate==0)
			{
				if($task->otimeestimate == '')
					$sheet->setCellValue('E'.$row,'-');
				else
					$sheet->setCellValue('E'.$row,$task->otimeestimate);
			}
			$sheet->setCellValue('F'.$row,'-');
			if(isset($task->ostorypoints)&&$task->duplicate==0)
			{
				$sheet->setCellValue('F'.$row,$task->ostorypoints);
			}
			$sheet->setCellValue('G'.$row,$task->ostatus);
		}
		else
		{
			$sheet->setCellValue('E'.$row,'Duplicate');
			$sheet->setCellValue('G'.$row,$task->ostatus);
		}
			
		foreach($task->children as $child)
		{
			$this->PutTaskData($sheet,$child);
		}
	}
	public function Downloadexcel($user,$project)
	{
		$user = User::where('name',$user)->first();
		if($user==null)
    	{
    		abort(403, 'Account Not Found');
    	}
		if(ctype_digit($project))
			$project = $user->projects()->where('id',$project)->first();
		else
			$project = $user->projects()->where('name',$project)->first();
		if($project==null)
    	{
    		abort(403, 'Project Not Found');
        }
		$isloggedin = Auth::check();
		if($isloggedin)
			$isloggedin = 1;
		else
            $isloggedin = 0;
		
		$projecttree = new ProjectTree($project);
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$row = $this->row++;
		$sheet->setCellValue('A'.$row,'ID');
		$sheet->setCellValue('B'.$row,'Jira');
		$sheet->setCellValue('C'.$row,'Parent');
		$sheet->setCellValue('D'.$row,'Name');
		$sheet->setCellValue('E'.$row,'Time');
		$sheet->setCellValue('F'.$row,'Story');
		
		foreach($projecttree->tree->children as $child)
		{
			$this->PutTaskData($sheet,$child);
		}
		
		$writer = new Xlsx($spreadsheet);
		$writer->save('hello_world.xlsx');
		dd($projecttree);
	}
	
	public function ShowDocument(Request $request,$user,$project)
	{
		$user = User::where('name',$user)->first();
		if($user==null)
    	{
    		abort(403, 'Account Not Found');
    	}
		if(ctype_digit($project))
			$project = $user->projects()->where('id',$project)->first();
		else
			$project = $user->projects()->where('name',$project)->first();
		if($project==null)
    	{
    		abort(403, 'Project Not Found');
        }
		$isloggedin = Auth::check();
		if($isloggedin)
			$isloggedin = 1;
		else
            $isloggedin = 0;
		
		$projecttree = new ProjectTree($project);
		$fixversion = $request->fixversion;
		if($fixversion != null)
		{
			if($fixversion == 'all')
			{
				session()->forget('widget_document_fixversion');
				$fixversion = null;
			}
			else
				session(['widget_document_fixversion' => $fixversion]);
		}
		else
		{
			$fixversion = session('widget_document_fixversion');
			
		}
		foreach($projecttree->tasks as $task)
		{
			if($task->_summary == 'Product Requirements')
			{
				return View('widgets.document',compact('user','project','task','isloggedin','fixversion'));
			}
		}
		abort(403, 'Product Requirements Tag Not Found');
		//return View('widgets.document',compact('user','project','projecttree','isloggedin','data'));
	}
	
    public function ShowWeeklyReport(Request $request,$user, $project)
    {
        $user = User::where('name',$user)->first();
    	if($user==null)
    	{
    		abort(403, 'Account Not Found');
    	}
		if(ctype_digit($project))
			$project = $user->projects()->where('id',$project)->first();
		else
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
		if(ctype_digit($project))
			$project = $user->projects()->where('id',$project)->first();
		else
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
