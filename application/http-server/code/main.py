import logging
from psycopg2.extensions import ISOLATION_LEVEL_AUTOCOMMIT
from lib import init_connection_pool, db_setup
from routes_file import all_routes
from flask import Flask, request, g
from flask_cors import CORS
from waitress import serve

# Start Flask
app = Flask(__name__)
app.json.sort_keys = False

# Logging and DB
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger('waitress')
connection_pool = init_connection_pool()


@app.before_request
def log_and_db_setup():
    logger.info(request.url)

    global connection_pool
    if connection_pool.closed:
        connection_pool = init_connection_pool()

    if 'conn' not in g:
        connection = connection_pool.getconn()
        g.conn = connection
        g.get('conn').set_isolation_level(ISOLATION_LEVEL_AUTOCOMMIT)


@app.teardown_request
def db_cleanup(_exception):
    global connection_pool
    conn = g.pop('conn', None)

    if conn is None:
        return

    try:
        cursor = conn.cursor()
        cursor.execute("SELECT 1")
        cursor.close()
    except Exception as error:
        if not conn.closed:
            conn.close()

    connection_pool.putconn(conn)


# Setup Cors
cors = CORS(app)
app.config['CORS_HEADERS'] = 'Content-Type'

# Routes
app.register_blueprint(all_routes)

# Run
# db_setup()
if __name__ == '__main__':
    serve(app, host='0.0.0.0', port=8080)