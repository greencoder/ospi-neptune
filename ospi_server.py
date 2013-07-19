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
        
        # Make sure they passed "station,minutes"
        match = re.match("(?P<station>\d+),(?P<minutes>\d+)", data)

        if match:
            station = int(match.group('station'))
            minutes = int(match.group('minutes'))
        else:
            self.request.sendall("BAD REQUEST")

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
            print "Delay is in effect. Job will not run."

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

    # Parse command-line arguments
    parser = argparse.ArgumentParser()
    parser.add_argument('--host', required=False, default='127.0.0.1')
    parser.add_argument('--port', type=int, required=False, default=9999)
        
    args = vars(parser.parse_args())
    host = args['host']
    port = args['port']

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
    server = SimpleServer((host, port), TCPHandler)
    print "Starting server on %s:%d" % (host, port)

    # Terminate gracefully with Ctrl-C
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print "Shutting down server."
        sprinkler.cleanup()
        sys.exit(0)
