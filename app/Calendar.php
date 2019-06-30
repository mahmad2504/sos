<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
  
	protected $fillable = [
        'resource_id','data'
    ];
    //
	public function __construct()
	{
		$this->data = '[]';
	}

	
	public function resource()
	{
		return $this->belongsTo(Resource::Class);
	}
}
