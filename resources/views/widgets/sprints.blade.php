@extends('layouts.app')
@section('csslinks')

@endsection
@section('style')

ul {
    margin: 20px 60px;
}

ul li {
    display: inline-block;
    //height: 30px;
    line-height: 30px;
   
    margin: 5px 1px 0 0;
    text-indent: 20px;
    position: relative;
	font-size:2;
}
ul li.closed a{
	color:grey;
}
ul li:before {
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    left: -2px;
    border-style: solid;
    border-width: 15px 0 15px 15px;
    border-color: transparent transparent transparent #fff;
    z-index: 0;
	
}

ul li:first-child:before {
    border-color: transparent;
}

ul li a:after {
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    right: -15px;
    border-style: solid;
    border-width: 15px 0 15px 15px;
    border-color: transparent transparent transparent #ccc;
    z-index: 10;
}
ul li.future a {
    background: DarkSeaGreen ;
	color:white;
    z-index: 100;
}

ul li.future a:after {
    border-left-color: DarkSeaGreen ;
}

ul li.active a {
    background: green;
    z-index: 100;
	color:white;
}

ul li.active a:after {
    border-left-color: green;
}

ul li a {
    display: block;
    background: #ccc;
}

ul li a:hover {
    background: green;
}

ul li a:hover:after {
    border-color: transparent transparent transparent pink; 
}
.info {
	font-size:10px;
	line-height: 2;
	//position: absolute;
}
@endsection
@section('content')
<?php $i=0 ?>
<ul id="anchor">
	@foreach($sprints as $sprint)
		<?php    
		$estimate = $sprint['estimate'];
		$tstart = new DateTime($sprint['tstart']);
		$tstart = $tstart->format('d-M-Y');
		
		$tend = new DateTime($sprint['tend']);
		$tend = $tend->format('d-M-Y');
		
		
		$info = '<div class="info">'.$tstart." - ";
		$info .= $tend."(<span style='color:green;font-weight:bold'>".$estimate.'</span>)</div>';
		?>
		<li class="tile" id="tile{{$i++}}"><a href="#">{{$sprint['name']}}</a><?php echo $info; ?></li>
	@endforeach  
	
    <!-- <li><a href="#">Foobard<br>dsddsd</a></li>
    <li class="active"><a href="#">Fo</a></li>
    <li><a href="#">Foobar</a></li>
    <li><a href="#">Foobar</a></li>
    <li class="future"><a href="#">Foobar</a></li> -->
</ul>

@endsection
@section('script')
var sprints=Object.values(@json($sprints));
console.log(sprints);
$(function() 
{
	console.log(sprints.length);
	for(i=0;i<sprints.length;i++)
	{
		console.log(sprints[i]);
		$('#tile'+i).addClass(sprints[i]["state"]);
		
			
		title = sprints[i]['name'].substring(0,15);
		//$('#anchor').append('<li><a class="active" href="#" style="">'+title+'</a></li>');
	}
});		
@endsection