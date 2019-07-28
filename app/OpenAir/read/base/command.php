<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

class  ReadCommand
{
	public $method='all';
	public $filter = null;
	public $limit=1;
	public $type='';
	public $result;
	public $cmdtype = 'oa_read_command';//constant

	function __construct() 
	{
	}
	public function __get($name)
	{
		$data = array();
		if(is_string($this->result))
		{
			return $data;
		}
		if(is_array($this->result))
		{
			if( array_key_exists($name,$this->result) )
				$data[] = $this->result[$name];
			else
			{
				foreach($this->result as $result)
				{
					if( array_key_exists($name,$result) )
						$data[] =  $result[$name];
				}	
			}
		}
		else
		{
			foreach($this->result as $result)
			{
				if( array_key_exists($name,$result) )
					$data[] =  $result[$name];
			}
		}
		return $data;
	}
	function _buildDefaults($dom)
	{
		$read = $dom->createElement('Read');	
		$type = $dom->createAttribute('type');
        $type->value = $this->type;
		
		$method = $dom->createAttribute('method');
		$method->value = $this->method;
		
		$limit = $dom->createAttribute('limit');
        $limit->value = $this->limit;
		
		$read->appendChild($type);
		$read->appendChild($method);
		$read->appendChild($limit);
		
		if($this->filter != null)
		{
			$filter = $dom->createAttribute('filter');
			$filter->value = $this->filter;
			$read->appendChild($filter);
		}
		
		return $read;
	}
	function _setResults($array)
	{
		$this->result = $array;
	}
	function _buildRequest($dom)
	{
		return $this->_buildDefaults($dom);

    }
     function toString()
    {
	   if(!is_array($this->result))
	   {
			echo "Failed with error code ".$this->result;
			return -1;
	    }
	    return 0;
	}	
}
?>