import pandas as pd
import time
import bcrypt
import mysql.connector
from mysql.connector import Error
from mysql.connector import errorcode
from dateutil.parser import parse
connection = mysql.connector.connect(host='10.229.30.185',
                                     database='wifi_tracking',
                                     user='brandon',
                                     password='password')

#Dictionary to store MovingAverage objects
dictOfObjs = {}

#Object class to store moving average values
class MovingAverage:
    def __init__(self):
        self.window_size = 5
        self.values = []
        self.sum = 0
    
    def process(self, value):
        self.values.append(value)
        self.sum += value
        if len(self.values) > self.window_size:
            self.sum -= self.values.pop(0)
        return float(self.sum) / len(self.values)
try:
    while True:
        pi_id = input ("Enter pi identification number (1, 2, or 3): ")
        # check if pi_id is equal to one of the strings, specified in the list
        if pi_id in ['1', '2', '3']:
        # if it was equal - break from the while loop
            break
    currentDate = parse('2000-01-01 00:00:00')
    rowDate = 0
    while True:
        #remove first 3 rows of cvs file, unneccesary 
        df=pd.read_csv("../data/capture-01.csv", skiprows=3)
        #deletes white space and colons from MAC address
        df.columns = df.columns.str.lstrip()
        df['Last time seen'] = df['Last time seen'].str.lstrip()
        df['Station MAC'] = df['Station MAC'].str.replace(':', '')
        
        keep_col = ['Station MAC', 'Last time seen', 'Power']
        new_df = df[keep_col]
        
        #for loop to grab the most recent timestamp values
        for index, row in new_df.iterrows():
            rowDate = parse(row['Last time seen'])
            if (currentDate < rowDate):
                currentDate = rowDate

        for index, row in new_df.iterrows():
            if (currentDate == parse(row['Last time seen'])):       
                foundMacAddress = False
                currentMac = row['Station MAC']
                #print('Searching for MAC address {} in database...'.format(currentMac))
                cursor = connection.cursor(buffered=True)
                cursor.execute("""SELECT mac_address, id FROM registered_macs WHERE enabled = 1""")
                cursorRow = cursor.fetchone()
                currentUniqueId = -1
                while cursorRow:
                    currentStoredMac = cursorRow[0]
                    
                    if bcrypt.checkpw(currentMac.encode('utf8'), currentStoredMac.encode('utf8')):
                        #print('Found! Storing RSSI value now...'.format(currentMac))
                        # Grabbing the device ID if found in the stored devices DB
                        currentUniqueId = cursorRow[1]
                        foundMacAddress = True
                    cursorRow = cursor.fetchone()
                if (foundMacAddress):
                    if(pi_id=='1'):
                        if(dictOfObjs.get(currentUniqueId) == None):
                            #Create object and store power value in dictionary
                            #also push current average into DB
                            currentObject = MovingAverage()
                            currentAvg = currentObject.process(row['Power'])
                            dictOfObjs[currentUniqueId] = currentObject
                            cursor.execute("""SELECT * FROM rssi where device_id = %s AND last_seen = %s""", (currentUniqueId, row['Last time seen']))
                            data = cursor.fetchone()
                            if data is None:
                                print('Inserting device: ' + str(currentUniqueId) + ' at timestamp: ' + str(row['Last time seen']) + ' with db value: '+ str(currentAvg))
                                cursor.execute("""INSERT INTO rssi (device_id, last_seen, pi_1_power) VALUES (%s, %s, %s)""", (currentUniqueId, row['Last time seen'], currentAvg))
                        else:
                            #Retrieve object from dict, store power value and push
                            #current average into DB
                            currentAvg = dictOfObjs.get(currentUniqueId).process(row['Power'])
                            cursor.execute("""SELECT * FROM rssi where device_id = %s AND last_seen = %s""", (currentUniqueId, row['Last time seen']))
                            data = cursor.fetchone()
                            if data is None:
                                print('Inserting device: ' + str(currentUniqueId) + ' at timestamp: ' + str(row['Last time seen']) + ' with db value: '+ str(currentAvg))
                                cursor.execute("""INSERT INTO rssi (device_id, last_seen, pi_1_power) VALUES (%s, %s, %s)""", (currentUniqueId, row['Last time seen'], currentAvg))
                    elif(pi_id=='2'):
                        if(dictOfObjs.get(currentUniqueId) == None):
                            #Create object and store power value in dictionary
                            #also push current average into DB
                            currentObject = MovingAverage()
                            currentAvg = currentObject.process(row['Power'])
                            dictOfObjs[currentUniqueId] = currentObject
                            cursor.execute("""SELECT * FROM rssi where device_id = %s AND last_seen = %s""", (currentUniqueId, row['Last time seen']))
                            data = cursor.fetchone()
                            if data is None:
                                print('Inserting device: ' + str(currentUniqueId) + ' at timestamp: ' + str(row['Last time seen']) + ' with db value: '+ str(currentAvg))
                                cursor.execute("""INSERT INTO rssi (device_id, last_seen, pi_2_power) VALUES (%s, %s, %s)""", (currentUniqueId, row['Last time seen'], currentAvg))
                        else:
                            #Retrieve object from dict, store power value and push
                            #current average into DB
                            currentAvg = dictOfObjs.get(currentUniqueId).process(row['Power'])
                            cursor.execute("""SELECT * FROM rssi where device_id = %s AND last_seen = %s""", (currentUniqueId, row['Last time seen']))
                            data = cursor.fetchone()
                            if data is None:
                                print('Inserting device: ' + str(currentUniqueId) + ' at timestamp: ' + str(row['Last time seen']) + ' with db value: '+ str(currentAvg))
                                cursor.execute("""INSERT INTO rssi (device_id, last_seen, pi_2_power) VALUES (%s, %s, %s)""", (currentUniqueId, row['Last time seen'], currentAvg))
                    else:
                        if(dictOfObjs.get(currentUniqueId) == None):
                            #Create object and store power value in dictionary
                            #also push current average into DB
                            currentObject = MovingAverage()
                            currentAvg = currentObject.process(row['Power'])
                            dictOfObjs[currentUniqueId] = currentObject
                            cursor.execute("""SELECT * FROM rssi where device_id = %s AND last_seen = %s""", (currentUniqueId, row['Last time seen']))
                            data = cursor.fetchone()
                            if data is None:
                                print('Inserting device: ' + str(currentUniqueId) + ' at timestamp: ' + str(row['Last time seen']) + ' with db value: '+ str(currentAvg))
                                cursor.execute("""INSERT INTO rssi (device_id, last_seen, pi_3_power) VALUES (%s, %s, %s)""", (currentUniqueId, row['Last time seen'], currentAvg))
                        else:
                            #Retrieve object from dict, store power value and push
                            #current average into DB
                            currentAvg = dictOfObjs.get(currentUniqueId).process(row['Power'])
                            cursor.execute("""SELECT * FROM rssi where device_id = %s AND last_seen = %s""", (currentUniqueId, row['Last time seen']))
                            data = cursor.fetchone()
                            if data is None:
                                print('Inserting device: ' + str(currentUniqueId) + ' at timestamp: ' + str(row['Last time seen']) + ' with db value: '+ str(currentAvg))
                                cursor.execute("""INSERT INTO rssi (device_id, last_seen, pi_3_power) VALUES (%s, %s, %s)""", (currentUniqueId, row['Last time seen'], currentAvg))
                    connection.commit()
finally:
    if(connection.is_connected()):
        cursor.close()
        connection.close()
        print("Connection is closed")