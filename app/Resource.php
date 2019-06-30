<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Resource;

class Resource extends Model
{
	protected $fillable = [
        'name', 'displayname','email', 'timezone'
    ];
    function __construct()
	{
		 
	}
	public function Modify(Resource $resource)
	{
		foreach($this->fillable as $field)
		{
			if($resource->$field != null)
				$this->$field = $resource->$field;
		}
		$this->save();
	}
	public function projects()
    {
        return $this->hasMany(ProjectResource::class);
    }
	public function calendar()
    {
        return $this->hasOne(Calendar::class);
    }

}
