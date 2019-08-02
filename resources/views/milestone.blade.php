@extends('layouts.app')
@section('csslinks')

@endsection
@section('style')
.progress {height: 10px;}
@endsection
@section('content')
<div id="container" style="width:90%; margin-left: auto; margin-right: auto;" class="center">
	<div id="table1">
	</div>
	
	<div id="table2">
	</div>
</div>

@endsection
@section('script')

$(document).ready(function()
{
	console.log("Loading Resource Page");
	var tableData = [
		{id:1, name:"Billy Bob f1", age:"12", gender:"male", height:1, col:"red", dob:"", cheese:1},
		{id:2, name:"Mary May f2", age:"1", gender:"female", height:2, col:"blue", dob:"14/05/1982", cheese:true},
	]

	var table = new Tabulator("#table1", {
		data:tableData, //set initial table data
		movableRows: true, //enable user movable rows
		movableRowsReceiver: "add", //add rows when dropped on the table
		movableRowsSender: "delete",
		dataTree:true,
		movableRowsConnectedTables: "#table2",
		columns:[
		    {rowHandle:true, formatter:"handle", headerSort:false, frozen:true, width:30, minWidth:30},
			{title:"Name", field:"name"},
			{title:"Age", field:"age"},
			{title:"Gender", field:"gender"},
			
		],
	});
	
	var tableData2 = [
		{id:1, name:"   Billy Bob", age:"12", gender:"male", height:1, col:"red", dob:"", cheese:1, "_children":
			[
			  {id:2, name:"Mumtaz", age:"1", gender:"female", height:2, col:"blue", dob:"14/05/1982", cheese:true},
			  {id:3, name:"Fouzia", age:"1", gender:"female", height:2, col:"blue", dob:"14/05/1982", cheese:true},
		    ]
		},
		{id:4, name:"Mary May", age:"1", gender:"female", height:2, col:"blue", dob:"14/05/1982", cheese:true},
	]

	var table = new Tabulator("#table2", {
		 layout:"fitColumns",
		data:tableData2, //set initial table data
		movableRows: true, //enable user movable rows
		movableRowsSender: "delete",
		movableRowsReceiver: "add", //add rows when dropped on the table
		movableRowsConnectedTables: "#table1",
		dataTree:true,
		columns:[
		    {rowHandle:true, formatter:"handle", headerSort:false, frozen:true, width:30, minWidth:30},
			{title:"Name", field:"name"},
			{title:"Age", field:"age"},
			{title:"Gender", field:"gender"},
			{title:"Height", field:"height"},
			{title:"Favourite Color", field:"col"},
			{title:"Date Of Birth", field:"dob"},
			{title:"Cheese Preference", field:"cheese",widthGrow:5},
		],
	});
});
@endsection