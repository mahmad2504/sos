function Rmo(tabledata)
{
	var self = this;
	this.tabledata=tabledata;
	this.today_color='#8FBC8F';
	this.col = [1,1,1];
	
	this.Show = function(tag)
	{
		self.CreateTable(tag);
	}
	this.AddRow = function(row)
	{
		this.table.append(row);
	}
	this.CreateTable = function(tag)
	{
		var table = $('<table>');
		table.addClass("RmoTable");
		$('#'+tag).append(table);
		var row=1;
		yearrow = self.GenerateYearRow(row++);
		table.append(yearrow);
		
		monthrow = self.GenerateMonthRow(row++);
		table.append(monthrow);
		
		sprintrow = self.GenerateSprintRow(row++);
		table.append(sprintrow);
		
		weekrow = self.GenerateWeekRow(row++);
		table.append(weekrow);
		
		
		
		dayrow = self.GenerateDayRow(row++);
		dayrow.attr("height","15px");
		dayrow.attr("width","15px");
		table.append(dayrow);
		this.table = table;
	
	}
	this.AppendRow= function(row)
	{
		this.table.append(row);
	}
	this.MonthName = function(month)
	{
		if(month == 1)
			return "Jan";
		else if(month == 2)
			return "Feb";
		else if(month == 3)
			return "Mar";
		else if(month == 4)
			return "Apr";
		else if(month == 5)
			return "May";
		else if(month == 6)
			return "Jun";
		else if(month == 7)
			return "Jul";
		else if(month == 8)
			return "Aug";
		else if(month == 9)
			return "Sep";
		else if(month == 10)
			return "Oct";
		else if(month == 11)
			return "Nov";
		else if(month == 12)
			return "Dec";
	
		return month;
	}
	this.GenerateYearRow = function(r)
	{
		var yearArray = this.tabledata.years;
		
		var c=1;
		var row = $('<tr>');
		row.addClass("rowyear");
		
		
		if(this.col[0])
		{
			var col = $('<th>');
			col.html('&nbsp&nbsp&nbsp&nbsp&nbsp&nbspYear&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp');
			col.attr('id','r'+r+'c'+c++);
			row.append(col);
			col.addClass('sticky-col');
		}
		
		if(this.col[1])
		{
			var col = $('<th>');
			col.html('');
			col.attr('id','r'+r+'c'+c++);
			col.addClass('sticky-col1');
			row.append(col);
		}
		if(this.col[2])
		{
			var col = $('<th>');
			col.attr('id','r'+r+'c'+c++);
			col.html('');
			row.append(col);
		}
		var color='#DCDCDC';
		for (var year in yearArray) 
		{
			var today = yearArray[year].includes(1);

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
		monthArray = this.tabledata.months;
		//console.log(monthArray);
		var row = $('<tr>');
		row.addClass("rowmonth");
		var c=1;
		
		if(this.col[0])
		{
			var col = $('<th>');
			row.append(col);
			col.attr('id','r'+r+'c'+c++);
			col.html('Month');
			col.addClass('sticky-col');
		}
		
		if(this.col[1])
		{
			var col = $('<th>');
			col.attr('id','r'+r+'c'+c++);
			col.addClass('sticky-col1');
			row.append(col);
		}
		
		if(this.col[2])
		{
			var col = $('<th>');
			col.attr('id','r'+r+'c'+c++);
			row.append(col);
		}
		var color='#DCDCDC';
		for (var month in monthArray) 
		{
			var today = monthArray[month].includes(1);
			
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
		weekArray = this.tabledata.weeks;
		var c=1;
		var row = $('<tr>');
		row.addClass("rowweek");
		
		if(this.col[0])
		{
			var col = $('<th>');
			col.attr('id','r'+r+'c'+c++);
			col.html('Week');
			row.append(col);
			col.addClass('sticky-col');
		}
		
		if(this.col[1])
		{
			var col = $('<th>');
			col.attr('id','r'+r+'c'+c++);
			col.addClass('sticky-col1');
			row.append(col);
		}
		
		if(this.col[2])
		{
			var col = $('<th>');
			col.attr('id','r'+r+'c'+c++);
			row.append(col);
		}
		var color='#DCDCDC';
		for (var week in weekArray) 
		{
			var today = weekArray[week].includes(1);
			
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
	this.GenerateWeekRowT2 =  function(r)
	{
		weekArray = this.tabledata.weeks;
		var c=1;
		var row = $('<tr>');
		row.addClass("rowweek");
		
		if(this.col[0])
		{
			var col = $('<th style="white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:230px;"  >');
			col.attr('id',r+'1');
			col.html('Week');
			row.append(col);
			col.addClass('sticky-col');
		}
		
		if(this.col[1])
		{
			var col = $('<th>');
			col.attr('id',r+'2');
			col.addClass('sticky-col1');
			row.append(col);
		}
		
		if(this.col[2])
		{
			var col = $('<th>');
			col.attr('id',r+'3');
			row.append(col);
		}
		var color='#DCDCDC';
		for (var week in weekArray) 
		{
			var today = weekArray[week].includes(1);
			
			if(today)
				color=this.today_color;
			else
			{
				if(color==this.today_color)
					color='#FFFFFF';
			}
			colspan = Object.keys(weekArray[week]).length;
			col = $('<th colspan="'+colspan+'">');
			col.attr('id',r+'_'+week);
			year = week.substring(0,4);
			weeknum = week.substring(5);
			//col.html(weeknum);
			col.css('background-color',color);
			col.css('font-size','12px');
			row.append(col);
		}
		return row;
	}
	this.GenerateSprintRow =  function(r)
	{
		sprintArray = this.tabledata.sprints;
		var row = $('<tr>');
		var c=1;
		
		if(this.col[0])
		{
			var col = $('<th>');
			col.attr('id','r'+r+'c'+c++);
			col.html('Sprint');
			row.append(col);
			col.addClass('sticky-col');
		}
		
		if(this.col[1])
		{
			var col = $('<th>');
			col.attr('id','r'+r+'c'+c++);
			col.addClass('sticky-col1');
			row.append(col);
		}
		
		if(this.col[2])
		{
			var col = $('<th>');
			col.attr('id','r'+r+'c'+c++);
			row.append(col);
		}
		
		var color='#DCDCDC';
		for (var sprint in sprintArray) 
		{
			sprintdata = sprintArray[sprint];
			//console.log(sprintdata);
			start = sprintdata[0].date;
			start=new Date(start).toString().slice(4, 10);
			end = sprintdata[sprintdata.length-1].date;
			end=new Date(end).toString().slice(4, 10);
			
			var today = sprintdata.find(function(obj, index) 
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
			var html=sprint+"<br><span style='color:green;font-size:8px;'>"+start+"-"+end+"</span>";
			col.attr('title',start+"-"+end);
			if(colspan < 21)
				html = '';
			col.html(html);
			col.css('background-color',color);
			
			row.append(col);
		}
		return row;
	}
	this.GenerateSubSprintRow =  function(cls)
	{
		sprintArray = this.tabledata.sprints;
		var row = $('<tr>');
		row.addClass(cls);
		var c=1;
		
		if(this.col[0])
		{
			var col = $('<th>');
			row.append(col);
			col.html("Epics");
			col.addClass('sticky-col');
		}
		
		if(this.col[1])
		{
			var col = $('<th>');
			col.addClass('sticky-col1');
			row.append(col);
		}
		
		if(this.col[2])
		{
			var col = $('<th>');
			row.append(col);
		}
		
		var color='#DCDCDC';
		for (var sprint in sprintArray) 
		{
			sprintdata = sprintArray[sprint];
			//console.log(sprintdata);
			start = sprintdata[0].date;
			start=new Date(start).toString().slice(4, 10);
			end = sprintdata[sprintdata.length-1].date;
			end=new Date(end).toString().slice(4, 10);
			
			var today = sprintdata.find(function(obj, index) 
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
			var html=sprint+"<br><span style='color:green;font-size:8px;'>"+start+"-"+end+"</span>";
			col.attr('title',start+"-"+end);
			if(colspan < 21)
				html = '';
			col.html(html);
			col.css('background-color',color);
			
			row.append(col);
		}
		return row;
	}
	this.GenerateSprintDataRow =  function(id,classname='noclass')
	{
		sprintArray = this.tabledata.sprints;
		var row = $('<tr>');
		var c=1;
		row.addClass(classname);
		if(this.col[0])
		{
			var col = $('<th style="padding: 0px 5px 0px 15px;">');
			col.attr('id',id+'_cell'+c);
			col.html('<i style="font-size:10px;" data-key="'+classname+'" class="expand fa fa-plus" aria-hidden="true"></i>');
			var span = $('<span style="padding-left:5px;padding-right:5px;font-weight:bold;"></span>');
			span.attr('id',id+'_'+c++);
			col.append(span);
			col.addClass('sticky-col');
			//col.html('Sprint');
			row.append(col);
		}
		
		if(this.col[1])
		{
			var col = $('<th style="text-align:left;padding: 0px 5px 0px 5px;">');
			col.attr('id',id+'_'+c++);
			col.html('<span style="padding-left:15px;padding-right:5px">'+''+'</span>');
			col.addClass('sticky-col1');
			row.append(col);
		}
		
		if(this.col[2])
		{
			var col = $('<th style="padding:0px;">');
			col.attr('id',id+'_'+c++);
			col.html('<span style="padding-left:5px;padding-right:5px">'+''+'</span>');
			row.append(col);
		}
		
		var color='#DCDCDC';
		for (var sprint in sprintArray) 
		{
			sprintdata = sprintArray[sprint];
			//console.log(sprintdata);
			start = sprintdata[0].date;
			start=new Date(start).toString().slice(4, 10);
			end = sprintdata[sprintdata.length-1].date;
			end=new Date(end).toString().slice(4, 10);
			
			var today = sprintdata.find(function(obj, index) 
			{
				if(obj.today == 1)
					return true;
			});
			
		
			if(today)
			{
				color='#E9FFE2';
			}
			else
			{
				if(color=='#E9FFE2')
					color='#FFFFFF';
			}
			colspan = Object.keys(sprintArray[sprint]).length;
			col = $('<th colspan="'+colspan+'">');
			col.attr('id',id+'_'+sprint);
			col.css('font-size','10px');
			col.css('padding','0px');
			//var html=sprint+"<br><span style='color:green;font-size:8px;'>"+start+"-"+end+"</span>";
			//col.attr('title',start+"-"+end);
			if(colspan < 21)
				html = '';
			//col.html(html);
			col.css('background-color',color);
			//col.css('color','blue');
			row.append(col);
		}
		return row;
	}
	this.GenerateSprintChildDataRow =  function(id,classname='noclass',expand=0)
	{
		sprintArray = this.tabledata.sprints;
		var row = $('<tr>');
		var c=1;
		row.addClass(classname);
		
		if(this.col[0])
		{
			var col = $('<th style="text-align:left;padding: 0px 5px 0px 17px;">');
			//col.attr('id',id+'_'+c++);
			if(expand==1)
			{
				col.html('<i style="text-align:left;font-size:10px" class="expand fa fa-plus" aria-hidden="true"></i>');
				var span = $('<span style="text-align:left;font-size:10px;padding-left:0px;padding-right:5px;font-weight:normal;"></span>');
			}
			else
				var span = $('<span style="text-align:left;font-size:10px;padding-left:20px;padding-right:5px;font-weight:normal;"></span>');
			span.attr('id',id+'_'+c++);
			col.append(span);
			col.addClass('sticky-col');
			//col.html('Sprint');
			row.append(col);
		}
		
		if(this.col[1])
		{
			var col = $('<th style="font-weight: normal;text-align:left;padding: 0px 5px 0px 5px;">');
			col.attr('id',id+'_'+c++);
			//col.html('<span style="padding-left:35px;padding-right:5px">'+'Sprint'+'</span>');
			col.addClass('sticky-col1');
			row.append(col);
		}
		
		if(this.col[2])
		{
			var col = $('<th style="padding:0px;">');
			col.attr('id',id+'_'+c++);
			col.html('<span style="padding-left:5px;padding-right:5px">'+''+'</span>');
			row.append(col);
		}
		
		var color='#DCDCDC';
		for (var sprint in sprintArray) 
		{
			sprintdata = sprintArray[sprint];
			//console.log(sprintdata);
			start = sprintdata[0].date;
			start=new Date(start).toString().slice(4, 10);
			end = sprintdata[sprintdata.length-1].date;
			end=new Date(end).toString().slice(4, 10);
			
			var today = sprintdata.find(function(obj, index) 
			{
				if(obj.today == 1)
					return true;
			});
			
		
			if(today)
			{
				color='#E9FFE2';
			}
			else
			{
				if(color=='#E9FFE2')
					color='#FFFFFF';
			}
			colspan = Object.keys(sprintArray[sprint]).length;
			col = $('<th colspan="'+colspan+'">');
			col.attr('id',id+'_'+sprint);
			col.css('font-size','10px');
			col.css('padding','0px');
			//var html=sprint+"<br><span style='color:green;font-size:8px;'>"+start+"-"+end+"</span>";
			//col.attr('title',start+"-"+end);
			if(colspan < 21)
				html = '';
			//col.html(html);
			col.css('background-color',color);
			col.css('color','#0066FF');
			row.append(col);
		}
		return row;
	}
	this.GenerateDayRow =  function(r)
	{
		dayArray =  this.tabledata.days;
		var row = $('<tr>');
		row.addClass("rowday");
		var c=1;
		
		if(this.col[0])
		{
			var col = $('<th style="padding:0px;">');
			col.attr('id','r'+r+'c'+c++);
			col.html('<span style="padding:0px;font-weight:bold;">Risk/Dependency</span>');
			row.append(col);
			col.addClass('sticky-col');
		}
		
		if(this.col[1])
		{
			var col = $('<th>');
			col.attr('id','r'+r+'c'+c++);
			col.addClass('sticky-col1');
			row.append(col);
		}
		
		if(this.col[2])
		{
			var col = $('<th>');
			col.html('<span style="padding:0px;font-weight:bold;">Product</span>');

			col.attr('id','r'+r+'c'+c++);
			row.append(col);
		}
		var color='#DCDCDC';
		var todate =  new Date();
		todate = todate.toDateString();
		for (var day in dayArray) 
		{
			var date =  new Date(day);
			
			var today = (todate === date.toDateString());
			
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
			
			col.attr('title',date.toDateString());
			//col.html('<span style="font-size:5px;color:red;">'+day.substring(8,10)+'</span>');
			row.append(col);
		}
		return row;
	}
}