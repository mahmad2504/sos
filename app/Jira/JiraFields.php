<?php
namespace App\Jira;
class JiraFields
{
	private $customfields=[];
	private $fields=['key','summary','duedate','fixVersions','assignee','reporter','status','statuscategory','summary'];
	function __construct()
	{
		if(!file_exists("customefields.json"))
		{
			echo "Custom Field Mapping Not Found\n";
			echo "Run command php artisan configure:customfields\n";
			exit();
		}
		$customfields = json_decode(file_get_contents("customefields.json"));
		foreach($customfields as $variable_name=>$customfield)
		{
			$this->customfields[$variable_name]=$customfield->id;
		}
	}
	function Custom()
	{
		return $this->customfields;
	}
	function Standard()
	{
		return $this->fields;
	}
	function __get($prop)
	{
		if(isset($this->customfields[$prop]))
			return $this->customfields[$prop];
	}
}
