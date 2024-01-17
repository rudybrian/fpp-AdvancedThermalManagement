#!/usr/bin/python

import logging
import json
import os
import errno
import atexit
import socket
import subprocess
from sys import argv
from time import sleep
from datetime import datetime

device_addresses = {
                   "0x4c":"EMC2101", 
                   "0x50":"MAX31760",
                   "0x51":"MAX31760",
                   "0x52":"MAX31760",
                   "0x53":"MAX31760",
                   "0x54":"MAX31760",
                   "0x55":"MAX31760",
                   "0x56":"MAX31760",
                   "0x57":"MAX31760"
                   }

def parseCommand(command):
    "Parse command"
    if ("detectI2CDevices" in command): 
        return(detectI2CDevicesCommand(command["detectI2CDevices"]));
    else:
        error_response = {"Error":"Invalid Command"}
        return(error_response)

def detectI2CDevicesCommand(bus):
    "Detect and enumerate known fan control devices on I2C bus"
    detected_devices = {}
    logging.debug('detectI2CDevices on bus=%s', bus)
    for address in device_addresses:
        try:
            s = subprocess.check_output(['/usr/sbin/i2cget', '-y', bus, address], stderr=subprocess.STDOUT, encoding='UTF-8')
            detected_devices[address] = device_addresses[address]
        except:
            y=1 # we need to put something here to catch eceptions or python complains about formatting
    logging.debug("returning:%s", json.dumps(detected_devices))
    return(detected_devices)
   
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
with open(fifo_path, 'r') as fifo:
    while True:
        line = fifo.readline().rstrip()
        if len(line) > 0:
            logging.debug('line %s', line)
            if line == 'EXIT':
                logging.info('Processing exit')
                exit()
            else:
                try:
                    json_command = json.loads(line)
                except ValueError:  # includes simplejson.decoder.JSONDecodeError
                    logging.info('JSON decoding error')
                else:
                    with open(fifo_path, 'w') as fifow:
                        logging.info('Sending response')
                        # need to check if the request key exists, or this would fail
                        if "Request" in json_command:
                            logging.debug('Processing Request: %s', json_command["Request"])
                            response = json.dumps(parseCommand(json_command["Request"]))
                            fifow.write('{"Response": {"OriginalRequest":' + json.dumps(json_command["Request"]) + '}, "CommandResponse":' + response + '}')
                        else:
                            logging.debug('Invalid command')
                            fifow.write('Baguette!')
        else:
            # Sleep until the top of the next second
            sleep ((1000000 - datetime.now().microsecond) / 1000000.0)
