<?php
/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/
namespace App\OpenAir;
class Auth
{
	private $company;
	private $user;
	private $password;
	
	function __construct($company,$user,$password)
	{
		$this->company = $company;
		$this->user = $user;
		$this->password = $password;
	}
	function _buildRequest($dom)
	{
		$eauth = $dom->createElement('Auth');
		$elogin = $dom->createElement('Login');
		$ecompany = $dom->createElement('company', $this->company);
		$euser = $dom->createElement('user', $this->user);
		$epassword = $dom->createElement('password', $this->password);
		$elogin->appendChild($ecompany);
		$elogin->appendChild($euser);
		$elogin->appendChild($epassword);
		$eauth->appendChild($elogin);

		return $eauth;
    }
}
?>