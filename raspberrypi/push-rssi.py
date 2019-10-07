import pandas as pd
import time
import bcrypt
import mysql.connector
from mysql.connector import Error
from mysql.connector import errorcode
from dateutil.parser import parse
connection = mysql.connector.connect(host='192.168.137.1',
                                     database='wifi_tracking',
                                     user='brandon',
                                     password='password')
try:
    while True:
        pi_id = input ("Enter pi identification number (1, 2, or 3): ")
        # check if pi_id is equal to one of the strings, specified in the list
        if pi_id in ['1', '2', '3']:
        # if it was equal - break from the while loop
            break
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
                cursor = connection.cursor(buffered=True)
                cursor.execute("""SELECT mac_address, id FROM registered_macs WHERE enabled = 1""")
                cursorRow = cursor.fetchone()
                currentUniqueId = -1
                while cursorRow:
                    currentStoredMac = cursorRow[0]
                    
                    if bcrypt.checkpw(currentMac.encode('utf8'), currentStoredMac.encode('utf8')):
                        print('Found! Storing RSSI value now...'.format(currentMac))
                        currentUniqueId = cursorRow[1]
                        foundMacAddress = True
                    cursorRow = cursor.fetchone()
                if (foundMacAddress):
                    cursor.execute("""SELECT device_id, last_seen FROM rssi WHERE device_id=%s AND last_seen=%s""", (currentUniqueId, row['Last time seen'],))
                    if cursor.fetchone() == None:
                        # if specified timestamp and id is not currectly in DB create a row, else update row
                        if(pi_id=='1'):
                            cursor.execute("""INSERT INTO rssi (device_id, last_seen, pi_1_power) VALUES (%s, %s, %s)""", (currentUniqueId, row['Last time seen'], row['Power']))
                        elif(pi_id=='2'):
                            cursor.execute("""INSERT INTO rssi (device_id, last_seen, pi_2_power) VALUES (%s, %s, %s)""", (currentUniqueId, row['Last time seen'], row['Power']))
                        elif(pi_id=='3'):
                            cursor.execute("""INSERT INTO rssi (device_id, last_seen, pi_3_power) VALUES (%s, %s, %s)""", (currentUniqueId, row['Last time seen'], row['Power']))
                    else:
                        if(pi_id=='1'):
                            cursor.execute("""UPDATE rssi SET pi_1_power = %s WHERE device_id=%s AND last_seen=%s""", (row['Power'], currentUniqueId, row['Last time seen']))
                        elif(pi_id=='2'):
                            cursor.execute("""UPDATE rssi SET pi_2_power = %s WHERE device_id=%s AND last_seen=%s""", (row['Power'], currentUniqueId, row['Last time seen']))
                        elif(pi_id=='3'):
                            cursor.execute("""UPDATE rssi SET pi_3_power = %s WHERE device_id=%s AND last_seen=%s""", (row['Power'], currentUniqueId, row['Last time seen']))
                    connection.commit()
                else:
                    connection.commit()
finally:
    if(connection.is_connected()):
        cursor.close()
        connection.close()
        print("Connection is closed")
