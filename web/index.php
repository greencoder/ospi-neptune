<html>

<head>
<meta name="viewport" content="width=device-width">
<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="apple-touch-icon-precomposed" href="icon.png"/>  
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script>

$(document).ready(function() {

	$('button.on').click(function() {
		var station = $(this).attr('id');
		alert("Turning on Station " + station + " for 15 minutes");		
		$.get('/ajax.php?station=' + station + '&minutes=15');
	});

	$('button.off').click(function() {
		var station = $(this).attr('id');
		alert("Turning off Station " + station);
		$.get('/ajax.php?station=0&minutes=0');
	});
	
});

</script>

<style type="text/css">

body {
	margin: 15 auto;
	text-align: center;
}

.row {
	margin-bottom: 18px;
}

button {
	height: 44px;
	width: 120px;
	font-size: 12pt;
}

button.off {
	margin-left: 30px;
}

</style>
</head>

<body>
	
<div id="container">

	<div class="row">
		<button class="on" id="1">Zone 1 On</button>
		<button class="off" id="1">Zone 1 Off</button>
	</div>

	<div class="row">
		<button class="on" id="2">Zone 2 On</button>
		<button class="off" id="2">Zone 2 Off</button>
	</div>

	<div class="row">
		<button class="on" id="3">Zone 3 On</button>
		<button class="off" id="3">Zone 3 Off</button>
	</div>

	<div class="row">
		<button class="on" id="4">Zone 4 On</button>
		<button class="off" id="4">Zone 4 Off</button>
	</div>

	<div class="row">
		<button class="on" id="5">Zone 5 On</button>
		<button class="off" id="5">Zone 5 Off</button>
	</div>

	<div class="row">
		<button class="on" id="6">Zone 6 On</button>
		<button class="off" id="6">Zone 6 Off</button>
	</div>

	<div class="row">
		<button class="on" id="7">Zone 7 On</button>
		<button class="off" id="7">Zone 7 Off</button>
	</div>

</div>

</body>
</html>