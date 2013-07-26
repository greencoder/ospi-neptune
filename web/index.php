<?php

include('config.inc.php');

// If it's a POST, get the contents and write to file
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	
	// entries is already encoded properly with JSON so all we have to do 
	// is write it to a file
    $entries = $_POST['entries'];
    file_put_contents($scheduleFilePath, $entries);

    $successAlert = "<strong>Success!</strong>&nbsp;Schedule file written.";
}

// Load the schedule file from disk. If it's not there, present the user
// with a blank form.
if (file_exists($scheduleFilePath)) {
    $contents = file_get_contents($scheduleFilePath);
    $schedule = json_decode($contents);
}
else {
    $schedule = [];
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

        .btn.active {
            background-color: #00CC66;
            color: #FFF;
        }

        .help {
            color: #ccc;
        }
        
        a.remove {
            text-decoration: none;
            color: #000;
        }

		h1 {
			margin-bottom: 25px;
		}
		
		.btn-default {
			margin-bottom: 10px;
		}
		
		.yellow-bg {
			background-color: yellow;
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
    <script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js"></script>
    
    <script>
    var numberFormsAdded = 0;

    $(document).ready(function() {
        
        $('#btnSave').click(function() {
            
            var entries = [];
            var hasErrors = false;
            
            // Loop through all forms
            $('form.entries').each(function(){

				// Get the field values and cast them
                var minutes = Number($(this).find("#inputMinutes").val());
                var station = Number($(this).find("#station").val());
                var startHour = $(this).find("#inputStartHour").val();
                var startMins = $(this).find("#inputStartMinute").val();

				// It's tricky to get the days. We have to look for buttons 
				// with the class 'active' applied.
                var days = [];
                $(this).find('.btn-group button.active').each(function() {
                    days.push($(this).data().value);
                });

				// startTime is a string concatenation
                var startTime = startHour + ":" + startMins;

                entries.push({
                    'station': station,
                    'minutes': minutes,
                    'start': startTime,
                    'days': days
                });

            });
            
            entries = _(entries).sortBy('startTime');
            var jsonEntries = JSON.stringify(entries, null, 4);
            
            if (!hasErrors) {
                $('#frmSchedule #entries').val(jsonEntries);
                $('#frmSchedule').submit();
            }
            
        });

        $('#btnAdd').click(function() {

            // First get the HTML template
            var html = $('#template').html();
            $('#entries').prepend(html);

            // Next change the form name
            var newForm = $('form[name="FRMTPL"]');
            newForm.attr('name', 'new' + numberFormsAdded);
            
            // Slide down the new form and increment our added form counter
            newForm.slideDown("slow");
            numberFormsAdded += 1;
            
            // Assign a click handler to the remove button, since it's added
            // after the DOM loads.
            var a_element = newForm.find('a.remove');
            a_element.click(function(event) {
                removeForm(newForm);
                return false;
            });

        });
        
        $('.remove').click(function() {         
            var form = $(this).closest("form");
            removeForm(form);
            return false;                
        });

    });

    function removeForm(form) {
        if (confirm("Are you sure you wish to remove this entry?")) {
            form.slideUp('slow', function() {
                form.remove();                    
            });
        }
    }
        
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
                <li class="active"><a href="index.php">Schedule</a></li>
                <li><a href="delay.php">Create Delay</a></li>
                <li><a href="buttons.php">Control</a></li>
                <li><a href="log.php">Event Log</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">

        <?php if (isset($successAlert)) { ?>
			<div class='alert alert-success'>
				<?php echo $successAlert; ?>
			</div>
		<?php } ?>

        <h1>Schedule</h1>

        <a class="btn btn-info btn-default" id="btnAdd" href="#"><i class="icon-plus icon-white"></i>&nbsp;&nbsp;Add new entry</a>&nbsp;
        <a class="btn btn-danger btn-default" id="btnSave" href="#"><i class="icon-file icon-white"></i>&nbsp;&nbsp;Save Schedule</a>

        <div id="entries">

		<?php

		$loopCounter = 0;

		foreach ($schedule as $entry) {

		    $theStartTime = $entry->start;
		    $theMinutes = $entry->minutes;
		    $theStation = $entry->station;
		    $theDays = $entry->days;

		    $parts = explode(":", $theStartTime);
		    $theStartHour = $parts[0];
		    $theStartMinute = $parts[1];

		?>

        <form id="frm<?php echo $loopCounter ?>" name="frm<?php echo $loopCounter ?>" class="form-horizontal entries" method="POST" action="">

        <hr>
            <div class="control-group">
                <label class="control-label" for="inputStartHour">Start Time</label>
                <div class="controls">
					<select class="input-mini" id="inputStartHour" name="inputStartHour">
						<?php 
						foreach (range(0,23) as $hour) { 
							$paddedHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
							$selected = ($theStartHour == $hour) ? "selected" : "";
							echo "<option value='$hour' $selected>$paddedHour</option>\n";
						}
						?>
					</select>
					&nbsp;:&nbsp;
					<select class="input-mini" id="inputStartMinute" name="inputStartMinute">
						<?php 
						foreach (range(0,59) as $minute) { 
							$paddedMinute = str_pad($minute, 2, '0', STR_PAD_LEFT);
							$selected = ($theStartMinute == $minute) ? "selected" : "";
							echo "<option value='$paddedMinute' $selected>$paddedMinute</option>\n";
						}
						?>
					</select>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="inputDays">Days</label>
                <div class="controls">
                    <div class='btn-group' data-toggle='buttons-checkbox'>
						<?php
						$daysArray = [[7,'S'], [1,'M'], [2,'T'], [3,'W'], [4,'T'], [5,'F'], [6,'S']];
						foreach(range(0, 6) as $dayNum) {
							$dayGroup = $daysArray[$dayNum];
							$dayInt = $dayGroup[0];
							$dayLtr = $dayGroup[1];
							$active = (in_array($dayInt, $theDays)) ? "active": "";
							echo "<button type=\"button\" data-value=\"$dayInt\" class=\"btn $active\">$dayLtr</button>\n";
						}					
						?>
                    </div>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="inputMinutes">Minutes</label>
                <div class="controls">
					<select class="input-mini" id="inputMinutes" name="inputMinutes">
					<?php 
						foreach (range(1, $maxMinutesPerStation) as $minute) { 
							$paddedMinute = str_pad($minute, 2, '0', STR_PAD_LEFT);
							$selected = ($theMinutes == $minute) ? "selected" : "";
							echo "<option value='$minute' $selected>$paddedMinute</option>\n";
						}
					?>
                    </select>	
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="inputStation">Station</label>
                <div class="controls">
					<select class="input-mini" id="station" name="station">
					<?php 
						foreach (range(1, $numberOfStations) as $stationNumber) { 
							$selected = ($stationNumber == $theStation) ? "selected" : "";
							echo "<option value='$stationNumber' $selected>$stationNumber</option>\n";
						}
					?>
                    </select>
                </div>
            </div>
        
        <div class="control-group">
            <div class="controls">
                <a class="remove" href="#"><i class="icon-remove-sign"></i>&nbsp;&nbsp;Remove Entry</a>
            </div>
        </div>

    </form>
        
	<?php 
	$loopCounter += 1;
	} 
	?>

    </div> <!-- /entries -->
    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-dropdown.js"></script>
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-button.js"></script>
    <script src="http://twitter.github.io/bootstrap/assets/js/bootstrap-collapse.js"></script>

    <script type="text/template" id="template">

        <form name="FRMTPL" class="form-horizontal entries" method="POST" action="" style="display:none;">

        <hr>
            <div class="control-group">
                <label class="control-label" for="inputStartHour">Start Time</label>
                <div class="controls">
                    <select class="input-mini" id="inputStartHour" name="inputStartHour">
						<?php 
							foreach (range(0,23) as $hour) { 
								$paddedHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
								echo "<option value='$hour'>$paddedHour</option>\n";
							}
						?>
					</select>
					&nbsp;:&nbsp;
					<select class="input-mini" id="inputStartMinute" name="inputStartMinute">
					<?php 
						foreach (range(0,59) as $minute) { 
							$paddedMinute = str_pad($minute, 2, '0', STR_PAD_LEFT);
							echo "<option value='$paddedMinute'>$paddedMinute</option>\n";
						}
					?>
					</select>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="inputDays">Days</label>
                <div class="controls">
                    <div class='btn-group' data-toggle='buttons-checkbox'>
						<?php
						$daysArray = [[7,'S'], [1,'M'], [2,'T'], [3,'W'], [4,'T'], [5,'F'], [6,'S']];
						foreach(range(0, 6) as $dayNum) {
							$dayGroup = $daysArray[$dayNum];
							$dayInt = $dayGroup[0];
							$dayLtr = $dayGroup[1];
							echo "<button type=\"button\" data-value=\"$dayInt\" class=\"btn\">$dayLtr</button>\n";
						}				
						?>
                    </div>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="inputMinutes">Minutes</label>
                <div class="controls">
                    <select class="input-mini" id="inputMinutes" name="inputMinutes">
					<?php 
						foreach (range(1, $maxMinutesPerStation) as $minute) { 
							$paddedMinute = str_pad($minute, 2, '0', STR_PAD_LEFT);
							echo "<option value='$minute'>$paddedMinute</option>\n";
						}
					?>
                    </select>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="inputStation">Station</label>
                <div class="controls">
                    <select class="input-mini" id="station" name="station">
					<?php 
						foreach (range(1, $numberOfStations) as $stationNumber) { 
							$selected = ($stationNumber == $theStation) ? "selected" : "";
							echo "<option value='$stationNumber' $selected>$stationNumber</option>\n";
						}
					?>
                    </select>
                </div>
            </div>
        
        <div class="control-group">
            <div class="controls">
                <a class="remove" href="#"><i class="icon-remove-sign"></i>&nbsp;&nbsp;Remove Entry</a>
            </div>
        </div>

        </form>
    </script>

    <form action="" method="POST" id="frmSchedule" name="frmSchedule">
        <input type="hidden" id="entries" name="entries" />
    </form>

  </body>
</html>