@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.css') }}" />
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.theme.default.css') }}" />
@endsection
@section('style')

.tabulator .tabulator-tableHolder {
   background-color:#fff;
}

@endsection
@section('content')
<div style="width:95%;" class="container-fluid">
	<div class="mainpanel">
		<button type="button" onclick="expandCollapse('expand');">Expand</button>
<button type="button" onclick="expandCollapse('collapse');">Collapse</button> 

		<div style="margin-top:20px;" id="table"></div>
	</div>
</div>
<script src="{{ asset('js/tabulator.table.js') }}" ></script>
@endsection
@section('script')
var isloggedin = {{$isloggedin}};
var iframe = {{$iframe}};
var head = @json($head);
var table=null;
$(document).ready(function()
{
	if(iframe==0)
	{
		if(isloggedin)
		{
			$('.navbar').removeClass('d-none');
			$('#dashboard_menuitem').show();
			$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
		}
	}
	tabledata = Object.values(head);
	console.log(tabledata);
	
	table = new Tabulator("#table", 
	{
		tooltips:true,
		//layout:"fitDataFill",
		dataTree:true,
		dataTreeStartExpanded:[true,false],
		//height:1000,
		dataTreeBranchElement:false, //hide branch elementoggle icon
		dataTreeChildField:"children", //look for the child row data array in the childRows field
		//persistenceMode:true , 
		//persistentLayout:true,
		movableColumns: true,
		
		layout:"fitDataStretch",
		//layout:"fitData",
		//layout:"fitColumns",
		data:tabledata, //load data into table
		columns:[
			{width:300,title:"Title", field:"summary",
				mutator:function(value, data, type, params, component)
				{
					if(data.duplicate == 1)
					{
						data.comment = "Duplicate";
					}
					return data.summary;
				},
				formatter:function(cell, formatterParams, onRendered)
				{
					data=cell.getRow().getData();
					icons = '';
					if(data.duplicate == 1)
					{
						icons += '<i style="color:DarkSeaGreen;margin-left:10px" class="fa fa-clone" aria-hidden="true"></i>&nbsp&nbsp';
					}
					else if(data.issuetype == 'DEFECT')
					{
						icons += '<i style="color:orange" class="fas fa-bug"></i>&nbsp&nbsp';
						
					}
					return icons+"<span>"+data.summary+"</span>";
				}
			},
			{width:100,title:"Jira", field:"key", 
				formatter:function(cell, formatterParams, onRendered)	
				{
					data  = cell.getRow().getData();
					if(cell.getValue() == data.extid)// Not a Jira Task 
						return '';
					return cell.getValue();
				}
			},		
			{title:"Assignee", field:"assignee"},		
			{title:"Id", field:"status",visible:false},
			
			{title:"Version", field:"fixVersions",
				formatter:function(cell, formatterParams, onRendered)
				{
					style = '';
					return '<span style="font-size1:10px;'+style+'">'+cell.getValue()+'</span>';
				}
			},
			{title:"Sprint", field:"sprintname",
				formatter:function(cell, formatterParams, onRendered)	
				{
					data  = cell.getRow().getData();
					sprintstate = data.sprintstate;
					if(sprintstate == 'ACTIVE')
					{
						style= '';
						if(data.status != "RESOLVED")
							style="color:green";
					}
					else if(sprintstate == 'FUTURE')
						style="";
					else if(sprintstate == 'CLOSED')
						style="text-decoration: line-through;color:grey";
					else
						style= '';
					
					return '<span style="font-size1:10px;'+style+'">'+cell.getValue()+'</span>';
				}
			},
			{title:"Estimate", field:"estimate",headerVertical:"flip",
				formatter:function(cell, formatterParams, onRendered)	
				{
					estimate = Math.round(cell.getValue());
					return '<span title="Estimate">'+estimate+'</span>';
				}
			},
			{title:"Achieved", field:"timespent",headerVertical:"flip",
				formatter:function(cell, formatterParams, onRendered)	
				{
					timespent = Math.round(data.timespent);
					return '<span title="Achieved">'+timespent+"</span>";
				}
			},
			{title:"Status", field:"ostatus"},
			{title:"Progress", field:"progress",
				formatter:"progress", formatterParams:{
					min:0,
					max:100,
					legend :function(value){return '<span style="font-size1:10px;font-weight:bold">'+value + "%"+'</span>'},
					color:["lightgreen"],
					legendColor:"#000000",
					legendAlign:"center",
				}
			},
			{title:"Created", field:"created",
				formatter:function(cell, formatterParams, onRendered)	
				{
					today = new Date();
					date = new Date(cell.getValue());
					if(date == 'Invalid Date')
						return '';
					
					days=  dates.countDays(date,today);
					if(days <= 7)
					{
						return '<span style="font-size1:12px;color:green;font-weight:bold">'+date.toString().slice(0,15)+'</span>';
					}
					return '<span style="font-size1:12px;">'+date.toString().slice(0,15)+'</span>';
					
				}
			},
			{title:"Comments", field:"comment"}
			],
			rowFormatter:function(row){
				var data = row.getData();
				row.getElement().style.fontSize = "13px";
				if(data.isparent ==1)
				{
					row.getElement().style.fontWeight = "bold";
					row.getElement().style.fontSize = "12px";
				}
				if(data.status == "RESOLVED"){
					row.getElement().style.color = "#CDCDCD";
				}
				else if(data.duplicate == 1)
				{
					row.getElement().style.color = "#696969";
					row.getElement().style.fontStyle = "italic";
					
				}
			},
	});
})
function expandCollapse(action)
{
	if (action == "expand")
	{
		table.options.dataTreeStartExpanded = true;
	}
	else
	{
		table.options.dataTreeStartExpanded = [true,false]; 	
	}
  table._create();
}

@endsection