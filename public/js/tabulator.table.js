var currentYear = new Date().getFullYear();
var dataSource = [
            {
                id: 0,
                name: 'Google I/O',
                location: 'San Francisco, CA',
                startDate: new Date(currentYear, 4, 26),
                endDate: new Date(currentYear, 4, 29),
				color : 'green'
				
            },
            {
                id: 1,
                name: 'Microsoft Convergence',
                location: 'New Orleans, LA',
                startDate: new Date(currentYear, 2, 16),
                endDate: new Date(currentYear, 2, 19)
            },
            {
                id: 2,
                name: 'Microsoft Build Developer Conference',
                location: 'San Francisco, CA',
                startDate: new Date(currentYear, 3, 29),
                endDate: new Date(currentYear, 4, 1)
            },
            {
                id: 3,
                name: 'Apple Special Event',
                location: 'San Francisco, CA',
                startDate: new Date(currentYear, 8, 1),
                endDate: new Date(currentYear, 8, 1)
            },
            {
                id: 4,
                name: 'Apple Keynote',
                location: 'San Francisco, CA',
                startDate: new Date(currentYear, 8, 9),
                endDate: new Date(currentYear, 8, 9)
            },
            {
                id: 5,
                name: 'Chrome Developer Summit',
                location: 'Mountain View, CA',
                startDate: new Date(currentYear, 10, 17),
                endDate: new Date(currentYear, 10, 18)
            },
            {
                id: 6,
                name: 'F8 2015',
                location: 'San Francisco, CA',
                startDate: new Date(currentYear, 2, 25),
                endDate: new Date(currentYear, 2, 26)
            },
            {
                id: 7,
                name: 'Yahoo Mobile Developer Conference',
                location: 'New York',
                startDate: new Date(currentYear, 7, 25),
                endDate: new Date(currentYear, 7, 26)
            },
            {
                id: 8,
                name: 'Android Developer Conference',
                location: 'Santa Clara, CA',
                startDate: new Date(currentYear, 11, 1),
                endDate: new Date(currentYear, 11, 4)
            },
            {
                id: 9,
                name: 'LA Tech Summit',
                location: 'Los Angeles, CA',
                startDate: new Date(currentYear, 10, 17),
                endDate: new Date(currentYear, 10, 17)
            }
        ];

function OnCalendarShowClick(element)
{
	var username = $(element).data('username');
	ShowCalendar(username);
}



function InitTabulator()
{
	var openIcon = function(cell, formatterParams, onRendered){ //plain text value
		//return '<i class="fas fa-calendar-alt" data-toggle="modal" data-target="#calendar-modal" ></i>';
		var username = cell.getRow().getData().profile.name;
		if(username != 'unassigned')
			return '<span onclick="OnCalendarShowClick(this)" data-username="'+username+'" data-toggle="modal" data-backdrop="static" data-target="#calendar-modal" data-keyboard="false">&nbsp<i class="fas fa-calendar-alt"></i></span>';
	};
	//define custom formatter


	var settings = 
	{
		tooltips:true,
		layout:"fitDataFill",
		//pagination:'local', //enable local pagination.
        //paginationSize:15, // this option can take any positive integer value (default = 10)
		columnVertAlign:"bottom", 
		data:resources,
		//height:105, // set height of table (in CSS or here), this enables the Virtual DOM and improves render speed dramatically (can be any valid css height value)
		//ajaxURL:'/data/resources/'+username+"/"+projectname, //ajax URL
		//autoColumns:true,
		//ajaxResponse:function(url, params, response)
		//{
		//	console.log(response);
		//	if(response.status === undefined)
		//		return response;
		//	return [];
			
		//},
		columns:
		[
			{resizable: false,title:"",formatter:"rownum", align:"center", width:"3%", headerSort:false},
			{resizable: false,title:"Full Name",field:"profile.displayname", headerFilter:false, width:"15%"},
			{resizable: false,title:"User Name",field:"profile.name", headerFilter:false, width:"10%"},
			{resizable: false,title:"Email",field:"profile.email", headerFilter:false, width:"15%"},
			{resizable: false,title:"Country",field:"cc", headerFilter:false, width:"10%",editor:"select",
				editorParams:function(cell)
				{
					return {"values":{
						"PK":"Pakistan", 
						"IN":"India",
						"UK":"United Kingdom",
						"USA":"United States",
						"EG":"Egypt",
						"HU":"Hungary",
						"GE":"Germany",
						"RO":"Romania",
						"FR":"France",
						}};
				},
				/*cellClick:function(e, cell)
				{
					name = cell.getRow().getData().profile.name;
					if(name == 'unassigned')
						return '';
					return cell.getValue(); 
				}*/
				editable:function(cell){
					//cell - the cell component for the editable cell
					//get row data
					name = cell.getRow().getData().profile.name;
					if(name == 'unassigned')
						return false;
					return true; 
				}
			},
			{resizable: false,title:"Available", sortable:false,field:"efficiency",width:"8%",editor:"number",
				editorParams:{
					min:0,
					max:100,
					step:5,
				},
				formatter:function(cell, formatterParams, onRendered){
					//cell - the cell component
					//formatterParams - parameters set for the column
					//onRendered - function to call when the formatter has been rendered
					return cell.getValue()+"%"; //return the contents of the cell;
				},
				editable:function(cell){
					//cell - the cell component for the editable cell
					//get row data
					name = cell.getRow().getData().profile.name;
					if(name == 'unassigned')
						return false;
					return true; 
				}
			},
			{resizable: false,title:"$/hr", sortable:false,field:"cost",width:"8%",editor:"number",
				editorParams:{
					min:0,
					max:1000,
					step:5,
				},
				formatter:function(cell, formatterParams, onRendered){
					//cell - the cell component
					//formatterParams - parameters set for the column
					//onRendered - function to call when the formatter has been rendered
					name = cell.getRow().getData().profile.name;
					if(name == 'unassigned')
						return '';
					return cell.getValue()+" $/hr"; //return the contents of the cell;
				},
				editable:function(cell){
					//cell - the cell component for the editable cell
					//get row data
					name = cell.getRow().getData().profile.name;
					if(name == 'unassigned')
						return false;
					return true; 
				}
			
			},
			{resizable: false,title:"Team", sortable:false,field:"team",width:"20%",editor:"input"
			
			},
			{resizable: false,title:"Calendar", sortable:false, formatter:openIcon}
		],
		initialFilter:[
			
		],
		initialSort:
		[
			//{column:"received_date", dir:"dsc"} //sort by this first
		],
		renderComplete:function()
		{
			
		}
	};
	return settings;
}