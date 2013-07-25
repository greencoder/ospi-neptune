import SocketServer
import Queue
import threading
import os
import sys
import re
import time
import datetime
import argparse
import atexit
import json

from opensprinkler import OpenSprinkler
import config

CUR_DIR = os.path.dirname(os.path.realpath(__file__))

class TCPHandler(SocketServer.BaseRequestHandler):

    def log(self, message):
        """
        A convenience method for writing operations to a log file. If debugging 
        is enabled, the message is output to the console.
        """
        file_path = os.path.join(CUR_DIR, 'log.txt')
        f = open(file_path, 'a')
        now_time = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        msg = '%s\t%s\t%s\n' % (now_time, os.getpid(), message)
        f.write(msg)
        if config.DEBUG:
            print msg

    def thread_done(self, value):
        """
        This method gets called when the thread finishes. We use 
        it to clean up our list of running threads.
        """
        thread_name = threading.current_thread().name
        thread_list.remove(thread_name)

    def handle(self):
        """
        This method is called when data is received on the network socket.
        """
        # Receive the data on the socket
        data = self.request.recv(1024).strip()
        
        # Decode the JSON that arrived on the socket
        try:
            json_data = json.loads(data)
        except Exception, e:
            self.log("Could not decode JSON. Skipping.")
            return

        if json_data.has_key('cmd') and json_data.has_key('args'):
            command = json_data['cmd']
            arguments = json_data['args']
        else:
            self.log("JSON data missing cmd or args. Skipping")
            return
        
        # If the command is to set the delay, check the args for 'hours'
        if command == "create-delay":
            self._handle_command_create_delay(arguments)

        # If the command is to get the station status, we don't need args.
        elif command == "status":
            self._handle_command_status(arguments)

        # If the command is to operate stations, we need "minutes" and "stations" arguments
        elif command == "operate-station":
            self._handle_command_operate_station(arguments)

    ### Command Handlers ###

    def _handle_command_operate_station(self, arguments):
        """
        Handles the command to operate a station
        """
        if not (arguments.has_key('minutes') and arguments.has_key('station')):
            self.log("operate-station command received but station or minutes argument missing. Skipping.")
            return

        try:
            station = int(arguments['station'])
            minutes = int(arguments['minutes'])
        except ValueError:
            self.log("opearate station command received but station or minutes arguments were invalid. Skipping.")

        # Check the inputs
        if station not in range(0, config.NUMBER_OF_STATIONS+1):
            self.log("Received Bad Station Number: %d" % station)
            self.request.sendall("BAD STATION NUMBER")
            return
        elif minutes not in range(0, config.MAX_MINUTES_PER_STATION+1):
            self.log("Received Invalid Number of Minutes: %d" % minutes)
            self.request.sendall("BAD STATION NUMBER")
            return
        else:
            self.request.sendall("OK")

        # See if there is already a running thread. If so, we issue 
        # a 'DIE' command to all threads (via queue) to make sure they 
        # stop before we start the new thread. We pause for 2 seconds 
        # to make sure that any threads have time to clean themselves up.
        if len(thread_list) > 0:
            self.log("Killing running jobs.")
            queue.put("DIE")
            time.sleep(2)

        # Make sure there isn't a delay in effect. If there is not, go 
        # ahead and spawn a new thread to run the station. Keep the name 
        # of the thread in the list.
        if not sprinkler.check_for_delay():
            callback_function = self.thread_done
            thread = threading.Thread(target=sprinkler.operate_station, 
                args=(station, minutes, queue, callback_function))
            thread_list.append(thread.name)
            self.log("Sending command to operate station %d for %d minutes" % (station, minutes))
            thread.start()
        else:
            self.log("Delay is in effect. Job will not run.")

    def _handle_command_status(self, arguments):
        """
        Handles a command to get the current status of the stations
        """
        self.log("status command received but functionality not built yet. Skipping.")
        return
    
    def _handle_command_create_delay(self, arguments):
        """
        Comamnd handler for creating delay files
        """
        if not arguments.has_key('hours'):
            self.log("delay command received but hours argument missing. Skipping.")
            return

        try:
            hours = int(arguments['hours'])
            sprinkler.create_delay(hours)
        except ValueError:
            self.log("Delay command received but hours argument was not an integer.")
            return

class SimpleServer(SocketServer.ThreadingMixIn, SocketServer.TCPServer):
    """
    We have to create the server in this manner so that it can be 
    multithreaded. If it wasn't, only one client can connect at a time. We 
    probably don't need multile client access, but it's better to be safe in 
    case an improperly-written client holds a socket open by accident.
    """
    
    # Ctrl-C will cleanly kill all spawned threads
    daemon_threads = True

    # much faster rebinding
    allow_reuse_address = True

    def __init__(self, server_address, RequestHandlerClass):
        SocketServer.TCPServer.__init__(self, server_address, RequestHandlerClass)

if __name__ == "__main__":

    # Keep a list of all running threads so we can make sure that two stations 
    # don't run at the same time.
    thread_list = []

    # Queue is the mechanism used to communicate between threads
    queue = Queue.Queue()
    
    # Instantiate the sprinkler object
    sprinkler = OpenSprinkler(debug=config.DEBUG, number_of_stations=config.NUMBER_OF_STATIONS)

    # Make sure we run cleanup when server shuts down. (to be safe)
    atexit.register(sprinkler.cleanup)

    # Create the server
    server = SimpleServer((config.HOST, config.PORT), TCPHandler)
    print "Starting server on %s:%d" % (config.HOST, config.PORT)

    # Terminate gracefully with Ctrl-C
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print "Shutting down server."
        sprinkler.cleanup()
        sys.exit(0)
