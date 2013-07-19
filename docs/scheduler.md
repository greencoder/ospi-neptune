ospi-neptune Scheduler
============

A scheduling system for creating repeating station events with a simple JSON format.

###Usage###

The scheduler is meant to be run every minute via cron, but it can be run manually:

    $ python schedule.py --file <schedule_filename>.json

If an event matching the current day number, hour, and minute is found, the operation command will be sent to the network server. 

*Note: If multiple events match the current time, only the first event read will fire.*

###Schedule Format###

The schedule file is written in JSON and must be an array of dictionaries. Four dictionary keys are required:
* `station` - the number of the station that should be run
* `days` - the day(s) of the week that the event should run on. This must be an array and can contain multiple days.
* `minutes` - the number of minutes the job should run for
* `start` - the time at which the job should run. Must be written in 24-hour format. The time will be checked against whatever timezone your system uses. (i.e. if you are PST, a start time of '15:00' will run at 3pm Pacific Time)

Here's an example:

	[
		{
			"station": 1,
			"days": [1,2,3,4,5,6,7],
			"minutes": 1,
			"start-time": "09:00"
		},
		{
			"station": 2,
			"days": [1,3,5],
			"minutes": 10,
			"start-time": "10:00"
		}
	]

*Note: To ensure your file is valid JSON, try pasting the contents into [jsonlint.com](http://www.jsonlint.com).*

###Running the Scheduler###

Currently, the scheduler is meant to be run every minute by cron. This may change to a daemon-based approach in the future.

    # Run every minute
    * * * * * /usr/bin/python /home/pi/ospi-neptune/scheduler/schedule.py --file /home/pi/ospi-neptune/scheduler/schedule.json
