@extends('layouts.app')
@section('csslinks')
@endsection
@section('style')
@endsection
@section('content')
	<div class="container">
		<ul class="list-group">
		@foreach($users as $user)
			@if($user->name != 'admin')
				<li class="list-group-item">
					<a href="/admin/{{$user->name}}">Program Dashboard {{ $user->name }}</a>
				</li>
			@endif
		@endforeach
	</div>
@endsection
@section('script')
$(document).ready(function()
{
	console.log("Loading admin Home Page");
	ShowNavBar();
	
});
@endsection