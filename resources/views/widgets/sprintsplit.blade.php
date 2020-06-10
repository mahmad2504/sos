@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
<link rel="stylesheet" href="{{ asset('css/rmo.css') }}" />
@endsection
@section('style')
#divtable
{
width: 100%;
height: 600px;
overflow: scroll
}

.center {
  display: flex;
  justify-content: center;
  align-items: center;
  //margin-top:-45px;
}

.sticky-col {
  position: sticky;
  position: -webkit-sticky;
  //background-color: white;
  border: 1px solid black;
  left: 0px;
}
.sticky-col2 {
  position: sticky;
  position: -webkit-sticky;
  //background-color: white;
  border: 1px solid black;
  left: 80px;
}
.sticky-col3 {
  position: sticky;
  position: -webkit-sticky;
  //background-color: white;
  border: 1px solid black;
  left: 480px;
}


#r1c1,#r2c1,#r3c1,#r4c1,#r1c2,#r2c2,#r3c2,#r4c2,#r1c3,#r2c3,#r3c3,#r4c3
{
   background-color: #DCDCDC;	
}
//#r1c3
//{
// background-color: #ff0000;	
//}
@endsection
@section('content')
<div  class="flex" style="height:35px; border: 3px solid #4682B4;background:#2e5790;">
<h3>{{ $project->name}}</h3>
</div>

<div id="divtable_header" class=""></div>
<div id="divtable" class=""></div>
<hr>

<script src="{{ asset('js/msc-script.js') }}" ></script>

@endsection
@section('script')
var username = "{{$user->name}}";
var userid = {{$user->id}};
var projectid = {{$project->id}};
var isloggedin = {{$isloggedin}};
var data = @json($data);
var url = "{{$url}}";
var boundary=[];
boundary["2018-11-31"]={y:2019,w:1};

boundary["2019-11-30"]={y:2020,w:1};
boundary["2019-11-31"]={y:2020,w:1};

boundary["2021-0-1"]={y:2020,w:53};
boundary["2021-0-2"]={y:2020,w:53};
boundary["2021-0-3"]={y:2020,w:53};

boundary["2022-0-1"]={y:2021,w:52};
boundary["2022-0-2"]={y:2021,w:52};

boundary["2023-0-1"]={y:2022,w:52};

boundary["2024-11-30"]={y:2025,w:1};
boundary["2024-11-31"]={y:2025,w:1};

var rows = [];
function scrollTo(id)
{
  // Scroll
  $('html,body').animate({scrollTop: $("#"+id).offset().top},'slow');
}

function Rmo(start,end)
{
	var self = this;
	this.today_color='#8FBC8F';
	this.start = start;
	this.end=end;

	this.Show = function(tag)
	{
		this.SetDuration();
		$('#start').append(this.start.toUTCString());
		$('#end').append(this.end.toUTCString());
		this.dateArray = self.GenerateTableData();
		self.CreateTable('#'+tag);
	}
	this.SetDuration = function(start,end)
	{
		start = new Date(this.start);
		_start  = start.getDate() - start.getDay()+1;
		this.start = new Date(start.setDate(_start));

		end = new Date(this.end);
		if(end.getDay() != 0)
		{
			_end = end.getDate() + 7 - end.getDay(); // last day is the first day + 6
			this.end = new Date(end.setDate(_end));
		}
		else
		{
			this.end = end;	
		}	
	}
	Date.prototype.addDays = function(days) 
	{
		var dat = new Date(this.valueOf())
		dat.setDate(dat.getDate() + days);
		return dat;
	}
	Date.prototype.GetWeekNumber = function(){
		
		var jan1, w, d = new Date(this);
		var year=d.getFullYear();
		var month=d.getMonth();
		var dat=d.getDate();
		d.setDate(d.getDate()+4-(d.getDay()||7));		// Set to nearest Thursday: current date + 4 - current day number, make Sunday's day number 7
		jan1 = new Date(d.getFullYear(),0,1);		// Get first day of year
		w = Math.ceil((((d-jan1)/86400000)+1)/7);		// Calculate full weeks to nearest Thursday
		
	    if(year == 2021)
			w=w-1;
		var o = boundary[year+"-"+month+"-"+dat];
		if(o !== undefined)
			return o;
		return {y: year, w: w };
	};
	this._GetDates =  function(startDate, stopDate) 
	{
		var dateArray = new Array();
		var currentDate = startDate;
		while (currentDate <= stopDate) 
		{
			dateArray.push(currentDate)
			currentDate = currentDate.addDays(1);
		}
		return dateArray;
	}
	this.isToday = (someDate) => {
	  const today = new Date()
	  return someDate.getDate() == today.getDate() &&
		someDate.getMonth() == today.getMonth() &&
		someDate.getFullYear() == today.getFullYear()
	}
	this.MonthName = function(month)
	{
		if(month == '')
			return "";
		if(month == 0)
			return "Jan";
		else if(month == 1)
			return "Feb";
		else if(month == 2)
			return "Mar";
		else if(month == 3)
			return "Apr";
		else if(month == 4)
			return "May";
		else if(month == 5)
			return "Jun";
		else if(month == 6)
			return "Jul";
		else if(month == 7)
			return "Aug";
		else if(month == 8)
			return "Sep";
		else if(month == 9)
			return "Oct";
		else if(month == 10)
			return "Nov";
		else if(month == 11)
			return "Dec";
		else if(month == '')
			return "";
		return month;
	}
	this.GenerateTableData =   function()
	{
		var dateArray = self._GetDates(this.start, this.end);
		j=0;
		k=0;
		l=0;
		var yearArray=[];
		var monthArray=[];
		var weekArray=[];
		var sprintArray=[];
		for (i = 0; i < dateArray.length; i ++ ) 
		{
			weekinfo=dateArray[i].GetWeekNumber();
			week=weekinfo.w;
			year=weekinfo.y;
		    if(weekArray[year+"_"+week] === undefined)
				weekArray[year+"_"+week]=[]
			 
			
			today=0;
			if( self.isToday(dateArray[i]) )
			{
				console.log("Today is "+dateArray[i].toString());
				today=1;
			}
			dateArray[i].today=today;
			console.log(dateArray[i].today);
			weekArray[year+"_"+week].push({'week':week,'today':today,'date':dateArray[i]});
		   
		    var sprint = Math.floor(week/3);
			if(week%3 > 0)
				sprint = sprint+1;
			if(sprintArray[year+"_"+sprint] === undefined)
				sprintArray[year+"_"+sprint]=[];
			
			sprintArray[year+"_"+sprint].push({'year':year,'sprint':sprint,'today':today,'date':dateArray[i]});
			
			year=dateArray[i].getFullYear();
			if(yearArray[year] === undefined)
				yearArray[year]=[]
			
			yearArray[year].push({'year':year,'today':today,'date':dateArray[i]});
			month=dateArray[i].getMonth();
			if(monthArray[year+"_"+month] === undefined)
				monthArray[year+"_"+month]=[]
			
			monthArray[year+"_"+month].push({'year':year,'today':today,'date':dateArray[i]});
		}
		ret = {};
		ret.dayArray = dateArray;
		ret.weekArray = weekArray;
		ret.monthArray = monthArray;
		ret.yearArray = yearArray;
		ret.sprintArray= sprintArray;
		return ret;
	}
	this.CreateTable = function(tag)
	{
		var table = $('<table>');
		table.addClass("RmoTable");
		$(tag).append(table);
		

		$(tag).append('<br>');
		var row=1;
		yearrow = self.GenerateYearRow(row++);
		table.append(yearrow);
		
		monthrow = self.GenerateMonthRow(row++);
		table.append(monthrow);
		
		weekrow = self.GenerateWeekRow(row++);
		table.append(weekrow);
		
		sprintrow = self.GenerateSprintRow(row++);
		table.append(sprintrow);
		
		dayrow = self.GenerateDayRow(row++);
		dayrow.attr("height","5px");
		table.append(dayrow);
		
		for(var i=0;i<data.length;i++)
		{
			var j=0;
			if(data[i].sprintsplit.length == 0)
			{
				data[i].team = '';
				sprintrowdata = self.GenerateSprintRowData(row++,data[i]);
				sprintrowdata.css('background-color','#ffffff');
			}
			else
			{	
				for(var team in data[i].sprintsplit)
				{
					//var team=data[i].sprintsplit[j];
					//data[i]->team=data[i].sprintsplit[j]
					data[i].team = team;
					
					sprintrowdata = self.GenerateSprintRowData(row,data[i]);
					if(j==0)
						sprintrowdata.css('background-color','#F2F2F2');
					else
						sprintrowdata.css('background-color','#F8F8F8');
					
					table.append(sprintrowdata);
					rows.push(sprintrowdata);
					j++;
					//console.log(data[i]);
					for(index in data[i].sprintsplit[team])
					{
						number=data[i].sprintsplit[team][index].number*1;
						year=data[i].sprintsplit[team][index].year*1;
						//console.log("--------->"+row+"<----------");
						var id = "#"+row+"_"+year+"_"+number;
						//console.log(id);
						$(id).attr('title', data[i].sprintsplit[team][index].origname );
						$(id).addClass('sprintcell');
						$(id).css('background-color','#E4FCE4');
					}
					row++;
				}			
			}
			//sprintrowdata = self.GenerateSprintRowData(row++,{'key':'','summary':''});
			//table.append(sprintrowdata);
		}
		this.table = table;
	}
	this.GenerateYearRow = function(r)
	{
		var yearArray = this.dateArray.yearArray;
		var row = $('<tr>');
		row.addClass("rowyear");
		var c=1;
		
		var col = $('<th>');
		col.html('Year');
		//col.addClass('sticky-col');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var col = $('<th>');
		col.html('&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var col = $('<th>');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		col.html('&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp');
		var color='#DCDCDC';
		for (var year in yearArray) 
		{
			var today = yearArray[year].find(function(obj, index) 
			{
				if(obj.today == 1)
					return true;
			});

			if(today)
				color=this.today_color;
			else
			{
				if(color==this.today_color)
					color='#FFFFFF';
			}
			colspan = Object.keys(yearArray[year]).length;
			if(colspan < 15)
				year = '';
			col = $('<th colspan="'+colspan+'">');
			col.attr('id','r'+r+'c'+c++);
			col.html(year);
			col.css('background-color',color);
			row.append(col);
		}
		return row;
	}
	this.GenerateMonthRow =  function(r)
	{
		monthArray = this.dateArray.monthArray;
		var row = $('<tr>');
		row.addClass("rowmonth");
		
		var c=1;
		var col = $('<th>');
		row.append(col);
		col.attr('id','r'+r+'c'+c++);
		col.html('Month');
		//col.addClass('sticky-col');
		
		var col = $('<th>');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var col = $('<th>');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var color='#DCDCDC';
		for (var month in monthArray) 
		{
			var today = monthArray[month].find(function(obj, index) 
			{
				if(obj.today == 1)
					return true;
			});

			if(today)
				color=this.today_color;
			else
			{
				if(color==this.today_color)
					color='#FFFFFF';
			}
			colspan = Object.keys(monthArray[month]).length;
			if(colspan <= 15)
				month = '';
			
			col = $('<th colspan="'+colspan+'">');
			col.attr('id','r'+r+'c'+c++);
			col.html(self.MonthName(month.substring(5)));
			col.css('background-color',color);
			row.append(col);
		}
		return row;
	}
	this.GenerateWeekRow =  function(r)
	{
		weekArray = this.dateArray.weekArray;
		var c=1;
		var row = $('<tr>');
		row.addClass("rowweek");
		var col = $('<th>');
		col.attr('id','r'+r+'c'+c++);
		//col.addClass('sticky-col');
		col.html('Week');
		row.append(col);
		
		var col = $('<th>');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var col = $('<th>');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var color='#DCDCDC';
		for (var week in weekArray) 
		{
			var today = weekArray[week].find(function(obj, index) 
			{
				if(obj.today == 1)
					return true;
			});

			if(today)
				color=this.today_color;
			else
			{
				if(color==this.today_color)
					color='#FFFFFF';
			}
			colspan = Object.keys(weekArray[week]).length;
			col = $('<th colspan="'+colspan+'">');
			col.attr('id','r'+r+'c'+c++);
			year = week.substring(0,4);
			weeknum = week.substring(5);
			col.html(weeknum);
			col.css('background-color',color);
			col.css('font-size','12px');
			row.append(col);
		}
		return row;
	}
	this.GenerateSprintRow =  function(r)
	{
		sprintArray = this.dateArray.sprintArray;
		var row = $('<tr>');
		
		var c=1;
		
		var col = $('<th>');
		col.attr('id','r'+r+'c'+c++);
		//col.addClass('sticky-col');
		col.html('Sprint');
		row.append(col);
		
		
		var col = $('<th>');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var col = $('<th>');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var color='#DCDCDC';
		for (var sprint in sprintArray) 
		{
			var today = sprintArray[sprint].find(function(obj, index) 
			{
				if(obj.today == 1)
					return true;
			});

			if(today)
				color=this.today_color;
			else
			{
				if(color==this.today_color)
					color='#FFFFFF';
			}
			colspan = Object.keys(sprintArray[sprint]).length;
			col = $('<th colspan="'+colspan+'">');
			col.attr('id','r'+r+'c'+c++);
			col.html(sprint.substring(5));
			col.css('background-color',color);
			col.css('font-size','12px');
			row.append(col);
		}
		return row;
	}
	this.GenerateSprintRowData =  function(r,data)
	{
	    sprintArray = this.dateArray.sprintArray;
		var row = $('<tr>');
		row.addClass(data.key);
		row.addClass("rowsprint");
		var c=1;
		var col = $('<td>');
		col.attr('id','r'+r+'c'+c++);
		col.attr('title',data.summary);

		var link='<a href="'+url+'/browse/'+data.key+'">'+data.key+'</a>';
		col.html(link);
		col.addClass('sticky-col');
		col.css("padding-left","3px");
		col.css("padding-right","3px");
		row.append(col);
		
		var col = $('<td>');
		col.attr('id','r'+r+'c'+c++);
		col.addClass(data.key);
		col.addClass("summary");
		if(data.summary.length > 50)
			col.html(data.summary.substring(0,47)+"...");
		else
			col.html(data.summary.substring(0,50));
		
		col.attr('title',data.summary);
		col.addClass('sticky-col2');
		//col.css("padding-left","3px");
		row.append(col);
		
		var col = $('<td>');
		col.attr('id','r'+r+'c'+c++);
		col.addClass('sticky-col3');
		col.html(data.team+"&nbsp&nbsp&nbsp&nbsp");
		row.append(col);

		var sprintsplit = data.sprintsplit[data.team];

		//var color='#DCDCDC';
		for (var sprint in sprintArray) 
		{
			colspan = Object.keys(sprintArray[sprint]).length;
			var year= sprintArray[sprint][0].year;
			var sprint= sprintArray[sprint][0].sprint;
			col = $('<td colspan="'+colspan+'">');
			col.attr('id',r+"_"+year+"_"+sprint);
			//col.html(sprint.substring(5));
			//col.css('background-color',color);
			col.css('font-size','12px');
			row.append(col);
		}
		if(sprintsplit === undefined)
			return row;
		//console.log(data.team);
		//console.log(sprintsplit);
		
		console.log("exit");
		return row;
	}
	this.GenerateDayRow =  function(r)
	{
		dayArray = this.dateArray.dayArray;
		var row = $('<tr>');
		row.addClass("rowday");
		var c=1;
		var col = $('<td>');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var col = $('<td>');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var col = $('<td>');
		col.attr('id','r'+r+'c'+c++);
		row.append(col);
		
		var color='#DCDCDC';
		for (var day in dayArray) 
		{
			var today = dayArray[day].today;
			
			if(today)
				color=this.today_color;
			else
			{
				if(color==this.today_color)
					color='#FFFFFF';
			}
			col = $('<td>');
			col.attr('id','r'+r+'c'+c++);
			col.css('background-color',color);
			row.append(col);
		}
		return row;
	}
	
	this.GenerateEmptyRow = function()
	{
		yearArray = this.dateArray.yearArray;
		html = '<tr>';
		html += '<th class="cell_year"></th>';
		html += '<th class="cell_year">&nbsp&nbsp&nbsp&nbsp</th>';
		color='#DCDCDC';
		colspan = 0;
		for (var year in yearArray) 
		{
			if(yearArray[year].includes(1))
				color=this.today_color;
			else
			{
				if(color==this.today_color)
					color='#FFFFFF';
			}
			colspan += Object.keys(yearArray[year]).length;
		}
		html += '<th style="background-color:'+color+';" class="cell_year" colspan="'+colspan+'">'+'</th>';
		html += '</tr>';
		return $(html);
	}
	this.GenerateDayCells = function()
	{
		var html= '';
		for (var day in this.dateArray.dayArray) 
			html += '<td></td>';
		return html;
					
	}
	this.GenerateWeekHeaderCells = function()
	{
		var html= '';
		for (var week in this.dateArray.weekArray) 
			html += '<th></th>';
		return html;
	}
	this.GenerateWeekCells = function()
	{
		var html= '';
		for (var week in this.dateArray.weekArray) 
			html += '<td></td>';
		return html;
	}
	this.GenerateCells = function(resource=null,project=null,tag='td',showweek=0)
	{
		weekArray =  this.dateArray.weekArray;
		var html='';
		var sub=0;
		var id='';
	   
	  // $('element').attr('id', 'value');
	   //$( "p" ).addClass( "myClass yourClass" );
		//var cls = 'cell_'+tag;
		var cls='';
		id='cell_';
		del='';
		if(resource != null)
		{
			//cls = 'cell_'+tag+' cell_resource';
			id=id+resource.id;
			del='_';
		}
		if(project != null)
		{
			//cls = 'cell_'+tag+' cell_project';
			id=id+del+project.id;
	    }
		
		//var today = new Date();
		// todayyear  = today.getFullYear();
		color='#DCDCDC';
		currentweek=0;
		for (var week in weekArray) 
		{
			if(weekArray[week].length < 7)
				continue;
			
			if(currentweek == 0)
			{
				for(key in weekArray[week])
				{
					//console.log(weekArray[week][key]);
					if(weekArray[week][key].today == 1)
					{
						//console.log(weekArray[week][key]);
						currentweek=1;
					}
					
				}
			}
			
			colspan = Object.keys(weekArray[week]).length;
			//console.log(week);
			//console.log(colspan);
			year = week.substring(0,4);
			week = week.substring(5);
			var data='data-year="'+year+'"';
			data= data+' data-week="'+week+'"';
			if(resource !=  null)
				ncls = cls+' column_'+resource.id+'_'+year+'_'+week;
			else
				ncls = cls;
			
			if(resource != null)
				data=data+' data-resource="'+resource.id+'"';
			if(project != null)
			   data=data+' data-pindex="'+project.id+'"';
			
			data=data+' data-tag="'+this.tag+'"';
			
			nid = id+"_"+year+"_"+week;
			if(week==1&&colspan==0)
				sub=1;
			else if(week==1)
				sub=0;
			 
			//week=ParseInt(week)+ParseInt(sub);
			if(showweek)
			{
				if(week < 10)
					week = "&nbsp&nbsp"+week+"&nbsp&nbsp";
				else
					week = "&nbsp"+week+"&nbsp";
			}
			else
				week='';
			if(colspan != 7)
				consolel.log("Error "+nid);
			if(colspan>0)
			{
				if(currentweek==1 && showweek)
				{
					html += '<'+tag+' '+data+' style="background-color:'+this.today_color+';" class="'+ncls+'" id="'+nid+'" width="40px;" colspan="'+colspan+'">'+week+'</'+tag+'>';
					color = '#FFFFFF';
					currentweek=2;
				}
				else if(showweek)
					html += '<'+tag+' '+data+' style="background-color:'+color+';" class="'+ncls+'" id="'+nid+'" width="40px;" colspan="'+colspan+'">'+week+'</'+tag+'>';
				else
					html += '<'+tag+' '+data+' class="'+ncls+'" id="'+nid+'" width="40px;" colspan="'+colspan+'">'+week+'</'+tag+'>';
			}
		 }
		 return html;
	}
}
$(document).ready(function()
{
	//alert(localStorage.getItem("lastname"));
	var rmo = new Rmo('2020-01-01','2021-12-31');
	rmo.Show('divtable');
	var hidden=0;
	$('.sprintcell').click(function(){
		console.log($(this).attr('title'));
		var link = url+'/issues/?jql=sprint="'+$(this).attr('title')+'"';
		var win = window.open(link, '_blank');
		win.focus();				
	});
	$('.summary').click(function(){
		//$(this).hide();
		//console.log($(this));
		var key = $(this).attr('class').split(' ')[0];;
		
		for(i in rows)
		{
			//console.log(rows[i]);
			if(rows[i].hasClass(key))
				continue;
			
			if(hidden==0)
				rows[i].hide();
			else
				rows[i].show();
				
		}
		
		
		$(this).show();
		if(hidden == 0)
			hidden = 1;
		else
			hidden=0;
	});
	
	
})
@endsection