import config
import mysql.connector as mysql
import os.path
from os import path
import csv
import time



def open_db_connection(Db):
    global cnx #Set Global variable to hold the connection

    trys = 0
    retry = True

    configDB = config.main if Db == 'main' else config.log

    while retry and trys < config.Number_of_Tries:
        try:
            cnx = mysql.connect(**configDB) # Init connetion with Main DB
            retry = False
        except mysql.Error as err:
                print(err)
                trys += 1
                time.sleep(config.time_to_wait)

    if trys == config.Number_of_Tries:
        print("migration skiped")
        exit()


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