<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    function __get($field)
	{
		switch($field)
		{
			case 'isloggedin':
				$isloggedin = Auth::check();
				if($isloggedin)
					return 1;
				else
					return 0;
                break;
		}
	}
}
