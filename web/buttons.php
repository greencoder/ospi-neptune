<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Neptune</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="http://twitter.github.io/bootstrap/assets/css/bootstrap.css" rel="stylesheet">
    <style>

		body {
			padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
		}

		.btn_row {
			margin-bottom: 18px;
		}

		button.off {
			margin-left: 15px;
		}

    </style>
    <link href="http://twitter.github.io/bootstrap/assets/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://twitter.github.io/bootstrap//assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
	<link rel="apple-touch-icon-precomposed" href="icon.png">
    <link rel="shortcut icon" href="favicon.ico">

    <script src="http://twitter.github.io/bootstrap/assets/js/jquery.js"></script>
	<script>
	$(document).ready(function() {

		$('button.on').click(function() {
			var station = $(this).attr('number');
			var response = prompt("How many minutes should the station run?", 15);			
			var minutes = parseInt(response);
			
			if (!isNaN(minutes)) {
				$.get('/ajax.php?station=' + station + '&minutes=' + minutes);	
			}
			else {
				alert("Invalid Number of Minutes.");			
			}			
		});

		$('button.off').click(function() {
			var station = $(this).attr('number');
			alert("Turning off Station " + station);
			$.get('/ajax.php?station=0&minutes=0');
		});

	});

	</script>

  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="#">Neptune</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
				<li><a href="index.php">Schedule</a></li>
				<li><a href="delay.php">Create Delay</a></li>
				<li class="active"><a href="buttons.php">Control</a></li>
				<li><a href="log.php">Event Log</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container text-center">

			<div class="btn_row">
				<button class="btn btn-success btn-large on" number="1" type="button">Zone 1 On</button>
				<button class="btn btn-danger btn-large off" number="1" type="button">Zone 1 Off</button>
			</div>

			<div class="btn_row">
				<button class="btn btn-success btn-large on" number="2" type="button">Zone 2 On</button>
				<button class="btn btn-danger btn-large off" number="2" type="button">Zone 2 Off</button>
			</div>

			<div class="btn_row">
				<button class="btn btn-success btn-large on" number="3" type="button">Zone 3 On</button>
				<button class="btn btn-danger btn-large off" number="3" type="button">Zone 3 Off</button>
			</div>

			<div class="btn_row">
				<button class="btn btn-success btn-large on" number="4" type="button">Zone 4 On</button>
				<button class="btn btn-danger btn-large off" number="4" type="button">Zone 4 Off</button>
			</div>

			<div class="btn_row">
				<button class="btn btn-success btn-large on" number="5" type="button">Zone 5 On</button>
				<button class="btn btn-danger btn-large off" number="5" type="button">Zone 5 Off</button>
			</div>

			<div class="btn_row">
				<button class="btn btn-success btn-large on" number="6" type="button">Zone 6 On</button>
				<button class="btn btn-danger btn-large off" number="6" type="button">Zone 6 Off</button>

			</div>

			<div class="btn_row">
				<button class="btn btn-success btn-large on" number="7" type="button">Zone 7 On</button>
				<button class="btn btn-danger btn-large off" number="7" type="button">Zone 7 Off</button>

			</div>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-dropdown.js"></script>
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-button.js"></script>
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-collapse.js"></script>

  </body>
</html>