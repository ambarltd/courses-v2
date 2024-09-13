from psycopg2.extensions import ISOLATION_LEVEL_AUTOCOMMIT
import psycopg2
from psycopg2 import pool
import os
import sys
from functools import wraps
from flask import request


def env_var(name):
    if name in os.environ.keys():
        return os.environ[name]
    else:
        raise Exception("Missing environment variable " + name)


def init_connection_pool():
    return psycopg2.pool.ThreadedConnectionPool(
        minconn=3,
        maxconn=15,
        database=env_var("PG_DATABASE"),
        user=env_var("PG_USERNAME"),
        password=env_var("PG_PASSWORD"),
        host=env_var("PG_HOST"),
        port=int(env_var("PG_PORT")),
        connect_timeout=3
    )


def db_setup():
    conn = psycopg2.connect(
        database=env_var("PG_DATABASE"),
        user=env_var("PG_USERNAME"),
        password=env_var("PG_PASSWORD"),
        host=env_var("PG_HOST"),
        port=int(env_var("PG_PORT")),
        connect_timeout=3
    )
    conn.set_isolation_level(ISOLATION_LEVEL_AUTOCOMMIT)
    cursor = conn.cursor()

    print("Creating tables", file=sys.stdout)
    cursor.execute(
        f"""CREATE TABLE IF NOT EXISTS event_store_table (
            serial_column       serial PRIMARY KEY,
            partition_key       integer,
            event_payload       json,
            event_type          text,
            occurred_at         timestamp
        )"""
    )
    print("Created tables", file=sys.stdout)
    cursor.close()
    conn.close()


def requires_auth(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        auth = request.authorization
        if not auth or not (
                auth.username == "course_username" and auth.password == "course_password"):
            return 'Unauthorized', 401
        return f(*args, **kwargs)

    return decorated