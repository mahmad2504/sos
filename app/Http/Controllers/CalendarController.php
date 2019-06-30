<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use App\Calendar;
use Redirect,Response;
class CalendarController extends Controller
{
    //
	public function getcalendar(Request $request)
	{
		if($request->resource_name == null)
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'Missing Parameters CalendarController@getcalendar(resource_name)'
			);
			return Response::json($returnData, 500);
		}
		$resource = Resource::where('name',$request->resource_name)->first();
		if($resource == null)
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'Resource Not found'
			);
			return Response::json($returnData, 500);
		}
		$calendar = $resource->calendar()->first();
		if($calendar == null)
		{
			$cal =  new Calendar;
			$cal->resource_id = $resource->id;
			$cal->save();
			return  $cal;
		}
		return  $calendar;
	}
	public function savecalendar(Request $request)
	{
		if($request->id == null)
			return;
		//dd($request->data);
		$calendar = Calendar::updateOrCreate(['id' => $request->id],
                    ['resource_id' => $request->resource_id, 
					 'data' => json_encode($request->data)
					]);
		dd($calendar);
	}
}
