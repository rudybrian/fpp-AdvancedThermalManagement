#!/usr/bin/python

import logging
import json
import os
import errno
import subprocess
import socket
from sys import argv
from time import sleep
from datetime import datetime

if len(argv) <= 1:
    print('Usage:')
    print('   --init     | Used to start up the AdvancedThermalManagement_Updater.py script')
    print('   --exit     | Function used to shutdown the AdvancedThermalManagement_Updater.py script')
    print('   --command jsondata \'{...}\'    | Send the JSON encoded command')
    print('Note: Running with sudo might be needed for manual execution')
    exit()

script_dir = os.path.dirname(os.path.abspath(argv[0]))

#logging.basicConfig(filename=script_dir + '/AdvancedThermalManagement_CLI.log', level=logging.INFO, format='%(asctime)s:%(name)s:%(levelname)s:%(message)s')
logging.basicConfig(filename=script_dir + '/AdvancedThermalManagement_CLI.log', level=logging.DEBUG, format='%(asctime)s:%(name)s:%(levelname)s:%(message)s')
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


with open(fifo_path, 'w') as fifow:
    logging.info('Processing %s', argv[1])
    if argv[1] == '--exit':
        # Not used by FPPD, but useful for testing
        fifow.write('EXIT\n')
        exit()
    elif argv[1] == '--command' and argv[2] == 'jsondata' and len(argv) == 4:
       logging.info('Command JSON')
       logging.debug('JSON command=%s', argv[3])
       try:
           json_command = json.loads(argv[3])
       except ValueError:  # includes simplejson.decoder.JSONDecodeError
           print('Decoding JSON has failed')
       else:
           # Do some stuff and return a response
           fifow.write('{"Request": ' + argv[3] + '}')
    else:
        print('Invalid command')
        exit()
    logging.debug('Processing done')


with open(fifo_path, 'r') as fifor:
    for i in range(2):
        line = fifor.readline().rstrip()
        if len(line) > 0:
            logging.debug('Received response line %s', line)
            print(line)
            exit()
        else:
            # Sleep until the top of the next second
            sleep ((1000000 - datetime.now().microsecond) / 1000000.0)


