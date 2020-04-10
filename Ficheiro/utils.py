import config as mscfg
import mysql.connector as mysql
import os.path
from os import path
import csv
import time

tables = {
    'LOG' : "log",

}

def open_db_connection(Db):
    global cnx #Set Global variable to hold the connection

    trys = 0
    retry = True

    config = mscfg.main if Db == 'main' else mscfg.log

    while retry and trys < 3:
        try:
            cnx = mysql.connect(**config) # Init connetion with Main DB
            retry = False
        except mysql.Error as err:
            if err.errno == mysql.errorcode.ER_ACCESS_DENIED_ERROR:
                print("Something is wrong with your user name or password")
            elif err.errno == mysql.errorcode.ER_BAD_DB_ERROR:
                print("Database does not exist")
            else:
                print("err")
                trys += 1
                time.sleep(5)


def close_db_connection():
    cnx.close() #Closes the conection with current DB


def fetch_to_csv(table):
    cursor = cnx.cursor(buffered=True)
    query = ("SELECT * FROM "+table+" WHERE Migrado = 0")
    cursor.execute(query)
    #update_data(table)
    save_csv(table, cursor.fetchall())


def insert_data(table,data):
    cursor = cnx.cursor(buffered=True)
    query = ('INSERT INTO '+table+' VALUES (' + '"{0}"'.format('", "'.join(data)) + ')')
    cursor.execute(query)
    cnx.commit()

def update_data(table):
    cursor = cnx.cursor()
    query = ('UPDATE '+ table +' SET Migrado = 1')
    cursor.execute(query) 
    cnx.commit()


def save_csv(table,rows): #Saves the the query result to an CSV file
    if len(rows) > 0:
        with open(table+'.csv', 'w', newline='') as csvfile: 
            spamwriter = csv.writer(csvfile, delimiter=',',quotechar='|', quoting=csv.QUOTE_MINIMAL)
            for row in rows: 
                spamwriter.writerow(row)



def insert_from_csv(table): #Reads the the content of an CSV file
    if (path.exists(table+'.csv')):
        with open(table+'.csv', newline='') as csvfile: 
            spamreader = csv.reader(csvfile, delimiter=',', quotechar='|')
            for row in spamreader:
                insert_data(table,row)
        os.remove(table+'.csv')