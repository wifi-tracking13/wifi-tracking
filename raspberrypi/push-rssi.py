import pandas as pd
import time 

for x in range(10):
    #remove first 3 rows of cvs file, unneccesary 
    df=pd.read_csv("../data/capture-01.csv", skiprows=3)
    #deletes strange white space at the beginning of column headers
    df.columns = df.columns.str.lstrip()
    keep_col = ['Station MAC', 'Last time seen', 'Power']
    new_df = df[keep_col]
    new_df.to_csv("db-cap.csv", index = False)
    print(new_df)
    time.sleep(1)
