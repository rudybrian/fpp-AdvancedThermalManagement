#!/usr/bin/python

import logging
import json
import os
import errno
import atexit
import socket
from sys import argv
from time import sleep
from datetime import datetime


# Setup logging
script_dir = os.path.dirname(os.path.abspath(argv[0]))

logging.basicConfig(filename=script_dir + '/AdvancedThermalManagement_updater.log', level=logging.DEBUG, format='%(asctime)s:%(name)s:%(levelname)s:%(message)s')
logging.info("----------")


# Establish lock via socket or exit if failed
try:
    lock_socket = socket.socket(socket.AF_UNIX, socket.SOCK_DGRAM)
    lock_socket.bind('\0ATM_Updater')
    logging.debug('Lock created')
except:
    logging.error('Unable to create lock. Another instance of AdvancedThermalManagement_Updater.py running?')
    exit(1)

# Setup fifo
fifo_path = script_dir + "/ATM_FIFO"
try:
    logging.debug('Setting up read side of fifo %s', fifo_path)
    os.mkfifo(fifo_path)
except OSError as oe:
    if oe.errno != errno.EEXIST:
        raise
    else:
        logging.debug('Fifo already exists')


# Main loop
with open(fifo_path, 'r', 0) as fifo:
    while True:
        line = fifo.readline().rstrip()
        if len(line) > 0:
            logging.debug('line %s', line)
            if line == 'EXIT':
                logging.info('Processing exit')
                exit()
        else:
            # Sleep until the top of the next second
            sleep ((1000000 - datetime.now().microsecond) / 1000000.0)
