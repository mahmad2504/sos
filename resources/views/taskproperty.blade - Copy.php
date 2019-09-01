@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.css') }}" />
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.theme.default.css') }}" />
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
@endsection
@section('style')
.progress {height: 10px;}
.saved{outline:2px solid green;
    outline-offset: -2px; }
.unsaved{outline:2px solid red;
    outline-offset: -2px; }
@endsection
@section('content')
<div id="container" style="width:90%; margin-left: auto; margin-right: auto; display:block" class="center">
	<div class="loading">Loading&#8230;</div>
	<p id='description'>Description</p>
	<div id="selectedtable"></div><br>
	<table id="treetable" style="display:none;" class="table">
		<caption style="caption-side:top;text-align: center">
		  <a href="#"  onclick="jQuery('#treetable').treetable('expandAll'); return false;">Expand all</a>&nbsp|
		  <a href="#" onclick="jQuery('#treetable').treetable('collapseAll'); return false;">Collapse all</a>
		</caption>
		<col style="width:40%;border-right:1pt solid lightgrey;"> <!--Title  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Jira  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Estimate  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Jira Duedate  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Progress  --> 
		<col style="width:5%;border-right:1pt solid lightgrey;"> <!--Select  --> 
		<thead style="background-color: SteelBlue;color: white;font-size: .8rem;">
		  <tr>
			<th>Title</th>
			<th>Jira</th>
			<th id='estimatecolumn'></th>
			<th>Jira Duedate</th>
			<th>Progress</th>
			<th>Select</th>
		  </tr>
		</thead>
		<tbody id="tablebody">
		</tbody>
	</table>
	<div id="legend">
		<span>Project<span style="margin-top:20px;padding:5px;" class="PROJECT">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Requirement<span style="margin-top:20px;padding:5px;" class="REQUIREMENT">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Workpackage<span style="margin-top:20px;padding:5px;" class="WORKPACKAGE">&nbsp&nbsp&nbsp</span></span>
		<span>Epic<span style="margin-top:20px;padding:5px;" class="EPIC">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Task<span style="margin-top:20px;padding:5px;" class="TASK">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Defect<span style="margin-top:20px;padding:5px;" class="DEFECT">&nbsp&nbsp&nbsp</span></span>
	</div>
</div>
<script src="{{ asset('js/tabulator.table.js') }}" ></script>
<script src="{{ asset('js/jquery.treetable.js') }}" ></script>
<script src="{{ asset('js/msc-script.js') }}" ></script>
@endsection
@section('script')

var username = "{{$user->name}}";
var userid = {{$user->id}};
var projectid = {{$project->id}};
var _token = "{{ csrf_token() }}";
var cur_row = null;
var data = null;
var data_array = [];
var selected_tasks = [
    {id:1, name:"Billy Bob", age:"12", gender:"male", height:1, col:"red", dob:"", cheese:1},
    {id:2, name:"Mary May", age:"1", gender:"female", height:2, col:"blue", dob:"14/05/1982", cheese:true},
    {id:3, name:"Christine Lobowski", age:"42", height:0, col:"green", dob:"22/05/1982", cheese:"true"},
    {id:4, name:"Brendon Philips", age:"125", gender:"male", height:1, col:"orange", dob:"01/08/1980"},
    {id:5, name:"Margret Marmajuke", age:"16", gender:"female", height:5, col:"yellow", dob:"31/01/1999"},
    {id:6, name:"Billy Bob", age:"12", gender:"male", height:1, col:"red", dob:"", cheese:1},
    {id:7, name:"Mary May", age:"1", gender:"female", height:2, col:"blue", dob:"14/05/1982", cheese:true},
    {id:8, name:"Christine Lobowski", age:"42", height:0, col:"green", dob:"22/05/1982", cheese:"true"},
    {id:9, name:"Brendon Philips", age:"125", gender:"male", height:1, col:"orange", dob:"01/08/1980"},
    {id:10, name:"Margret Marmajuke", age:"16", gender:"female", height:5, col:"yellow", dob:"31/01/1999"},
];
function UpdatePosition()
{
	data1 = table.getData(true);

	
	output = {};
	for(var i=0;i <data1.length; i++)
	{
		row  = data1[i];
		data[row.extid].position = i;
		output[i] = row.key;
		//console.log(row);
	}
	output._token = "{{ csrf_token() }}";
	$.ajax({
			type:"PUT",
			url:'/taskproperty/position/'+projectid,
			cache: false,
			data:output,
			success: function(response){
				
			},
			error: function(response){
			}
		});
	table.redraw();
}

function LoadProjectData(url,data,onsuccess,onfail)
{
	$.ajax({
		type:"GET",
		url:url,
		cache: false,
		data:data,
		success: onsuccess,
		error: onfail
	});
}
var estimate_units = '';
function OnProjectDataReceived(response)
{
	console.log(response.description);
	$('#description').append(response.description);
	if(response.estimation == 0)
	{
		header = 'Story Points';
		estimate_units = 'Points';
	}
	else
	{
		header = 'Time Estimates';
		estimate_units = 'Days';
	}
	
	$('#estimatecolumn').append(header);
}
var dateEditor = function(cell, onRendered, success, cancel){
    //cell - the cell component for the editable cell
    //onRendered - function to call when the editor has been rendered
    //success - function to call to pass the successfuly updated value to Tabulator
    //cancel - function to call to abort the edit and return to a normal cell

    //create and style input
    //var cellValue =  moment(cell.getValue(), "YYYY-MM-DD").format("YYYY-MM-DD");
	var cellValue =  moment(cell.getValue(), "YYYY-MM-DD").format('YYYY-MM-DD');


	
    input = document.createElement("input");

    input.setAttribute("type", "date");

    input.style.padding = "4px";
    input.style.width = "100%";
    input.style.boxSizing = "border-box";

	input.value = cellValue;
	

	console.log(cellValue);
    onRendered(function(){
        input.focus();
        input.style.height = "100%";
    });

    function onChange(){
		console.log("On Change");
        if(input.value != cellValue){
			val = moment(input.value, "YYYY-MM-DD").format('YYYY-MM-DD');
		
			if( val === 'Invalid date')
				success("");
            success(val);
        }else{
            cancel();
        }
    }

    //submit new value on blur or change
    input.addEventListener("blur", onChange);

    //submit new value on enter
    input.addEventListener("keydown", function(e){
		console.log(e.keyCode);
		if(e.keyCode == 46)
			success("");
        if(e.keyCode == 13){
            onChange();
        }

        if(e.keyCode == 27){
            cancel();
        }
    });
	
    return input;
};
var cur_row = null;
function GetSelectElementId(extid)
{
	var mid =  (extid+"").replace(/\./g, '-');
	return "select_"+mid;
}
function Update(extid)
{
	data[extid]._token = "{{ csrf_token() }}";
	console.log(data[extid]);
	$.ajax({
			type:"PUT",
			url:'/taskproperty/'+projectid,
			cache: false,
			data:data[extid],
			success: function(response){
				// Update Other table with isconfigured rows
				var mid = "#"+GetSelectElementId(extid);
				$(mid).addClass("saved");
				table.setData(data_array);
				//table = new Tabulator("#selectedtable", InitTabulator());
				

				//table.setFilter("isconfigured", "=", "true");
				//UpdatePosition();
				table.redraw();
				//table.setFilter("isconfigured", "=", "true");
				setTimeout(function()
				{
					$(mid).removeClass("saved");
				},500);
			},
			error: function(response){
				//console.log(extid);
				var mid = "#"+GetSelectElementId(extid);
				data[extid].isconfigured = (data[extid].isconfigured == true) ? false:true;
				$(mid).addClass("unsaved");
				$(mid).prop("checked", data[extid].isconfigured);
				//mscAlert('Error',response.responseJSON.message);
				setTimeout(function()
				{
					$(mid).removeClass("unsaved");
				},500);

			}
		});
}
function InitTabulator()
{
	var settings = 
	{
		tooltips:false,
		index:"id",
		layout:"fitDataFill",
		columnVertAlign:"bottom", 
		tooltipGenerationMode:"hover",
		movableRows:true,
		data:data_array,
		
		columns:
		[
			{rowHandle:true, formatter:"handle", headerSort:false, frozen:true, width:30, minWidth:30},
			{width:"10%",resizable: false,title:"Positiont",field:"position", headerFilter:false},
			{width:"5%",resizable: false,title:"Milestone",field:"ismilestone", headerFilter:false,
				align:"center", editor:true, formatter:"tick",
			},
			{width:"30%",resizable: false,title:"Title",field:"summary", headerFilter:false},
			{width:"20%",resizable: false,title:"Alternate Title",field:"atext", headerFilter:false,
				editor:"input"
			},

			{width:"10%",resizable: false,title:"Jira",field:"key", headerFilter:false,
				formatter:function(cell, formatterParams, onRendered)
				{
					link = cell.getRow().getData().jiraurl;
					extid = cell.getRow().getData().extid;
					if(cell.getValue() === extid)
						return null;
					else
						return '<a href="'+link+"/"+cell.getValue()+'">'+cell.getValue()+'</a>';
				}
			
			},

			{width:"10%",resizable: false,title:"Start",field:"tstart", headerFilter:false,editor:dateEditor,
				formatter:function(cell, formatterParams, onRendered)
				{
					var dte = moment(cell.getValue(),"YYYY-MM-DD");
					if(dte.isValid())
						return dte.format('MMMM D YYYY');
					else
						return '';
				}
			},
			{width:"10%",resizable: false,title:"Deadline",field:"tend", headerFilter:false,editor:dateEditor,
				formatter:function(cell, formatterParams, onRendered)
				{
					var dte = moment(cell.getValue(),"YYYY-MM-DD");
					if(dte.isValid())
						return dte.format('MMMM D YYYY');
					else
						return '';
				}
			},
		],
		rowMoved:function(row){
			//console.log(row);
			//console.log(row.getPosition(true));
        	//console.log("Row: " + row.getData().extid + " has been moved");
			
			data[row.getData().extid].ismoved = 1;
			UpdatePosition();
			
			//Update(row.getData().extid);
		},
		dataEdited:function(data){
			//data - the updated table data
			if(cur_row != null)
			{
				data = cur_row._row.data;
				
				Update(data.extid);

			}
			
		},
		rowClick:function(e, row)
		{
			console.log("Row click");
			cur_row = row;
		},
		rowFormatter:function(row){
		},
		initialSort:[
        	{column:"position", dir:"asc"}, //sort by this first
    	],
		validationFailed:function(cell, value, validators)
		{
			
		},
		initialFilter:[
			[
			    {field:"isconfigured", type:"=", value:true},
				{field:"isconfigured", type:"=", value:"true"}
			]
		],
		renderComplete:function()
		{
			
		}
	};
	return settings;
}


$(document).ready(function()
{
	if(username != null)
		$('.navbar').removeClass('d-none');
	
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
	console.log(selected_tasks);
	
	
	
	LoadProjectData("{{route('getproject',['id'=>$project->id])}}",null,OnProjectDataReceived,function(response){});
	$.ajax(
	{
		type:"GET",
		url:"{{ route('gettreedata',[$project->id]) }}",
		data:null,
		success: function(response)
		{
			$('.loading').hide();
			ShowTree(JSON.parse(response));
		},
		error: function (error) 
		{
			$('.loading').hide();
			console.log(error);  
			mscAlert('Error', 'Project Database Missing. Please sync with Jira and try again', function(){window.location.href = "/";})
		}
	});
	function round(value, precision) 
	{
		var multiplier = Math.pow(10, precision || 0);
		return Math.round(value * multiplier) / multiplier;
	}
	function OnTaskUpdateSuccess(data)
	{
		//data = JSON.parse(data);
		var row = data[exitid];
		var id = row['extid'];
		var mid = (id+"").replace(/\./g, '-');
		id="cb_isconfigured_"+mid;
		console.log(data);
	}
	function ShowTree(response)
	{
		console.log(response);
		data = response;
		
		for (var exitid in data)
		{
			var row = data[exitid];
			var id = row['extid'];

			//var mid = (id+"").replace(/\./g, '-');
	
			var pid = row['pextid'];
			var isconfigured = row['isconfigured'];
			var atext = row['atext'];
			var _class =row['issuetype'];
			var title=row['summary'];
			var link=row['jiraurl'];
			var linktext=row['key'];
			var estimate=round(row['estimate'],1);
			var progress=round(row['progress'],1);
			var status=row['status'];
			var priority=row['priority'];
			var duedate=row['duedate'];
			var duplicate=row['duplicate'];
			data_array.push(row);
			var progressbar_animation_class = 'progress-bar-striped progress-bar-animated';
			
			if(_class == 'TASK')
			{
				if(status == 'OPEN')
					_class = 'TASK_OPEN';
				if(status == 'RESOLVED')
				{
					_class = 'TASK_RESOLVED';
				}
			}
			color = '';
			progressbar_color = 'green';
			if(status == 'RESOLVED')
			{
				progressbar_animation_class = '';
				progressbar_color = 'darkgreen';
			}
			else
			{
				if(priority == 1)
					color = 'red';
				if(priority == 2)
					color = 'orange';
			}
			var rowstr = '<tr ';
			rowstr += "data-tt-id='"+id+"' ";

			if(pid != '')
				rowstr += "data-tt-parent-id='"+pid+"'";
			

			rowstr += "style='border-bottom:1pt solid grey;' class='branch expanded'>";
			rowstr += "<td  style='white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;'><span class='"+_class+"'>";
			rowstr += id+" "+title+"</span></td>";
			// ALTERNATE TEXT

			//rowstr += "<td  style='white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;'>";
			//rowstr += atext+"</span></td>";

			if(linktext == id)// Not a Jira Task 
				rowstr += '<td></td>';
			else
				rowstr += "<td><a style='font-size:.6rem; color:"+color+";' href='"+link+"/browse/"+linktext+"'>"+linktext+'</a></td>';
			rowstr += "<td  style='white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;'>";
			if(estimate > 0)
				rowstr += estimate+" "+estimate_units+"</span></td>";
			else
				rowstr += "</span></td>";

			rowstr += "<td  style='white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;'>";
			rowstr += duedate+"</span></td>";

			var str = '<div class="shadow-lg progress position-relative" style="background-color:grey"><div class="progress-bar '+progressbar_animation_class+'" role="progressbar" style="background-color:'+progressbar_color+' !important; width: '+progress+'%" aria-valuenow="'+progress+'" aria-valuemin="0" aria-valuemax="100"></div></div>'+'<small style="color:black;" class="justify-content-center d-flex">'+progress+'%</small>';
			rowstr += "<td>"+str+"</td>";

			var mid = GetSelectElementId(id);
			
			if(duplicate == 0)
			{
				if(isconfigured == true)
				{
					rowstr += '<td><input id="'+mid+'" class="input_field" field="isconfigured" extid="'+id+'"  type="checkbox" checked></td>';  		
				}
				else
				{
					rowstr += '<td><input id="'+mid+'" class="input_field" field="isconfigured" extid="'+id+'"  type="checkbox"></td>'; 
				}
			}
			else
			{
				rowstr += '<td>Duplicate</td>'; 
			}
			rowstr += "</tr>";
			$('#tablebody').append(rowstr);
		}
		console.log(data_array);
		table = new Tabulator("#selectedtable", InitTabulator());
		table.setData(data_array);
		//table.redraw();
		$('#container').css('display','block');

		$("#milestonetreetable").treetable({ expandable: true });
		$("#milestonetreetable").show();

		$("#treetable").treetable({ expandable: true });
		$("#treetable").show();
		$("#legend").show();
		$('#treetable').treetable('expandAll')
		//$("#treetable").treetable("expandNode", "1");
		
		$(".input_field").click(function(e)
		{ 
			//e.preventDefault();
			var extid = $(this).attr("extid");
			data[extid].isconfigured = $(this).is(':checked');
			$(this).removeClass("unsaved");
			$(this).removeClass("saved");
			//console.log(data[extid].isconfigured);
			//alert(table.getDataCount(true));
			data1 = table.getData(true);
			//console.log(data1.length);
			
			if(data[extid].isconfigured == true)
				data[extid].position =  data1.length;
			
			else
				data[extid].position =  -1;
			Update(extid);
		});
		
	}
})
@endsection
