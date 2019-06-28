<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utility;
use App\User;
use App\Project;
use Auth;
class AdminController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
	public function RestrictAccess()
	{
		$user = Auth::user();
		if($user->role != 'admin')
			abort(403, 'Unautorized');
	}
	public function index()
	{
		$this->RestrictAccess();
		
		$users = User::all();
		return view('adminhome',compact('users'));
		
	}
	public function ShowUserBoard($username)
	{
		$this->RestrictAccess();
		
		$user = User::where('name',$username) -> first();
		$projects  = Project::where('user_id',$user->id) -> get();
		$admin = 1;
		return view('home',compact('projects','user','admin'));
	}
}
