@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/logger.css') }}" />
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.css') }}" />
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.theme.default.css') }}" />
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />

@endsection
@section('style')


.tabulator .tabulator-header .tabulator-col {
	background-color:#DDEFEF !important;
}
.tabulator .tabulator-header {
	background-color:#DDEFEF !important;
}
@endsection
@section('content')

<div style="width:80%;" class="container-fluid">
	<div class="mainpanel">
		<div style="margin-top:20px;" id="table"></div>
	</div>
	
</div>
<script src="{{ asset('js/tabulator.table.js') }}" ></script>
<script src="{{ asset('js/eventsource.min.js') }}" ></script>
<script src="{{ asset('js/msc-script.js') }}" ></script>
<script src="{{ asset('js/logger.js') }}" ></script>
@endsection
@section('script')
var thistoday="{{date("Y-m-d H:i:s")}}";
var admin={{$admin}};
var username = "{{$user->name}}";
var userid = "{{$user->id}}";
var tabledata = @json($projects);
var jiraservers = @json($jiraservers);

function GetRisksIssues(data,type)
{
    count = 0;
	references = [];
	if(data['risksissues'] === undefined)
		return null;
	if(data['risksissues'][type]['Critical'] !==  undefined)
    {
        severity = 'Critical';
    }
	else if(data['risksissues'][type]['High'] !==  undefined)
    {
        severity = 'High';
    }
	else if(data['risksissues'][type]['Medium'] !==  undefined)
    {
        severity = 'Medium';
    }
	else
		return null;
	
    for (var key in data['risksissues'][type][severity]) 
    {
        references[count] = key;
        count++
    }
	obj = null;
    if(references.length > 0)
    {
		obj = {};
        str = references.toString();
		obj.references = str;
		obj.count = references.length;
        obj.type = severity;
    }
	return obj;
}
function GetBlockers(data)
{
    count = 0;
	obj = null;
	blockers = [];
	if(data['risksissues'] === undefined)
		return null;
	if(data['risksissues']['blockers'] ===  undefined)
		return;
	
    for (var key in data['risksissues']['blockers']) 
    {
        blockers[count] = key;
        count++
    }
    if(blockers.length > 0)
    {
		obj = {};
        str = blockers.toString();
		obj.references = str;
		obj.count = blockers.length;
        obj.type = 'Critical';
    }
	return obj;
}

function GetEscalations(data)
{
    count = 0;
	escalations = [];
	obj = null;
	if(data['risksissues'] === undefined)
		return null;
	if(data['risksissues']['escalations'] ===  undefined)
		return;
	
    for (var key in data['risksissues']['escalations']) 
    {
        escalations[count] = key;
        count++
    }
    if(escalations.length > 0)
    {
		obj = {};
        str = escalations.toString();
		obj.references = str;
		obj.count = escalations.length;
        obj.type = 'Critical';
    }
	return obj;
}
function ShowTable()
{
	tabledata = Object.values(tabledata);
	table = new Tabulator("#table", 
	{
		tooltips:true,
		//layout:"fitDataFill",
		
		//persistenceMode:true , 
		//persistentLayout:true,
		//movableColumns: true,
		
		layout:"fitDataStretch",
		//layout:"fitData",
		//layout:"fitColumns",
		data:tabledata, //load data into table
		columns:[
			{title:"Id", field:"id", sorter:"number",visible:false,
				mutator:function(value, data, type, params, component)
				{
					if(data.status !== undefined)
					{
						data.risks = GetRisksIssues(data.status,'risks');
						data.issues = GetRisksIssues(data.status,'issues');
						data.blockers = GetBlockers(data.status);
						data.escalations = GetEscalations(data.status);
					}
					return data.id;
				}
			},
			{title:"Name", field:"name", sorter:"string",width:300,cssClass:"blue-background",
				formatter:function(cell, formatterParams, onRendered)	
				{
					return cell.getValue();
				}
			},
			{title:"Start", field:"sdate", sorter:"string", formatter:"datetime", formatterParams:{
					inputFormat:"YYYY-MM-DD",
					outputFormat:"MMM DD YYYY",
					invalidPlaceholder:"(invalid date)",
				}
			
			},
			{title:"Duedate", field:"edate", sorter:"string", formatter:"datetime", formatterParams:{
					inputFormat:"YYYY-MM-DD",
					outputFormat:"MMM DD YYYY",
					invalidPlaceholder:"(invalid date)",
				}
			},
			{title:"Forecast<br>Finish", field:"status.end", sorter:"string", formatter:"datetime", formatterParams:{
					inputFormat:"YYYY-MM-DD",
					outputFormat:"MMM DD YYYY",
					invalidPlaceholder:"",
				}
			
			},
			{title:"Status", field:"status.status", sorter:"string",
				formatter:function(cell, formatterParams, onRendered)
				{
					return "<img width='80px' src='/images/"+cell.getValue()+".png'></img>"; 

				}
			},
			{title:"Blockers", field:"blockers",headerVertical:"flip",width:10,sorter:"number",
				
				formatter:function(cell, formatterParams, onRendered)	
				{
					tickets = cell.getValue();
					data = cell.getRow().getData();
					if(tickets != null)
					{
						if(tickets.type == 'Critical')
						{
							cell.getElement().style.backgroundColor ="red";	
							color ="white";
						}
						else if(tickets.type == 'Major')
						{
							cell.getElement().style.backgroundColor ="orange";
							color ="black";							
						}
						else 
						{
							cell.getElement().style.backgroundColor ="yellow";	
							color ="black";
						}
						link = data.jiraurl+'/issues/?jql=issue in ('+tickets.references+')';
						return '<a href="'+link+'" title="'+tickets.references+'"><span style="color:'+color+';">'+tickets.count+'</span></a>';
					}
					return cell.getValue();
				},
				sorter:function(a, b, aRow, bRow, column, dir, sorterParams)
				{
					//a, b - the two values being compared
					//aRow, bRow - the row components for the values being compared (useful if you need to access additional fields in the row data for the sort)
					//column - the column component for the column being sorted
					//dir - the direction of the sort ("asc" or "desc")
					//sorterParams - sorterParams object from column definition array
					if(a == null)
						a = 0;
					else
						a = a.count;
					if(b == null)
						b = 0;
					else
						b = b.count;
					return a-b; //you must return the difference between the two values
				}
			},
			{title:"Risks", field:"risks",headerVertical:"flip",width:10,sorter:"number",
					
				formatter:function(cell, formatterParams, onRendered)	
				{
					tickets = cell.getValue();
					data = cell.getRow().getData();
					if(tickets != null)
					{
						if(tickets.type == 'Critical')
						{
							cell.getElement().style.backgroundColor ="red";	
							color ="white";
						}
						else if(tickets.type == 'Major')
						{
							cell.getElement().style.backgroundColor ="orange";
							color ="black";							
						}
						else 
						{
							cell.getElement().style.backgroundColor ="yellow";	
							color ="black";
						}
						link = data.jiraurl+'/issues/?jql=issue in ('+tickets.references+')';
						return '<a href="'+link+'" title="'+tickets.references+'"><span style="color:'+color+';">'+tickets.count+'</span></a>';
					}
					return cell.getValue();
				},
				sorter:function(a, b, aRow, bRow, column, dir, sorterParams)
				{
					//a, b - the two values being compared
					//aRow, bRow - the row components for the values being compared (useful if you need to access additional fields in the row data for the sort)
					//column - the column component for the column being sorted
					//dir - the direction of the sort ("asc" or "desc")
					//sorterParams - sorterParams object from column definition array
					if(a == null)
						a = 0;
					else
						a = a.count;
					if(b == null)
						b = 0;
					else
						b = b.count;
					return a-b; //you must return the difference between the two values
				}
			},
			{title:"Issues", field:"issues",headerVertical:"flip",width:10,sorter:"number",
					
				formatter:function(cell, formatterParams, onRendered)	
				{
					tickets = cell.getValue();
					data = cell.getRow().getData();
					if(tickets != null)
					{
						if(tickets.type == 'Critical')
						{
							cell.getElement().style.backgroundColor ="red";	
							color ="white";
						}
						else if(tickets.type == 'Major')
						{
							cell.getElement().style.backgroundColor ="orange";
							color ="black";							
						}
						else 
						{
							cell.getElement().style.backgroundColor ="yellow";	
							color ="black";
						}
						link = data.jiraurl+'/issues/?jql=issue in ('+tickets.references+')';
						return '<a href="'+link+'" title="'+tickets.references+'"><span style="color:'+color+';">'+tickets.count+'</span></a>';
					}
					return cell.getValue();
				},
				sorter:function(a, b, aRow, bRow, column, dir, sorterParams)
				{
					//a, b - the two values being compared
					//aRow, bRow - the row components for the values being compared (useful if you need to access additional fields in the row data for the sort)
					//column - the column component for the column being sorted
					//dir - the direction of the sort ("asc" or "desc")
					//sorterParams - sorterParams object from column definition array
					if(a == null)
						a = 0;
					else
						a = a.count;
					if(b == null)
						b = 0;
					else
						b = b.count;
					return a-b; //you must return the difference between the two values
				}
			},
			{title:"Escalate", field:"escalations",headerVertical:"flip",width:10,
					
				formatter:function(cell, formatterParams, onRendered)	
				{
					tickets = cell.getValue();
					data = cell.getRow().getData();
					if(tickets != null)
					{
						if(tickets.type == 'Critical')
						{
							cell.getElement().style.backgroundColor ="red";	
							color ="white";
						}
						else if(tickets.type == 'Major')
						{
							cell.getElement().style.backgroundColor ="orange";
							color ="black";							
						}
						else 
						{
							cell.getElement().style.backgroundColor ="yellow";	
							color ="black";
						}
						link = data.jiraurl+'/issues/?jql=issue in ('+tickets.references+')';
						return '<a href="'+link+'" title="'+tickets.references+'"><span style="color:'+color+';">'+tickets.count+'</span></a>';
					}
					return cell.getValue();
				}, 
				sorter:function(a, b, aRow, bRow, column, dir, sorterParams)
				{
					//a, b - the two values being compared
					//aRow, bRow - the row components for the values being compared (useful if you need to access additional fields in the row data for the sort)
					//column - the column component for the column being sorted
					//dir - the direction of the sort ("asc" or "desc")
					//sorterParams - sorterParams object from column definition array
					if(a == null)
						a = 0;
					else
						a = a.count;
					if(b == null)
						b = 0;
					else
						b = b.count;
					return a-b; //you must return the difference between the two values
				}
			},
			{title:"Progress", field:"status.progress",
				formatter:"progress", formatterParams:{
					min:0,
					max:100,
					legend :function(value){return '<span style="font-size:10px;font-weight:bold">'+value + "%"+'</span>'},
					color:["lightgreen"],
					legendColor:"#000000",
					legendAlign:"center",
				}
			},
			],
			cellClick:function(e, cell)
			{
				
				row = cell.getRow();
				data = row.getData();
				
				selected_row = row;
				field = cell.getField();
				console.log("Row clicked");
			
				project = row.getData();
				
				window.location.href = "/dashboard/"+username+"/"+project.id;
			},
	});
	//table.hideColumn("id");
}
$(document).ready(function()
{
	ShowTable();
});
@endsection
