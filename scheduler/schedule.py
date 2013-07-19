import json
import os
import sys
import datetime
import socket
import argparse

CUR_DIR = os.path.dirname(os.path.realpath(__file__))

class Event():
    
    def __init__(self, event_dict):
        try:
            self.start_time = event_dict['start']
            self.days = event_dict['days']
            self.minutes = event_dict['minutes']
            self.station = event_dict['station']
        except KeyError, e:
            sys.exit('Error reading schedule. Missing key: %s' % e)

    def __repr__(self):
        day_list = ['M','T','W','Th','F','S','Sn']
        days = ",".join([day_list[day-1] for day in self.days])
        return "Station %d, run %d minutes on %s @ %s" % \
            (self.station, self.minutes, days, self.start_time)

    def should_run_now(self):
        """
        Compares the current day number and time to the event to see 
        if it's supposed to be running now.
        """
        now = datetime.datetime.now()
        day = now.isoweekday()
        # See if today is in the list of days to run
        if day in self.days:
            now_hour_min = now.strftime("%H:%M")
            if now_hour_min == self.start_time:
                return True
        # If none of the tests passed, we do not run
        return False

    @classmethod
    def log(self, message):
        """
        A convenience method for writing operations to a log file.
        """
        file_path = os.path.join(CUR_DIR, 'log.txt')
        f = open(file_path, 'a')
        now_time = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        msg = '%s\t%s\t%s\n' % (now_time, os.getpid(), message)
        f.write(msg)
        print msg

if __name__ == "__main__":

    # Parse command-line arguments
    parser = argparse.ArgumentParser()
    parser.add_argument('--file', required=True)
    parser.add_argument("--dump", action="store_true", dest="dump", default=False)
    args = vars(parser.parse_args())

    # Try to open up the filename that was passed
    try:
        file_path = os.path.abspath(args['file'])
        f = open(file_path, 'r')
        data = f.read()
        f.close()
    except IOError:
        sys.exit("Error opening schedule file: %s" % file_path)

    # Turn the contents of the json file into a Python list
    try:
        events = json.loads(data)
    except ValueError:
        sys.exit("Error in schedule.json syntax. Invalid JSON.")

    if type(events) is not list:
        sys.exit("Error in schedule.json syntax. Could not find events list.")

    # If we get a dump flag, we just want to output the schedule 
    if args['dump']:
        for item in events:
            print Event(item)
        sys.exit()

    # Loop over all the events and find the first one that should
    # be run now.
    for item in events:
        # Turn the event dictionary into an object
        event = Event(item)
        # See if the event is supposed to run this minute
        if event.should_run_now():
            Event.log("Running event: %s" % event)
            try:
                sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
                sock.connect(('localhost', 9999))
                sock.sendall("%s,%s" % (event.station, event.minutes))
                sock.close()
            except socket.error:
                Event.log("Error: Could not connect to socket")
            break
