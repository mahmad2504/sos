<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectResource extends Model
{
    //
	protected $fillable = [
        'project_id', 'resource_id','efficiency', 'cost','team','cc','active'
    ];
    //
	public function __construct()
	{
		$this->efficiency = 100;
		$this->cost = 10;
		$this->team = null;
		$this->cc = '';
		$this->active = 1;
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
	public function resource()
    {
        return $this->belongsTo('App\Resource');
    }
	public function project()
    {
        return $this->belongsTo('App\Project');
    }
}
