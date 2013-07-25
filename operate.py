import socket
import sys
import argparse
import json

import config

# Parse command-line arguments
parser = argparse.ArgumentParser()
parser.add_argument('--station', type=int, help='Station to run [1-8]', required=True)
parser.add_argument('--minutes', type=int, help='Number of minutes to run station.', required=True)
args = vars(parser.parse_args())

station = args['station']
minutes = args['minutes']

if station not in range(0, config.NUMBER_OF_STATIONS):
    sys.exit("Error: Invalid Station Number. Must be 0 to %d." % config.NUMBER_OF_STATIONS)

if minutes not in range(0, config.MAX_MINUTES_PER_STATION):
    sys.exit("Error: The number of minutes exceeds what is allowed in the configuration.")

sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
sock.connect((config.HOST, config.PORT))
cmd = { 'cmd': 'operate-station', 'args': { 'station': station, 'minutes': minutes } }
sock.sendall("%s" % json.dumps(cmd))
sock.close()
