<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utility;
class SyncController extends Controller
{
	public function sync(Request $request)
	{
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
		set_time_limit(300);
		Utility::ConsoleLog(time(),$request->projectid);
		
	}
    //
}
