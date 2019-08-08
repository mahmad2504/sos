<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use App\Calendar;
use App\Utility;
use Auth;
use Redirect,Response;
class CalendarController extends Controller
{
	//
	public static function GetCountryList()
	{
		$data = [];
		$countries = Utility::GetAllCountryInfo();
		
		foreach($countries as $timezone=>$info)
		{
			if($info[1] == 'Unknown')
				continue;
				
			$resource = Resource::where('name',$info[0])->first();
			if($resource == null)
			{
				$resource = new Resource();
				$resource->name = $info[0];
				$resource->displayname = $info[1];
				$resource->email  = 'Country';
				$resource->timeZone = $timezone;
				$resource->save();

				$cal =  new Calendar;
				$cal->resource_id = $resource->id;
				$cal->save();
				//$resource->cal = $cal;
			}
			$data[$resource->name] = $resource;
		}
		return $data;
	}
	public function ShowCountryCalendarList()
	{
		// Show list of all Country Calendars
		$user = Auth::user();
		if($user->role != 'admin')
			abort(403, 'Unautorized');

		
		$countrylist = self::GetCountryList();

		//dd($countrylist );
		return view('countrycalendars',compact('countrylist','user'));

	}
	public static function GetcalenarData($resource_name,$localcall=1)
	{
		$resource = Resource::where('name',$resource_name)->first();
		if($resource == null)
		{
			if($localcall)
					return null;
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


		return self::GetcalenarData($request->resource_name,0);

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
		//dd($calendar);
	}
}
