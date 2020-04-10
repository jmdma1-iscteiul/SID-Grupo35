
#Defines the database configurations to access the Orign DB
main = {
  'user': 'root',
  'password': '',
  'host': '127.0.0.1',
  'database': 'testmain',
  'raise_on_warnings': True
}


#Defines the database configurations to access the log DB
log = {
  'user': 'root',
  'password': '',
  'host': '127.0.0.1',
  'database': 'testlog',
  'raise_on_warnings': True
}

#Defines the table names that should be migrated
tables = {
    'LOG' : "log",
}

#Defines how many tries should be made when trying to migrate in case of failure 
Number_of_Tries = 3

#Defines how much time to wait between each try in case of failure (seconds)
time_to_wait = 3600 