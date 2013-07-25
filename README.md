ospi-neptune
============

A socket server to control the OpenSprinkler Pi

**Disclaimer: This software is very alpha. Don't blame me if your yard is turned into a lake. There are a few built-in safeguards, but please use at your own risk and test thoroughly before you go on a month-long vacation. This software is running at my house, but your mileage may vary.**

###Usage###

The server can be run in the foreground if you want to test and debug:

    $ sudo python ospi_server.py

Use `Ctrl-C` to shut down the server gracefully.

It is possible to run the server in the background from the command line and keep it running when you log out:

    $ nohup sudo python aspi_server.py &

While this does work, the server will not start automatically when the Raspberry Pi boots. Follow the instructions in the wiki to set up *supervisor* to run the server automatically.

###Clients###

One of the nice features of the server is that clients don't have to run with superuser privileges. (only the server does)

There is a python client included to operate stations from the command-line:

    $ python operate.py --station [STATION_NUMBER] --minutes [MINUTES_TO_RUN]

Look in the `example_clients` directory for examples of how to connect to the server in other languages.

###Configuration###

The `config.py` file contains a few configuration directives that can be tweaked:
* `NUMBER_OF_STATIONS` - Sets the number of stations your system supports (default is 8)
* `MAX_MINUTES_PER_STATION` - This is the longest any station is allowed to run. This is a safety mechanism to prevent clients from accidentally sending absurdly long run times.
* `DEBUG` - When set to True (case-sensitive) debugging and log messages are output to the console.

###Adding Delay to Prevent Station Operation###

You can prevent jobs from running by placing a file named `DELAY` in the project directory. If present, the job will abort.

There is a file in utilities that looks at the body of this file to determine when it should be cleared. The format is YYYY-MM-DD HH:MM.

    $ python utilities/check_delay.py

If the file is found and the time in the file is older than the current time, the `DELAY` file is removed so that normal operations can resume.

As a convenience, there is a utility program to automatically create the file in the proper format:

    $ python utilities/set_delay.py --hours [NUMBER_OF_HOURS_TO_DELAY]

*Note: This would be a good mechanism to use if you have any programs that check the weather; you could write logic to figure out how long to set a delay based on the forecast.*

###Cron Scheduling###

You can schedule the operation of stations via cron:

    # Run every Monday, Wednesday, and Friday at 5:00am
    00 05 * * 1,3,5 /usr/bin/python /home/pi/ospi-neptune/operate.py --station 1 --minutes 15

Remember, this is cron, so make sure to use full paths to your files.

### File-Based Scheduling###

Instead of running cron to schedule your events, you can use the built-in scheduler. (this is the preferred method). See the  documentation [here](docs/scheduler.md).

###Feedback###

I welcome any feedback or advice you are willing to offer. You can use the issues tab in Github or reach out to me at snewman18 [at gmail dot com].

###License###

This project is distributed under the MIT license. Do whatever you want with it, but please provide attribution back and don't hold me liable.


