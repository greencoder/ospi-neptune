<?php

include('header.inc.php');
include("config.inc.php");

$filePath = "../log.txt";

// Load the schedule file from disk. If it's not there, present the user
// with a blank form.
if (file_exists($filePath)) {
    $contents = file_get_contents($filePath);
    $lines = explode("\n", $contents);
    $lines = array_reverse($lines);
}

?>

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

  </body>
</html>