<?php

include("config.inc.php");

$isDelay = false;

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $action = $_POST['action'];

    if ($action == "DELETE") {
        if (file_exists($delayFilePath)) {
            unlink($delayFilePath);
        }
        $successAlert = "<strong>Success!</strong>&nbsp;Delay file removed.";
    }

    if ($action == "SET") {
        $hours = $_POST['hours'];
        $fp = fsockopen($serverHost, $serverPort, $errno, $errdesc);
    	$cmd = "{\"cmd\":\"create-delay\", \"args\":{\"hours\":\"$hours\" }}";
    	fputs($fp, $cmd);
    	fclose($fp);
		$successAlert = "<strong>Success!</strong>&nbsp;Delay file created.";
		// Give the system time to write the file.
		sleep(1);
    }
}

// Try to load the delay file
if (file_exists($delayFilePath)) {
    $contents = file_get_contents($delayFilePath);
    $infoAlert = "Delay in effect until $contents";
    $isDelay = true;
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
      h1 {
          margin-bottom: 25px;
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
       
		$('#btnDelete').click(function() {
			$('#action').val("DELETE");
			$('form').submit();
		});

		// Make sure they can only enter numbers
		$("input.numeric").bind({
			 keydown: function(e) {
				if (e.shiftKey === true ) {
		            if (e.which == 9) {
		                return true;
		            }
		            return false;
		        }
		        if (e.which > 57) {
		            return false;
		        }
		        if (e.which==32) {
		            return false;
		        }
		        return true;
		    }
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
				<li class="active"><a href="delay.php">Create Delay</a></li>
				<li><a href="buttons.php">Control</a></li>
				<li><a href="log.php">Event Log</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">
    
        <?php 
        if (isset($successAlert)) { ?>
            <div class='alert alert-success'><?php echo $successAlert; ?></div>
        <?php } ?>

        <?php 
        if (isset($infoAlert)) { ?>
            <div class='alert alert-info'><?php echo $infoAlert; ?></div>
        <?php } ?>

      <h1>Create Delay</h1>
      
      <p>
        <?php if ($isDelay) { ?>
            <a class="btn btn-danger btn-default" id="btnDelete" href="#">
                <i class="icon-remove icon-white"></i>&nbsp;&nbsp;Remove Delay File
            </a>&nbsp;
	        <form action="" method="POST">
	            <input type="hidden" id="action" name="action" value="DELETE" />
	        </form>
        <?php } else { ?>
            <form class="form-horizontal" action="" method="POST">
				<input type="hidden" id="action" name="action" value="SET" />
                <div class="control-group">
                    <label class="control-label" for="hours">Number of Hours</label>
                    <div class="controls">
                        <input required type="text" class="input-mini numeric" id="hours" name="hours" placeholder="Hours">  
                  		&nbsp;
						<input type="submit" class="btn btn-default">
                    </div>
                </div>
            </form>
            
        <?php } ?>
     </p>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-dropdown.js"></script>
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-button.js"></script>
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-collapse.js"></script>

  </body>
</html>