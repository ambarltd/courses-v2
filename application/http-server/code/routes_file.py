import logging
import json
from lib import requires_auth
from flask import request, Blueprint, g
from datetime import datetime, timedelta

all_routes = Blueprint('credit_card', __name__)
logger = logging.getLogger('waitress')


@all_routes.route('/signup', methods=['POST'])
@requires_auth
def destination_all_events():
    request_body_as_text = request.get_data(as_text=True)

    db = g.get('conn').cursor()
    now = datetime.now()
    occurred_at_1 = now.strftime("%Y-%m-%d %H:%M:%S")
    db.execute(
        "INSERT INTO event_store_table (partition_key, event_payload, event_type, occurred_at) VALUES (%s, %s, %s, %s)",
        (42, "{}",
         "test_event", occurred_at_1)
    )
    db.close()

    return {"result": {"success": {}}}


@all_routes.route('/credit_card/latest_events', methods=['GET'])
def latest_events():
    db = g.get('conn').cursor()
    db.execute("SELECT request_body FROM event_credit_card  ORDER BY id DESC LIMIT 10;")

    rows = db.fetchall()
    column_names = [col[0] for col in db.description]
    results = []
    for row in rows:
        row_key_value_pairs = dict(zip(column_names, row))
        payload_object = (json.loads(row_key_value_pairs['request_body']))['payload']
        results.append({
            "partition_key": payload_object["partition_key"],
            "serial_column": payload_object["serial_column"],
            "occurred_at": payload_object["occurred_at"],
            "event_type": payload_object["event_type"],
            "event_payload": payload_object["event_payload"]
        })
    db.close()

    return results
