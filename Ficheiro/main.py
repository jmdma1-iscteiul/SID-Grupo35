import utils
import config
from datetime import datetime


def main():
    utils.open_db_connection("main")

    for table in config.tables:
        utils.fetch_to_csv(config.tables[table])

    utils.close_db_connection()

    utils.open_db_connection("log")

    for table in config.tables:
        utils.insert_from_csv(config.tables[table])

    utils.close_db_connection()
main()


