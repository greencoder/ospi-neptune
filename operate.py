import socket
import sys
import argparse

import config

# Parse command-line arguments
parser = argparse.ArgumentParser()
parser.add_argument('--station', type=int, help='Station to run [1-8]', required=True)
parser.add_argument('--minutes', type=int, help='Number of minutes to run station.', required=True)
args = vars(parser.parse_args())

station_number = args['station']
number_minutes = args['minutes']

if station_number not in range(0, config.NUMBER_OF_STATIONS):
    sys.exit("Error: Invalid Station Number. Must be 0 to %d." % config.NUMBER_OF_STATIONS)

if number_minutes not in range(0, config.MAX_MINUTES_PER_STATION):
    sys.exit("Error: The number of minutes exceeds what is allowed in the configuration.")

sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
sock.connect(('localhost', 9999))
sock.sendall("%s,%s" % (station_number, number_minutes))
sock.close()
