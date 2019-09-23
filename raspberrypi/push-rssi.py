import pandas as pd
import time
import bcrypt
import mysql.connector
import time
from mysql.connector import Error
from mysql.connector import errorcode
from dateutil.parser import parse
connection = mysql.connector.connect(host='192.168.137.1',
                                     database='wifi_tracking',
                                     user='brandon',
                                     password='password')
try:
    pi_id = input("Enter pi id number: ")
    while True:
        #remove first 3 rows of cvs file, unneccesary 
        df=pd.read_csv("../data/capture-01.csv", skiprows=3)
        #deletes white space and colons from MAC address
        df.columns = df.columns.str.lstrip()
        df['Last time seen'] = df['Last time seen'].str.lstrip()
        df['Station MAC'] = df['Station MAC'].str.replace(':', '')
        
        keep_col = ['Station MAC', 'Last time seen', 'Power']
        new_df = df[keep_col]
        
        currentDate = parse('2000-01-01 00:00:00')
        rowDate = 0
        #for loop to grab the most recent timestamp values
        for index, row in new_df.iterrows():
            rowDate = parse(row['Last time seen'])
            if (currentDate < rowDate):
                currentDate = rowDate

        for index, row in new_df.iterrows():
            if (currentDate == parse(row['Last time seen'])):       
                foundMacAddress = False
                currentMac = row['Station MAC']
                print('Searching for MAC address {} in database...'.format(currentMac))
                cursor = connection.cursor()
                cursor.execute("""SELECT mac_address FROM registered_macs WHERE enabled = 1""")
                cursorRow = cursor.fetchone()
                while cursorRow:
                    currentStoredMac = cursorRow[0]
                    if bcrypt.checkpw(currentMac.encode('utf8'), currentStoredMac.encode('utf8')):
                        print('Found! Storing RSSI value now...'.format(currentMac))
                        foundMacAddress = True
                    cursorRow = cursor.fetchone()
                if (foundMacAddress):
                    cursor.execute("""INSERT INTO rssi (pi_id, mac_address, last_seen, power) VALUES (%s, %s, %s, %s)""", (pi_id, bcrypt.hashpw(currentMac.encode('utf8'), bcrypt.gensalt()), row['Last time seen'], row['Power']))
                    connection.commit()
                else:
                    connection.commit()
finally:
    if(connection.is_connected()):
        cursor.close()
        connection.close()
        print("Connection is closed")
