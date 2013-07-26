<?php

$filePath = "../log.txt";

// Load the schedule file from disk. If it's not there, present the user
// with a blank form.
if (file_exists($filePath)) {
    $contents = file_get_contents($filePath);
    $lines = explode("\n", $contents);
    $lines = array_reverse($lines);
}

?>
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
      p, h5 {
          margin-left: 2px;
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
				<li><a href="buttons.php">Control</a></li>
				<li class="active"><a href="log.php">Event Log</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">

      <h1>Event Log</h1>
      <h5>Displaying newest first</h5>
      
      <?php 
      foreach ($lines as $line) {
          $line = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;" , $line);
          echo "<p>$line</p>\n";
      }
      ?>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="http://twitter.github.io/bootstrap/assets/js/jquery.js"></script>
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-dropdown.js"></script>
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-button.js"></script>
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-collapse.js"></script>

  </body>
</html>