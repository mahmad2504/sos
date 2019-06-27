<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
	public function create($request)
	{
		
	}
	public function user()
	{
		$this->belongsTo('App\User');
	}
}
