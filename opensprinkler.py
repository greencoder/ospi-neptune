import os
import sys
import time
import atexit
import datetime
import argparse

CUR_DIR = os.path.dirname(os.path.realpath(__file__))

try:
    import RPi.GPIO as GPIO
except ImportError:
    # If you aren't running this on a Pi, you won't have 
    # the GPIO avaialble, so there is a file in utilities that 
    # stubs out the necessary values.
    import utilities.gpio_dev as GPIO

class OpenSprinkler():

    ### Low-Level Hardware Stuff. Don't mess with these. ###

    def _enable_shift_register_output(self):
        """
        Low-level function to enable shift register output. Don't call this
        yourself unless you know why you are doing it.
        """
        GPIO.output(self.PIN_SR_NOE, False)

    def _disable_shift_register_output(self):
        """
        Low-level function to disable shift register output. Don't call this
        yourself unless you know why you are doing it.
        """
        GPIO.output(self.PIN_SR_NOE, True)

    def _set_shift_registers(self, new_values):
        """
        This is the low-level function that is called to set the shift registers.
        I don't pretent do understand the inner workings here, but it works. Don't 
        use this to turn on/off stations, use set_station_status() as the 
        higher-level interface.
        """
        GPIO.output(self.PIN_SR_CLK, False)
        GPIO.output(self.PIN_SR_LAT, False)

        for s in range(0, self.number_of_stations):
            GPIO.output(self.PIN_SR_CLK, False)
            GPIO.output(self.PIN_SR_DAT, new_values[self.number_of_stations-1-s])
            GPIO.output(self.PIN_SR_CLK, True)

        GPIO.output(self.PIN_SR_LAT, True)
        
        # Update the status file
        self._update_status_file()

    def _initialize_hardware(self):
        """
        This contains the low-level stuff required to make the GPIO operations work. Someone 
        smarter than me wrote this stuff, I just smile and nod.
        """
        self.PIN_SR_CLK = 4
        self.PIN_SR_NOE = 17
        self.PIN_SR_LAT = 22
        self.PIN_SR_DAT = 21

        # The 2nd revision of the RPI has a different pin value
        if GPIO.RPI_REVISION == 2:
            self.PIN_SR_DAT = 27

        # Not sure why this is called, but it was in the original script.
        GPIO.cleanup()

        # setup GPIO pins to interface with shift register. Don't muck with this
        # stuff unless you know why you are doing it.
        GPIO.setmode(GPIO.BCM)

        GPIO.setup(self.PIN_SR_CLK, GPIO.OUT)
        GPIO.setup(self.PIN_SR_NOE, GPIO.OUT)        

        self._disable_shift_register_output()        

        GPIO.setup(self.PIN_SR_DAT, GPIO.OUT)
        GPIO.setup(self.PIN_SR_LAT, GPIO.OUT)

        self._set_shift_registers(self.station_values)
        self._enable_shift_register_output()

    def cleanup(self):
        """
        This runs at the termination of the file, turning off all stations, making 
        sure that any PID files are removed, and running GPIO cleanup.
        """
        self.log("Running Cleanup.")
        self.reset_all_stations()
        self._remove_pid_file()
        GPIO.cleanup()

    ### Convenience methods for filesystem operations. You don't need to call these 
    ### manually, they are handled by the higher-level operations.

    def _update_status_file(self):
        """
        Updates the STATUS file with the value for each station.
        """
        file_path = os.path.join(CUR_DIR, 'STATUS')
        f = open(file_path, 'w')
        station_values = "%s" % "".join([str(s) for s in self.station_values])
        f.write(station_values)
        f.close()

    def _create_pid_file(self, minutes_to_run):
        """
        Writes a PID file to the directory to indicate what the PID of the 
        current program is and when it expires.
        """
        expiration = datetime.datetime.now() + datetime.timedelta(minutes=minutes_to_run)
        file_path = os.path.join(CUR_DIR, '%s.pid' % self.pid)
        f = open(file_path, 'w')
        f.write("%s" % expiration.strftime('%Y-%m-%d %H:%M'))
        f.close()

    def _remove_pid_file(self):
        """
        Handles removal of the PID file.
        """
        file_path = os.path.join(CUR_DIR, '%s.pid' % self.pid)
        if os.path.exists(file_path):
            os.remove(file_path)

    def create_delay(self, hours):
        """
        Creates a delay file that will expire after the number of hours
        passed. 
        """
        # Calculate what the datetime object will be by adding the current time
        # and the number of hours to delay. This will be the body of the DELAY file.
        future_time = datetime.datetime.now() + datetime.timedelta(hours=hours)
        expiration = future_time.strftime('%Y-%m-%d %H:%M')
        
        # Write out the DELAY file and make the body the expiration time.
        self.log("Creating DELAY file with expiration %s" % expiration) 
        delay_file_path = os.path.join(CUR_DIR, 'DELAY')
        f = open(delay_file_path, 'w')
        f.write(expiration)
        f.close()

    def check_for_delay(self):
        """
        Look at the filesystem to see if a DELAY file exists. If the 
        file does exist, open it up to see if it's expired. If so, remove
        the file.
        """
        delay_file_path = os.path.join(CUR_DIR, 'DELAY')

        if os.path.exists(delay_file_path):
            # Read the file so we can inspect the contents
            f = open(delay_file_path, 'r')
            data = f.read()
            f.close()
            # The file might have a bad value. Check carefully.
            try:
                # Try to turn the body into a datetime object
                expiration = datetime.datetime.strptime(data, '%Y-%m-%d %H:%M')
                now = datetime.datetime.now()
                # If the expiration time is less than now (i.e. it has passed)
                # then go ahead and remove the file.
                if now >= expiration:
                    self.log("Expiration has passed. Removing DELAY file.")
                    os.remove(delay_file_path)
                else:
                    return expiration
            except ValueError:
                # If we can't cast the value of the file into a date object, there is 
                # no sense keeping the file around. Deleate it.
                self.log("Could not read date in file. Removing file.")
                os.remove(delay_file_path)
                return None
        else:
            return None

    ### Logging functionality ###

    def log(self, message):
        """
        A convenience method for writing operations to a log file. If debugging 
        is enabled, the message is output to the console.
        """
        file_path = os.path.join(CUR_DIR, 'log.txt')
        f = open(file_path, 'a')
        now_time = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        msg = '%s\t%s\t%s\n' % (now_time, self.pid, message)
        f.write(msg)
        if self.debug:
            print msg

    ### Higher-Level Interface. These are the functions you want to call

    def operate_station(self, station_number, minutes, queue, callback_function):
        """
        This is the method that operates a station. Running it causes any 
        currently-running stations to turn off, then a pid file is created that 
        lets the system know that there is a process running. When it completes, 
        ALL stations are turned off and the file is cleaned up.
        """
        self.log("Operating station %d for %d minutes." % (station_number, minutes))

        # Check to see if a delay is in effect
        if self.check_for_delay():
            self.log("Delay in effect. Job will not run.")
            return

        # First, set all stations to zero
        self.station_values = [0] * self.number_of_stations

        # Next, enable just the station to run (adjusting for 0-based index)
        try:
            self.station_values[station_number-1] = 1
        except IndexError:
            self.log("Invalid station number %d passed. Skipping." % station_number)

        # Send the command
        self._set_shift_registers(self.station_values)
        
        # Create a filesystem flag to indicate that the system is running
        self._create_pid_file(minutes)

        # After the number of minutes have passed, turn it off
        time_to_stop = datetime.datetime.now() + datetime.timedelta(minutes=minutes)
        
        # We want to stop it 5 seconds early so it's completely off when the time is up. 
        # This ensures that a program that is set to start the next minute won't have 
        # to kill this one.
        time_to_stop -= datetime.timedelta(seconds=5)
        
        while True:
            # If the queue is not empty, it's because a message was passed from the 
            # parent thread.
            if queue and not queue.empty():
                self.log("Recieved Kill Signal in Thread")
                # Remove the item from the queue
                queue.get(1)
                self._remove_pid_file()
                self.reset_all_stations()
                break
            if datetime.datetime.now() < time_to_stop:
                pass
            else:
                self.log("Finished operating station.")
                # We don't know if a new job started while we were snoozing.
                # If one did, we don't want to close all valves anymore.
                # We need a way to check and see if this process is the most
                # recent one.
                self._remove_pid_file()
                self.reset_all_stations()
                break

        # If a callback function was passed, we call it now.
        if callback_function:
            callback_function(station_number)

    def reset_all_stations(self):
        """
        A convenience method for turning everything off. 
        """
        self.log("Turning Off All Stations.")
        off_values = [0] * self.number_of_stations
        self.station_values = off_values
        self._set_shift_registers(off_values)

    def __init__(self, debug=False, number_of_stations=8):
        
        self.number_of_stations = number_of_stations
        
        # If debug is true, we print log messages to console
        self.debug = debug
        
        # We need to save the PID of the current process.
        self.pid = os.getpid()
        
        # Initial values are zero (off) for all stations.
        self.station_values = [0] * number_of_stations

        # Get the hardware ready for operations
        self._initialize_hardware()

if __name__ == "__main__":
    pass
