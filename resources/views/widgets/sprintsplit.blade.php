@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
<link rel="stylesheet" href="{{ asset('css/rmo.css') }}" />
@endsection
@section('style')
.sticky-col {
  position: sticky;
  position: -webkit-sticky;
  background-color: white;
  border: 1px solid black;
  left: 0px;
}

.sticky-col1 {
  position: sticky;
  position: -webkit-sticky;
  background-color: white;
  border: 1px solid black;
  left: 105px;
}
@endsection
@section('content')
<div  class="flex" style="height:35px; border: 3px solid #4682B4;background:#2e5790;">
<h3>{{ $project->name}}</h3>
</div>

<div id="divtable_header" class=""></div>
<div id="divtable" class=""></div>
<hr>

<div style="overflow-x: scroll;">
	<div id="table"></div>
</div>

<script src="{{ asset('js/msc-script.js') }}" ></script>
<script src="{{ asset('rmo/rmo.js') }}" ></script>
@endsection
@section('script')
var tabledata = @json($tabledata);
var data=@json($data);
var teams = @json($teams);
var url = "{{$url}}";
var colors=['#c1ff86','#ffff86'];
for(var team in teams)
{
	teams[team] = colors.pop();
	
}
/*
    blend two colors to create the color that is at the percentage away from the first color
    this is a 5 step process
        1: validate input
        2: convert input to 6 char hex
        3: convert hex to rgb
        4: take the percentage to create a ratio between the two colors
        5: convert blend to hex
    @param: color1      => the first color, hex (ie: #000000)
    @param: color2      => the second color, hex (ie: #ffffff)
    @param: percentage  => the distance from the first color, as a decimal between 0 and 1 (ie: 0.5)
    @returns: string    => the third color, hex, represenatation of the blend between color1 and color2 at the given percentage
*/
function blend_colors(color1, color2, percentage)
{
    // check input
    color1 = color1 || '#000000';
    color2 = color2 || '#ffffff';
    percentage = percentage || 0.5;

    // 1: validate input, make sure we have provided a valid hex
    if (color1.length != 4 && color1.length != 7)
        throw new error('colors must be provided as hexes');

    if (color2.length != 4 && color2.length != 7)
        throw new error('colors must be provided as hexes');    

    if (percentage > 1 || percentage < 0)
        throw new error('percentage must be between 0 and 1');

    // output to canvas for proof
    var cvs = document.createElement('canvas');
    var ctx = cvs.getContext('2d');
    cvs.width = 90;
    cvs.height = 25;
    //document.body.appendChild(cvs);

    // color1 on the left
    ctx.fillStyle = color1;
    ctx.fillRect(0, 0, 30, 25);

    // color2 on the right
    ctx.fillStyle = color2;
    ctx.fillRect(60, 0, 30, 25);

    // 2: check to see if we need to convert 3 char hex to 6 char hex, else slice off hash
    //      the three character hex is just a representation of the 6 hex where each character is repeated
    //      ie: #060 => #006600 (green)
    if (color1.length == 4)
        color1 = color1[1] + color1[1] + color1[2] + color1[2] + color1[3] + color1[3];
    else
        color1 = color1.substring(1);
    if (color2.length == 4)
        color2 = color2[1] + color2[1] + color2[2] + color2[2] + color2[3] + color2[3];
    else
        color2 = color2.substring(1);   

    //console.log('valid: c1 => ' + color1 + ', c2 => ' + color2);

    // 3: we have valid input, convert colors to rgb
    color1 = [parseInt(color1[0] + color1[1], 16), parseInt(color1[2] + color1[3], 16), parseInt(color1[4] + color1[5], 16)];
    color2 = [parseInt(color2[0] + color2[1], 16), parseInt(color2[2] + color2[3], 16), parseInt(color2[4] + color2[5], 16)];

    //console.log('hex -> rgba: c1 => [' + color1.join(', ') + '], c2 => [' + color2.join(', ') + ']');

    // 4: blend
    var color3 = [ 
        (1 - percentage) * color1[0] + percentage * color2[0], 
        (1 - percentage) * color1[1] + percentage * color2[1], 
        (1 - percentage) * color1[2] + percentage * color2[2]
    ];

    //console.log('c3 => [' + color3.join(', ') + ']');

    // 5: convert to hex
    color3 = '#' + int_to_hex(color3[0]) + int_to_hex(color3[1]) + int_to_hex(color3[2]);

    //console.log(color3);

    // color3 in the middle
    ctx.fillStyle = color3;
    ctx.fillRect(30, 0, 30, 25);

    // return hex
    return color3;
}

/*
    convert a Number to a two character hex string
    must round, or we will end up with more digits than expected (2)
    note: can also result in single digit, which will need to be padded with a 0 to the left
    @param: num         => the number to conver to hex
    @returns: string    => the hex representation of the provided number
*/
function int_to_hex(num)
{
    var hex = Math.round(num).toString(16);
    if (hex.length == 1)
        hex = '0' + hex;
    return hex;
}
function ShowChildRow(rmo,parent,child,pkey=null)
{
	var id = parent.key+"_"+child.key;
	var cls = "CHILD_"+parent.key;
	var row = rmo.GenerateSprintChildDataRow(id,cls,0);
	row.hide();
	rmo.AddRow(row);

	var link='<a  href="'+url+'/browse/'+child.key+'">'+child.key+'</a>';
	$('#'+id+"_1").html(link);
	
	var link='';
	if(pkey != null)
	{
		link='<a style="float:left;font-size:10px;" href="'+url+'/browse/'+pkey+'">'+pkey+'</a>&nbsp';
		$('#'+id+"_2").html(link+"/"+child.summary.substr(0,35));
	}
	else
		$('#'+id+"_2").html(child.summary.substr(0,40));
	
	$('#'+id+"_2").attr('title',child.summary);
	for(var team in child.sprintsplit)
	{
		var sprintsplit = child.sprintsplit[team];
		for(index in sprintsplit)
		{
			number=sprintsplit[index].number*1;
			year=child.sprintsplit[team][index].year*1;
			var nhtml = team + " "+ $('#'+id+"_"+year+"_"+number).html();
			var color=teams[team];
			$('#'+id+"_"+year+"_"+number).html(nhtml);
			var cur_color = $('#'+id+"_"+year+"_"+number).data('color');
			if(cur_color === undefined)
				cur_color = color;
			else
				cur_color =  blend_colors(cur_color, color, .4);

			$('#'+id+"_"+year+"_"+number).data('color',cur_color);
			$('#'+id+"_"+year+"_"+number).css('background-color',cur_color);
		}
	}
}
$(document).ready(function()
{
	$(document).ready(function()
	{
		console.log("Showing sprint table");
		var rmo = new Rmo(tabledata);
		rmo.Show("table");
		for(var i=0;i<data.length;i++)
		{
			//console.log(data[i]);
			var datarow = data[i];
			var j=0;
			var id = datarow.key;
			var cls = datarow.key;
			var row = rmo.GenerateSprintDataRow(id,cls);
			rmo.AddRow(row);
			var link='<a href="'+url+'/browse/'+datarow.key+'">'+datarow.key+'</a>';
			$('#'+id+"_1").html(link);
			
			$('#'+datarow.key+'_cell1').css("text-align","left");
		
			$('#'+id+"_2").html(datarow.summary.substr(0,40));
			$('#'+id+"_2").attr('title',datarow.summary);
			//console.log(datarow.sprintsplit);
		
			
			for(var team in datarow.sprintsplit)
			{
				var sprintsplit = datarow.sprintsplit[team];
				for(index in sprintsplit)
				{
					number=sprintsplit[index].number*1;
					year=data[i].sprintsplit[team][index].year*1;
					var nhtml = team + " "+ $('#'+id+"_"+year+"_"+number).html();
					var color=teams[team];
					$('#'+id+"_"+year+"_"+number).html(nhtml);
					var cur_color = $('#'+id+"_"+year+"_"+number).data('color');
					if(cur_color === undefined)
						cur_color = color;
					else
						cur_color =  blend_colors(cur_color, color, .4);

					$('#'+id+"_"+year+"_"+number).data('color',cur_color);
					$('#'+id+"_"+year+"_"+number).css('background-color',cur_color);
				}
			}
			//console.log(datarow);
			
	
			var k=0;
			for(var index in datarow.children)
			{
				var child = datarow.children[index];
				var id = datarow.key+"_"+child.key;
				var cls = "CHILD_"+datarow.key;
				if(k == 0)
				{
					var sprintrow = rmo.GenerateSubSprintRow(cls);
					rmo.AddRow(sprintrow);
					sprintrow.hide();
				}
				k++;
		
				if(child.children === undefined)
					ShowChildRow(rmo,datarow,child);
				else
				{
					for(var dindex in child.children)
					{
						var cchild = child.children[dindex];
						
						ShowChildRow(rmo,datarow,cchild,child.key);
					}
				}
				continue;
				if(child.children !== undefined)
					var row = rmo.GenerateSprintChildDataRow(id,cls,1);
				else
					var row = rmo.GenerateSprintChildDataRow(id,cls,0);
				row.hide();
				rmo.AddRow(row);
			
				var link='<a href="'+url+'/browse/'+child.key+'">'+child.key+'</a>';
				$('#'+id+"_1").html(link);
				$('#'+id+"_2").html(child.summary.substr(0,40));
				$('#'+id+"_2").attr('title',child.summary);
				for(var team in child.sprintsplit)
				{
					var sprintsplit = child.sprintsplit[team];
					for(index in sprintsplit)
					{
						number=sprintsplit[index].number*1;
						year=child.sprintsplit[team][index].year*1;
						var nhtml = team + " "+ $('#'+id+"_"+year+"_"+number).html();
						var color=teams[team];
						$('#'+id+"_"+year+"_"+number).html(nhtml);
						var cur_color = $('#'+id+"_"+year+"_"+number).data('color');
						if(cur_color === undefined)
							cur_color = color;
						else
							cur_color =  blend_colors(cur_color, color, .4);

						$('#'+id+"_"+year+"_"+number).data('color',cur_color);
						$('#'+id+"_"+year+"_"+number).css('background-color',cur_color);
					}
				}
				
			}
		}
		$('.expand').click(function(){
			var key = $(this).data('key');
			
			if( ($(this).data('expanded')==undefined)||($(this).data('expanded')==0))
			{
				$('#'+key+"_cell1").css('background-color','#FFA500');
				//$(this).css('background-color','red');
				$(this).data('expanded',1);
				$('.CHILD_'+key).fadeIn( "slow", function() {
					// Animation complete.
				});					
				$('.'+key).css("border-bottom","2pt solid black");
				$('.'+key).css("border-top","2pt solid black");
				
				$(".CHILD_"+key+":last").css("border-bottom","2pt solid black");
				$(this).removeClass('fa-plus');
				$(this).addClass('fa-minus');
			}
			else
			{
                                $('#'+key+"_cell1").css('background-color','#ffffff');
				$(this).data('expanded',0);
				$('.CHILD_'+key).fadeOut( "slow", function() {
					// Animation complete.
				});				
				$('.'+key).css("border-bottom","1pt");
				$('.'+key).css("border-top","1pt");
				$(".CHILD_"+key+":last").css("border-bottom","1pt");
				$(this).removeClass('fa-minus');
				$(this).addClass('fa-plus');
			}
				
		});
	});
})
@endsection