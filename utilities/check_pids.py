import os
import sys
import datetime
import socket

CUR_DIR = os.path.dirname(os.path.realpath(__file__))
PARENT_DIR = os.path.abspath(os.path.join(CUR_DIR, os.pardir))

# Look in the main directory for any files that end in .pid. The name of 
# the file represents the pid of any running Sprinkler programs
pid_files = [f for f in os.listdir(PARENT_DIR) if f.endswith('.pid')]

needs_cleanup = False
old_pid_files = []

# Look through any pid files we found and see if they are older than the 
# expiration time they contain. If any files older than the max age 
# allowed are found, we'll flag them and run some cleanup.
for file_name in pid_files:

    file_path = os.path.join(PARENT_DIR, file_name)

    # Read the contents of the file to find out when it expires
    f = open(file_path, 'r')
    data = f.read()
    f.close()
    
    try:
        expiration = datetime.datetime.strptime(data, '%Y-%m-%d %H:%M')
    except ValueError:
        # If the file contains badly formatted data, the best thing to do is 
        # assume the worst and close all stations, then remove the file.
        expiration = datetime.datetime.now() - datetime.timedelta(minutes=10)

    if datetime.datetime.now() >= expiration:
        needs_cleanup = True
        old_pid_files.append(file_path)

# If we found any old files, we need to delete the file and turn the system off
# in case it's errantly running.
if not needs_cleanup:
    sys.exit("No old PID files found. Aborting.")

# If we got here, we should ensure that all stations are turned off.
sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
sock.connect(('localhost', 9999))
sock.sendall("0,0") # Station 0 means 'all off'
sock.close()

# Any old files we found should be removed now that we've finished our 
# housekeeping.
for old_file in old_pid_files:
    print "Deleting File: %s" % old_file
    os.remove(old_file)
