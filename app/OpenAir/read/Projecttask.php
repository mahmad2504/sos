<?php 
/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

class Command_ReadTasks extends ReadCommand
{
	function __construct($limit=1) 
	{
		$this->type = 'Projecttask';
		$this->method = 'all';
		$this->limit = $limit;
	}
	function _buildRequest($dom)
	{
		$read = parent::_buildDefaults($dom);
		return $read;
    }
	function toString()
	{
		if(parent::toString()==-1)
			return;
		echo "---------------Project Tasks (all, limit=$this->limit) --------------".EOL;
		foreach($this->result as $result)
		{
			echo $result['id']." ".$result['name']."<br>";
		}
	}
}

class Command_ReadTasksByProjectId extends ReadCommand
{
	function __construct($projectid,$limit=1) 
	{
		$this->type = 'Projecttask';
		$this->method = 'equal to';
		$this->limit = $limit;
		$this->_projectid = $projectid;
	}
	function _buildRequest($dom)
	{
		$read = parent::_buildDefaults($dom);
	
		$project = $dom->createElement('Projecttask');
		$id = $dom->createElement('projectid',$this->_projectid);
		
		$project->appendChild($id);
		$read->appendChild($project);
		return $read;
    }
	function toString()
	{
		if(parent::toString()==-1)
			return;
		echo "---------------Project Tasks (projectid=$this->_projectid, limit=$this->limit) --------------".EOL;
		
		$ids = $this->id;
		$pids = $this->projectid;
		$names = $this->name;

		for($i=0;$i<count($ids);$i++)
			echo "id=".$ids[$i]." pid=".$pids[$i]." task=".$names[$i].EOL;
		
	}
}
?>