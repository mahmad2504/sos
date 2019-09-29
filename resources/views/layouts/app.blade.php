<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
	<script src="{{ asset('js/app.js') }}" ></script>
    <!-- Scripts -->
   


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
	
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('css/screen.css') }}" rel="stylesheet">
	<link href="{{ asset('css/loading.css') }}" rel="stylesheet">

    @yield('csslinks')
    <style>
	.modal-content 
	{
		background-clip: border-box;
		border: none !important;
	}
	@yield('style')
	</style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm d-none">
            <div class="container">
                <a id="home_menuitem" class="navbar-brand" href="{{ url('/') }}">
                    <img style="height:30px;" src="/images/logo.png"></img>
                </a>
				<!-- <a id="dashboard_menuitem" style="display:none" class="navbar-brand" href="{{ url('/') }}">
                    Dashboard
                </a> -->
				<a id="calendar_menuitem" style="display:none" class="navbar-brand" href="{{ url('/calendars') }}">
                    Calendars
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    Logged in as {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
									<a class="dropdown-item" href="{{ route('showchangepassword') }}">
                                       {{ __('Change Password') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        <main class="py-4">
			<div style="display:none;" class="loading">Loading&#8230;</div>
			@yield('content')
			<footer style="text-align: center;width:90%;" class="container-fluid">
				<small style="color:grey" >Stay On Schedule &#169; <a style="color:grey" href="mailto:Mumtaz_Ahmad@mentor.com">Mumtaz Ahmad</a>
				 <a href="https://www.linkedin.com/in/mumtazahmad2">
					<i class="fab fa-linkedin"></i>
				</a>
				<a href="https://github.com/mahmad2504/sos"> <span style="color:grey">Code</span>  
					<i class="fab fa-github"></i>
				</a>
				</small>
			</footer>
        </main>
    </div>
	<script>
		Date.prototype.getWeek = function() 
		{
			var date = new Date(this.getTime());
			date.setHours(0, 0, 0, 0);
			// Thursday in current week decides the year.
			date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
			// January 4 is always in week 1.
			var week1 = new Date(date.getFullYear(), 0, 4);
			// Adjust to Thursday in week 1 and count number of weeks from date to week1.
			return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
		}
		function getDateRangeOfWeek(weekNo, y){
			var d1, numOfdaysPastSinceLastMonday, rangeIsFrom, rangeIsTo;
			d1 = new Date(''+y+'');
			numOfdaysPastSinceLastMonday = d1.getDay() - 1;
			d1.setDate(d1.getDate() - numOfdaysPastSinceLastMonday);
			d1.setDate(d1.getDate() + (7 * (weekNo - d1.getWeek())));
			rangeIsFrom = (d1.getMonth() + 1) + "-" + d1.getDate() + "-" + d1.getFullYear();
			d1.setDate(d1.getDate() + 6);
			rangeIsTo = (d1.getMonth() + 1) + "-" + d1.getDate() + "-" + d1.getFullYear() ;
			var r = {};
			
			r.from = rangeIsFrom;
			r.to = rangeIsTo;
			return r;
		};

		function ShowCalendarMenuItem()
		{
			$('#calendar_menuitem').show();	
		}
		function ShowLoading()
		{
			$('.loading').show();	
		}
		function HideLoading()
		{
			$('.loading').hide();	
		}
		function MakeDate(day,month,year)
		{
			//var now = new Date();
			var day = ("0" + day).slice(-2);
			var month = ("0" + month).slice(-2);
			var today = year+"-"+(month)+"-"+(day) ;
			return today;
		}
		function ShowNavBar()
		{
			$('.navbar').removeClass('d-none');	
		}
		function HideNavBar()
		{
			$('.navbar').addClass('d-none');	
		}
		var dates = {
			year:function()
			{
				var today = new Date();
				return today.getFullYear();
			},
			month:function()
			{
				var today = new Date();
				return today.getMonth();
			},
			day:function()
			{
				var today = new Date();
				return today.getDate();
			},
			convert:function(d) {
				// Converts the date in d to a date-object. The input can be:
				//   a date object: returned without modification
				//  an array      : Interpreted as [year,month,day]. NOTE: month is 0-11.
				//   a number     : Interpreted as number of milliseconds
				//                  since 1 Jan 1970 (a timestamp) 
				//   a string     : Any format supported by the javascript engine, like
				//                  "YYYY/MM/DD", "MM/DD/YYYY", "Jan 31 2009" etc.
				//  an object     : Interpreted as an object with year, month and date
				//                  attributes.  **NOTE** month is 0-11.
				return (
					d.constructor === Date ? d :
					d.constructor === Array ? new Date(d[0],d[1],d[2]) :
					d.constructor === Number ? new Date(d) :
					d.constructor === String ? new Date(d) :
					typeof d === "object" ? new Date(d.year,d.month,d.date) :
					NaN
				);
			},
			compare:function(a,b) {
				// Compare two dates (could be of any type supported by the convert
				// function above) and returns:
				//  -1 : if a < b
				//   0 : if a = b
				//   1 : if a > b
				// NaN : if a or b is an illegal date
				// NOTE: The code inside isFinite does an assignment (=).
				return (
					isFinite(a=this.convert(a).valueOf()) &&
					isFinite(b=this.convert(b).valueOf()) ?
					(a>b)-(a<b) :
					NaN
				);
			},
			inRange:function(d,start,end) {
				// Checks if date in d is between dates in start and end.
				// Returns a boolean or NaN:
				//    true  : if d is between start and end (inclusive)
				//    false : if d is before start or after end
				//    NaN   : if one or more of the dates is illegal.
				// NOTE: The code inside isFinite does an assignment (=).
			   return (
					isFinite(d=this.convert(d).valueOf()) &&
					isFinite(start=this.convert(start).valueOf()) &&
					isFinite(end=this.convert(end).valueOf()) ?
					start <= d && d <= end :
					NaN
				);
			}
		}
		//yyyy-mm-dd
		function MakeDate2(string) {
			dateObj = new Date(string).toUTCString();
			if(dateObj === 'Invalid Date')
				return '';
			
    		return dateObj.slice(4,16);
		}
		function MakeDate(day,month,year)
		{
			//var now = new Date();
			var day = ("0" + day).slice(-2);
			var month = ("0" + month).slice(-2);
			var today = year+"-"+(month)+"-"+(day) ;
			return today;
		}
		function sleep(ms) {
		  return new Promise(resolve => setTimeout(resolve, ms));
		}

		function GetDay()
		{
			var today = new Date();
			return today.getDate();
		}
		function GetMonth()
		{
			var today = new Date();
			return today.getMonth();
		}
		function GetYear()
		{
			var today = new Date();
			return today.getFullYear();
		}

		function removeTrailingZeros(value) {
			value = value.toString();

			if (value.indexOf('.') === -1) {
				return value;
			}

			while((value.slice(-1) === '0' || value.slice(-1) === '.') && value.indexOf('.') !== -1) {
				value = value.substr(0, value.length - 1);
			}
			return value;
		}
		function endOfMonth(date)
		{
			var ndate = new Date(date.getFullYear(), date.getMonth()+1, 0);
			var year = ndate.getFullYear();
			var month = ndate.getMonth();
			var day = ndate.getDate();
			return year+"-"+("0" + (month + 1)).slice(-2)+"-"+day;
		}
		function endOfWeek(date)
		{
			var lastday = date.getDate() - (date.getDay() - 1) + 6;
			var ndate = new Date(date.setDate(lastday));
				var year = ndate.getFullYear();
				var month = ndate.getMonth();
				var day = ndate.getDate();
				return year+"-"+("0" + (month + 1)).slice(-2)+"-"+day;
		}
		function Round(val)
		{
            return  Math.round( val * 10 ) / 10;
		}
		function ConvertDateToString(datestr)
		{
			var d = new Date(datestr);
			if(d == 'Invalid Date')
				return '';
			
			dateString = d.toUTCString();
			dateString = dateString.split(' ').slice(0, 4).join(' ').substring(5);
			return dateString;
		}
		function ConvertDateFormat(datestr)
		{
			var d = new Date(datestr);
			if(d == 'Invalid Date')
				return '';
			
			var weekno = ISO8601_week_no(d);
			var dayno = ISO8601_day_no(d);
			var yearno = ISO8601_year_no(d);
			
			return yearno+"W"+weekno+"."+dayno;
		}
		function ISO8601_year_no(dt)
		{
			return dt.getFullYear().toString().substr(2, 2);;
		}
		function ISO8601_day_no(dt) 
		{
			var tdt = new Date(dt.valueOf());
			var dayn = (dt.getDay() + 6) % 7;
			return dayn+1;
		}
		function ISO8601_week_no(dt) 
		{
			var tdt = new Date(dt.valueOf());
			var dayn = (dt.getDay() + 6) % 7;
			tdt.setDate(tdt.getDate() - dayn + 3);
			var firstThursday = tdt.valueOf();
			tdt.setMonth(0, 1);
			if (tdt.getDay() !== 4) 
			{
				tdt.setMonth(0, 1 + ((4 - tdt.getDay()) + 7) % 7);
			}
			return 1 + Math.ceil((firstThursday - tdt) / 604800000);
		}
		function IsVleocityLow(cv,rv)
		{
			if(cv < (85/100)*rv)
				return 1;
			return 0;
		}
		@yield('script')
		</script>
		
</body>
</html>
