import utils
from datetime import datetime


def main():
    dateTimeObj = datetime.now()
    print(dateTimeObj)

    utils.open_db_connection("main")

    for table in utils.tables:
        utils.fetch_to_csv(utils.tables[table])

    utils.close_db_connection()

    utils.open_db_connection("log")

    for table in utils.tables:
        utils.insert_from_csv(utils.tables[table])

    utils.close_db_connection()
    
    dateTimeObj2 = datetime.now()
    print(dateTimeObj2)

    print(dateTimeObj2 - dateTimeObj)

main()


