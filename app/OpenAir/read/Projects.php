<?php
/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

class Command_ReadProjectByName extends ReadCommand
{
	function __construct($name) 
	{
		$this->_name = $name;
		$this->type = 'Project';
		$this->method = 'equal to';
		$this->limit = 1;
	}
	
	function _buildRequest($dom)
	{
		$read = parent::_buildDefaults($dom);
	
		$project = $dom->createElement('Project');
		$name = $dom->createElement('name',$this->_name);
		
		$project->appendChild($name);
		$read->appendChild($project);
		
		return $read;
    }
	function toString()
	{
		if(parent::toString()==-1)
			return;
		echo "---------------Project (name=$this->_name, limit=$this->limit) --------------".EOL;
		
		$ids = $this->id;
		$names = $this->name;
		for($i=0;$i<count($ids);$i++)
			echo $ids[$i]." ".$names[$i].EOL;

	}
}
class Command_ReadProjects extends ReadCommand
{
	function __construct($limit=1) 
	{
		$this->type = 'Project';
		$this->method = 'all';
		$this->limit = $limit;

	}
	function _buildRequest($dom)
	{
		return $this->_buildDefaults($dom);

    }
	function toString()
	{
		if(parent::toString()==-1)
			return;
		
		echo "---------------Project (all, limit=$this->limit) --------------".EOL;
		
		$ids = $this->id;
		$names = $this->name;
		for($i=0;$i<count($ids);$i++)
			echo $ids[$i]." ".$names[$i].EOL;
	}
}
class Command_ReadProjectById extends ReadCommand
{
	function __construct($id) 
	{
		$this->_id = $id;
		$this->type = 'Project';
		$this->method = 'equal to';
		$this->limit = 1;
		
	}
	function _buildRequest($dom)
	{
		$read = parent::_buildDefaults($dom);
	
		$project = $dom->createElement('Project');
		$id = $dom->createElement('id',$this->_id);
		
		$project->appendChild($id);
		$read->appendChild($project);
		
		return $read;
    }
	function toString()
	{
		if(parent::toString()==-1)
			return;
		
		echo "---------------Project (id=$this->_id, limit=$this->limit) --------------".EOL;
		
		$ids = $this->id;
		$names = $this->name;
		for($i=0;$i<count($ids);$i++)
			echo $ids[$i]." ".$names[$i].EOL;
	}
}


?>
