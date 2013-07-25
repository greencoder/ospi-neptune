import os
import sys
import argparse
import datetime
import json
import socket

CUR_DIR = os.path.dirname(os.path.realpath(__file__))
PARENT_DIR = os.path.abspath(os.path.join(CUR_DIR, os.pardir))

# Add the parent dir to the search path so we can import config
sys.path.insert(0, PARENT_DIR)
import config

parser = argparse.ArgumentParser()
parser.add_argument('--hours', help='# of hours to pause job execution', type=int, required=True)

args = vars(parser.parse_args())

try:
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.connect((config.HOST, config.PORT))
    cmd = { 'cmd': 'create-delay', 'args': { 'hours': args['hours'] }}
    sock.sendall("%s" % json.dumps(cmd))
    sock.close()
except socket.error:
    sys.exit("Could not connect to network socket.")