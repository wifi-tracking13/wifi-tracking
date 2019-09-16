import pandas as pd
import time
import bcrypt
import mysql.connector
import time
from mysql.connector import Error
from mysql.connector import errorcode
connection = mysql.connector.connect(host='192.168.137.1',
                                     database='wifi_tracking',
                                     user='brandon',
                                     password='password')
try:
    for x in range(100):
        #remove first 3 rows of cvs file, unneccesary 
        df=pd.read_csv("../data/capture-01.csv", skiprows=3)
        #deletes white space and colons from MAC address
        df.columns = df.columns.str.lstrip()
        df['Last time seen'] = df['Last time seen'].str.lstrip()
        df['Station MAC'] = df['Station MAC'].str.replace(':', '')
        
        keep_col = ['Station MAC', 'Last time seen', 'Power']
        new_df = df[keep_col]
        for index, row in new_df.iterrows():
            foundMacAddress = False;
            currentMac = row['Station MAC']
            print(row['Station MAC'], row['Last time seen'], row['Power'])
            cursor = connection.cursor()
            cursor.execute("""SELECT mac_address, enabled, device_name FROM registered_macs WHERE enabled = 1""")
            cursorRow = cursor.fetchone()
            while cursorRow:
                print('device name: {} and is enabled: {}'.format(cursorRow[2], cursorRow[1]))
                currentStoredMac = cursorRow[0]
                if bcrypt.checkpw(currentMac.encode('utf8'), currentStoredMac.encode('utf8')):
                    print('Found MAC address '+row['Station MAC']+ ' in database, storing rssi value...')
                    foundMacAddress = True
                cursorRow = cursor.fetchone()
            if (foundMacAddress):
                print('found')
                connection.commit()
                    #cursor.execute("""INSERT INTO
                    #connection.commit()
            else:
                print('MAC address ' +row['Station MAC']+ ' not found')
                connection.commit()
            #new_df.to_csv("../data/db-cap.csv", index = False)
            time.sleep(1)
finally:
    if(connection.is_connected()):
        cursor.close()
        connection.close()
        print("Connection is closed")
