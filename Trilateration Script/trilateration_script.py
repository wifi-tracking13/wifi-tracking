import filterpy
from filterpy.kalman import KalmanFilter
from filterpy.common import Q_discrete_white_noise
import numpy as np
import time
import mysql.connector
from mysql.connector import Error
from mysql.connector import errorcode
from dateutil.parser import parse
connection = mysql.connector.connect(host='localhost',
                                     database='wifi_tracking',
                                     user='brandon',
                                     password='password')

# Dictionary of Kalman Filter objects
kalmanObjs = {}

# Function that takes coordinates of 3 Raspberry Pis + device distances from Pi and return intersection point
def trilaterate(x1,y1,r1,x2,y2,r2,x3,y3,r3):
    A = 2*x2 - 2*x1
    B = 2*y2 - 2*y1
    C = r1**2 - r2**2 - x1**2 + x2**2 - y1**2 + y2**2
    D = 2*x3 - 2*x2
    E = 2*y3 - 2*y2
    F = r2**2 - r3**2 - x2**2 + x3**2 - y2**2 + y3**2
    x = (C*E - F*B) / (E*A - B*D)
    y = (C*D - A*F) / (B*D - A*E)
    return x,y

#Initializes a kalman filter object with a RSSI value parameter
def initialize(pow):
    f = KalmanFilter (dim_x=2, dim_z=1)
    f.x = np.array([pow, 0.])
    f.F = np.array([[1., 1.],
                   [0.,1.]])
    f.H = np.array([[1.,0.]])
    f.P = np.array([[1000., 0.],
                   [0., 1000.]])
    f.R = np.array([[5.]])
    f.Q = Q_discrete_white_noise(dim=2, dt=0.1, var=0.13)
    return f
        
# Function to grab rssi power values from table 'rssi' to combine into a single row
# for specific device/timestamp and store in 'joined_rssi' table
def join_rssi():
    cursor1 = connection.cursor()
    cursor1.execute("""SELECT * FROM rssi WHERE joined = '0' ORDER by last_seen ASC""")
    
    records = cursor1.fetchall()
    for row in records:
        pi_1_power = None
        pi_2_power = None
        pi_3_power = None
        
        # Store power value in variable if found for specific row
        if (row[3] != None):
            pi_1_power = row[3]
        if (row[4] != None):
            pi_2_power = row[4]
        if (row[5] != None):
            pi_3_power = row[5]
        
        # Create row and store power value in 'joined_rssi' table
        cursor1.execute("""SELECT device_id, last_seen FROM joined_rssi WHERE device_id = %s AND last_seen = %s LIMIT 1""", (row[1], row[2]))
        row2 = cursor1.fetchall()
        if(cursor1.rowcount == 0):
            ("Not yet combined, creating new row in joined_rssi table...")
            cursor1.execute("""INSERT INTO joined_rssi (device_id, last_seen) VALUES (%s, %s)""", (row[1], row[2]))
            if (pi_1_power != None):
                cursor1.execute("""UPDATE joined_rssi SET pi_1_power = %s WHERE device_id = %s AND last_seen = %s""", (pi_1_power, row[1], row[2]))
            if (pi_2_power != None):
                cursor1.execute("""UPDATE joined_rssi SET pi_2_power = %s WHERE device_id = %s AND last_seen = %s""", (pi_2_power, row[1], row[2]))
            if (pi_3_power != None):
                cursor1.execute("""UPDATE joined_rssi SET pi_3_power = %s WHERE device_id = %s AND last_seen = %s""", (pi_3_power, row[1], row[2]))
        else:
            ("Found row in joined_rssi table, storing power value...")
            if (pi_1_power != None):
                cursor1.execute("""UPDATE joined_rssi SET pi_1_power = %s WHERE device_id = %s AND last_seen = %s""", (pi_1_power, row[1], row[2]))
            if (pi_2_power != None):
                cursor1.execute("""UPDATE joined_rssi SET pi_2_power = %s WHERE device_id = %s AND last_seen = %s""", (pi_2_power, row[1], row[2]))
            if (pi_3_power != None):
                cursor1.execute("""UPDATE joined_rssi SET pi_3_power = %s WHERE device_id = %s AND last_seen = %s""", (pi_3_power, row[1], row[2]))
        # Update individual RSSI strengths from 'rssi' after combinining as to not query these past values
        cursor1.execute("""UPDATE rssi SET joined = 1 WHERE device_id=%s AND last_seen=%s""", (row[1], row[2]))
    connection.commit()

def kalman_filter():
    cursor = connection.cursor()
    cursor.execute("""SELECT * FROM joined_rssi WHERE PREDICT = '0' ORDER by last_seen ASC""")
    result = cursor.fetchall()
    for row in result:
        pi_1_pow = None
        pi_2_pow = None
        pi_3_pow = None
        trilaterateFlag = False
        currentId = int(row[0])
        currentUniqueId = row[1]
        last_seen = row[2]
        pi_1_pow = row[3]
        pi_2_pow = row[4]
        pi_3_pow = row[5]

        if (pi_1_pow != None):
            if(kalmanObjs.get("pow_1_"+str(currentUniqueId)) == None):
                #Initialize kalman filter object and store in dictionary
                kalmanObj = initialize(int(pi_1_pow))
                kalmanObjs["pow_1_"+str(currentUniqueId)] = kalmanObj
            else:
                #Get kalman filter object from dictionary and update the RSSI power value
                currentKalmanObj = kalmanObjs.get("pow_1_"+str(currentUniqueId))
                currentKalmanObj.predict()
                currentKalmanObj.update(int(pi_1_pow))
        else:
            #Predict the RSSI value if we have a kalman object in dictionary
            if(kalmanObjs.get("pow_1_"+str(currentUniqueId)) != None):
                currentKalmanObj = kalmanObjs.get("pow_1_"+str(currentUniqueId))
                currentKalmanObj.predict()
                array =  currentKalmanObj.x
                pi_1_pow = array.item(0)

        if (pi_2_pow != None):
            if(kalmanObjs.get("pow_2_"+str(currentUniqueId)) == None):
                #Initialize kalman filter object and store in dictionary
                kalmanObj = initialize(int(pi_2_pow))
                kalmanObjs["pow_2_"+str(currentUniqueId)] = kalmanObj
            else:
                #Get kalman filter object from dictionary and update the RSSI power value
                currentKalmanObj = kalmanObjs.get("pow_2_"+str(currentUniqueId))
                currentKalmanObj.predict()
                currentKalmanObj.update(int(pi_2_pow))
        else:
            #Predict the RSSI value if we have a kalman object in dictionary
            if(kalmanObjs.get("pow_2_"+str(currentUniqueId)) != None):
                currentKalmanObj = kalmanObjs.get("pow_2_"+str(currentUniqueId))
                currentKalmanObj.predict()
                array =  currentKalmanObj.x
                pi_2_pow = array.item(0)

        if (pi_3_pow != None):
            if(kalmanObjs.get("pow_3_"+str(currentUniqueId)) == None):
                #Initialize kalman filter object and store in dictionary
                kalmanObj = initialize(int(pi_3_pow))
                kalmanObjs["pow_3_"+str(currentUniqueId)] = kalmanObj
            else:
                #Get kalman filter object from dictionary and update the RSSI power value
                currentKalmanObj = kalmanObjs.get("pow_3_"+str(currentUniqueId))
                currentKalmanObj.predict()
                currentKalmanObj.update(int(pi_3_pow))
        else:
            #Predict the RSSI value if we have a kalman object in dictionary
            if(kalmanObjs.get("pow_3_"+str(currentUniqueId)) != None):
                currentKalmanObj = kalmanObjs.get("pow_3_"+str(currentUniqueId))
                currentKalmanObj.predict()
                array =  currentKalmanObj.x
                pi_3_pow = array.item(0)

        cursor.execute("""UPDATE joined_rssi SET predict = '1' WHERE id = %s""", (currentId,))
        connection.commit()

        #Find coordinates if we have all three power values
        if(pi_1_pow != None and pi_2_pow != None and pi_3_pow != None):
            trilaterateFlag = True
            pi_1_distance = 10**((-28.33-float(pi_1_pow))/30)
            pi_2_distance = 10**((-33.2-float(pi_2_pow))/30)
            pi_3_distance = 10**((-30.53-float(pi_3_pow))/30)
            x, y = trilaterate(1.52,0,pi_1_distance,0.39,2.83,pi_2_distance,2.87,2.57,pi_3_distance)

        #If statement to input coordinates if all three power values are known
        if(trilaterateFlag):
            cursor.execute("""INSERT INTO predicted_rssi (device_id, last_seen, pi_1_power, pi_2_power, pi_3_power, x_coord, y_coord) VALUES (%s, %s, %s, %s, %s, %s, %s)""", (currentUniqueId, last_seen, pi_1_pow, pi_2_pow, pi_3_pow, x, y))
            connection.commit()
        else:
            cursor.execute("""INSERT INTO predicted_rssi (device_id, last_seen, pi_1_power, pi_2_power, pi_3_power) VALUES (%s, %s, %s, %s, %s)""", (currentUniqueId, last_seen, pi_1_pow, pi_2_pow, pi_3_pow))
            connection.commit()
try:
    while True:
        start = time.time()
        join_rssi()
        kalman_filter()
        time.sleep(1)
        end = time.time()
        print(end - start)
finally:    
    if(connection.is_connected()):
        cursor.close()
        connection.close()
        print("connection is closed")