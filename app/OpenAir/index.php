<?php
/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

define('API_KEY','-----');
define('ORGANIZATION','-----');
define('USERNAME','------');
define('PASSWORD','-----');

require_once('/src/includes');

$oa = new OpenAir(API_KEY);
$auth = new Auth(ORGANIZATION,USERNAME,PASSWORD);
$oa->AddAuth($auth);

$user_data = $oa->ReadUsersByProjectId('5031');
$users = array();
foreach($user_data as $user)
{
		echo $user['id']." ".$user['name']."<BR>";
		$users[$user['id']] = $user['name'];
}


$worklogs = $oa->ReadWorkLogsByProjectId('5031');
foreach($worklogs as $worklog)
{
		
		$worklog['username'] = $users[$worklog['userid']];
		echo $worklog['date']." ".$worklog['username']." ".$worklog['decimal_hours']."<BR>";
		
}



return;


$h1 = $oa->ReadTasksByProjectId('5031');
$oa->Execute();
$h1->toString();

$h6 = $oa->ReadWorkLogsByProjectTaskId($h1,'id');
$oa->Execute();
$h6->toString();

return;

$h2 = $oa->ReadAssignedUsersByProjectTaskId($h1);
//$h1 = $oa->ReadProjectById('5031');
$oa->Execute();
$h2->toString();
$h3 = $oa->ReadUserById($h2,'userid');
$oa->Execute();
$h3->toString();
return;


//$h1 = $oa->ReadProjectByName('5002|OPP-464 Hypervisor for custom ASIC');
$h2 = $oa->ReadProjectById('6012,24');
$oa->Execute();

echo "<h3>----ReadProjectByName-----</h3>";
$h1->toString('id','name');
echo "<h3>----ReadProjectById-----</h3>";
$h2->toString('id','name');
$h3 = $oa->ReadTasksByProjectId($h2);
$oa->Execute();
echo "<h3>----ReadTasksByProjectId-----</h3>";
$h3->toString('id','projectid','name');

$h4 = $oa->ReadAssignedUsersByProjectTaskId($h3);
$oa->Execute();
echo "<h3>----ReadAssignedUsersByProjectTaskId-----</h3>";
$h4->toString('projecttaskid','userid');
$h5 = $oa->ReadUserById($h4,'userid');
$oa->Execute();
echo "<h3>----ReadUserById-----</h3>";
$data = $h5->toString('id','name','currency');

echo "<h3>----ReadWorkLogsByProjectTaskId-----</h3>";
$h6 = $oa->ReadWorkLogsByProjectTaskId('59484,66242');
$oa->Execute();
$h6->toString('date','userid','decimal_hours');


return;
?>