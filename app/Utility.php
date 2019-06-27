<?php
namespace App;
use Redirect,Response;
class Utility
{
	public static function Error($message)
	{
		return array(
				'status' => 'error',
				'message' => $message
			);	
	}
	public static function GetToday($format)
	{
		//return "2017-08-12";
		return Date($format);
	}
}