#!/usr/bin/python

import logging
import json
import os
import errno
import subprocess
import socket
from sys import argv
from time import sleep

if len(argv) <= 1:
    print 'Usage:'
    print '   --list     | Used by fppd at startup. Used to start up the Si4713_RDS_Updater.py script'
    print '   --reset    | Function by plugin_setup.php to reset the GPIO pin connected to the Si4713'
    print '   --exit     | Function used to shutdown the Si4713_RDS_Updater.py script'
    print '   --type media --data \'{...}\'    | Used by fppd when a new items starts in a playlist'
    print '   --type playlist --data \'{...}\' | Used by fppd when a playlist starts or stops'
    print 'Note: Running with sudo might be needed for manual execution'
    exit()

script_dir = os.path.dirname(os.path.abspath(argv[0]))

logging.basicConfig(filename=script_dir + '/AdvancedThermalManagement_CLI.log', level=logging.INFO, format='%(asctime)s:%(name)s:%(levelname)s:%(message)s')
logging.info('----------')
logging.debug('Arguments %s', argv[1:])

# Always start the Updater since it does the real work for all command
updater_path = script_dir + '/AdvancedThermalManagement_Updater.py'
try:
    logging.debug('Checking for socket lock by %s', updater_path)
    lock_socket = socket.socket(socket.AF_UNIX, socket.SOCK_DGRAM)
    lock_socket.bind('\0ATM_Updater')
    lock_socket.close()
    logging.debug('Lock not found')
    logging.info('Starting %s', updater_path)
    devnull = open(os.devnull, 'w')
    subprocess.Popen(['python', updater_path], stdin=devnull, stdout=devnull, stderr=devnull, close_fds=True)
except socket.error:
    logging.debug('Lock found - %s is running', updater_path)

# Always setup FIFO
fifo_path = script_dir + '/ATM_FIFO'
try:
    logging.debug('Setting up write side of fifo %s', fifo_path)
    os.mkfifo(fifo_path)
except OSError as oe:
    if oe.errno != errno.EEXIST:
        raise
    else:
        logging.debug('Fifo already exists')


with open(fifo_path, 'w') as fifo:
    logging.info('Processing %s', argv[1])
    if argv[1] == '--exit':
        # Not used by FPPD, but useful for testing
        fifo.write('EXIT\n')
    logging.debug('Processing done')

