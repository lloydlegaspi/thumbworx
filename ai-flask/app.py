import os, json, time
from flask import Flask, jsonify, request
import requests
import redis
from sqlalchemy import create_engine, MetaData, Table, Column, Integer, Float, String, DateTime, text
from datetime import datetime
import logging

# Set up logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)
 
TRACCAR_BASE = os.getenv("TRACCAR_BASE_URL")
TRACCAR_USER = os.getenv("TRACCAR_USER")
TRACCAR_PASS = os.getenv("TRACCAR_PASS")
REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/0")
DB_URL = os.getenv("DATABASE_URL", "postgresql://thumb_user:thumb_pass@localhost/thumbworx")

def wait_for_db():
    """Wait for database to be ready with retries"""
    max_retries = 30
    retry_interval = 2
    
    for attempt in range(max_retries):
        try:
            logger.info(f"Attempting database connection (attempt {attempt + 1}/{max_retries})")
            engine = create_engine(DB_URL)
            # Test connection with proper SQLAlchemy 2.0 syntax
            with engine.connect() as conn:
                conn.execute(text("SELECT 1"))
            logger.info("Database connection successful!")
            return engine
        except Exception as e:
            logger.warning(f"Database connection attempt {attempt + 1} failed: {e}")
            if attempt < max_retries - 1:
                logger.info(f"Retrying in 2 seconds...")
                time.sleep(retry_interval)
            else:
                logger.error("Max retries reached. Could not connect to database.")
                raise

# Wait for database and create engine
engine = wait_for_db()
 
r = redis.from_url(REDIS_URL)
metadata = MetaData()
 
positions = Table('positions', metadata,
    Column('id', Integer, primary_key=True),
    Column('device_id', Integer),
    Column('latitude', Float),
    Column('longitude', Float),
    Column('speed', Float),
    Column('device_time', DateTime),
    Column('attributes', String),
    Column('created_at', DateTime),
    Column('updated_at', DateTime),
)
 
metadata.create_all(engine)
 
app = Flask(__name__)
 
def traccar_auth():
    return (TRACCAR_USER, TRACCAR_PASS)

@app.route("/")
def home():
    """Root endpoint to show API information"""
    return jsonify({
        "message": "Thumbworx AI Flask API",
        "version": "1.0",
        "endpoints": {
            "/api/traccar/devices": "GET - Fetch devices from Traccar",
            "/api/traccar/positions": "GET - Fetch positions from Traccar",
            "/api/positions_cached": "GET - Get cached positions from Redis",
            "/api/predict_eta": "POST - Predict ETA between two points"
        },
        "status": "running"
    })

@app.route("/health")
def health():
    """Health check endpoint"""
    try:
        # Test database connection
        with engine.connect() as conn:
            conn.execute(text("SELECT 1"))
        db_status = "healthy"
    except Exception as e:
        db_status = f"error: {str(e)}"
    
    try:
        # Test Redis connection
        r.ping()
        redis_status = "healthy"
    except Exception as e:
        redis_status = f"error: {str(e)}"
    
    return jsonify({
        "status": "running",
        "database": db_status,
        "redis": redis_status,
        "traccar_base": TRACCAR_BASE
    })
 
@app.route("/api/traccar/devices")
def devices():
    try:
        res = requests.get(f"{TRACCAR_BASE}/api/devices", auth=traccar_auth(), timeout=10)
        res.raise_for_status()  # Raise an exception for bad status codes
        return jsonify(res.json())
    except requests.exceptions.RequestException as e:
        logger.error(f"Error fetching devices from Traccar: {e}")
        return jsonify({"error": "Failed to fetch devices from Traccar", "details": str(e)}), 500
    except ValueError as e:
        logger.error(f"Invalid JSON response from Traccar: {e}")
        return jsonify({"error": "Invalid response from Traccar", "details": str(e)}), 500
 
@app.route("/api/traccar/positions")
def positions_api():
    try:
        # Query traccar positions
        res = requests.get(f"{TRACCAR_BASE}/api/positions", auth=traccar_auth(), params=request.args, timeout=10)
        res.raise_for_status()
        items = res.json()
        
        # cache in redis (ttl 30s)
        r.set("latest_positions", json.dumps(items), ex=30)
        
        # persist latest N positions (optional)
        with engine.begin() as conn:
            for p in items:
                # Handle deviceTime conversion safely
                device_time = p.get("deviceTime")
                if device_time:
                    try:
                        if isinstance(device_time, str):
                            # Try to parse as ISO datetime string first
                            timestamp = datetime.fromisoformat(device_time.replace('Z', '+00:00'))
                        else:
                            # Assume it's a timestamp in milliseconds
                            timestamp = datetime.fromtimestamp(float(device_time)/1000.0)
                    except (ValueError, TypeError):
                        timestamp = datetime.utcnow()
                else:
                    timestamp = datetime.utcnow()
                    
                conn.execute(positions.insert().values(
                    device_id=p.get("deviceId"),
                    latitude=p.get("latitude"),
                    longitude=p.get("longitude"),
                    speed=p.get("speed"),
                    device_time=timestamp,
                    attributes=json.dumps(p.get("attributes", {})),
                    created_at=datetime.utcnow(),
                    updated_at=datetime.utcnow()
                ))
        return jsonify(items)
    except requests.exceptions.RequestException as e:
        logger.error(f"Error fetching positions from Traccar: {e}")
        return jsonify({"error": "Failed to fetch positions from Traccar", "details": str(e)}), 500
    except ValueError as e:
        logger.error(f"Invalid JSON response from Traccar: {e}")
        return jsonify({"error": "Invalid response from Traccar", "details": str(e)}), 500
    except Exception as e:
        logger.error(f"Error processing positions: {e}")
        return jsonify({"error": "Failed to process positions", "details": str(e)}), 500
 
@app.route("/api/positions_cached")
def cached_positions():
    data = r.get("latest_positions")
    if data:
        return jsonify(json.loads(data))
    return jsonify([])
 
# lightweight ETA endpoint stub
@app.route("/api/predict_eta", methods=["POST"])
def predict_eta():
    payload = request.json  # {current_lat, current_lng, dropoff_lat, dropoff_lng}
    # replace with your model call or simple heuristic
    # this example returns simple ETA = distance(km) * 3 (min per km)
    from geopy.distance import geodesic
    a = (payload['current_lat'], payload['current_lng'])
    b = (payload['dropoff_lat'], payload['dropoff_lng'])
    km = geodesic(a, b).km
    eta_minutes = km * 3
    return jsonify({"eta_minutes": round(eta_minutes,2)})
 
if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)