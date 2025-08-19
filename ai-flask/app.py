import os, json, time
from flask import Flask, jsonify, request
from flask_cors import CORS
import requests
import redis
from sqlalchemy import create_engine, MetaData, Table, Column, Integer, Float, String, DateTime, text
from datetime import datetime
import logging

# Set up logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes
 
TRACCAR_BASE = os.getenv("TRACCAR_BASE_URL")
TRACCAR_USER = os.getenv("TRACCAR_USER")
TRACCAR_PASS = os.getenv("TRACCAR_PASS")
REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/0")

# Database configuration - use Render's internal database URL format
DATABASE_URL = os.getenv("DATABASE_URL")
if not DATABASE_URL:
    # Fallback to individual components for local development
    DB_HOST = os.getenv("DB_HOST", "localhost")
    DB_PORT = os.getenv("DB_PORT", "5432")
    DB_NAME = os.getenv("DB_NAME", "thumbworx")
    DB_USER = os.getenv("DB_USER", "thumb_user")
    DB_PASS = os.getenv("DB_PASS", "thumb_pass")
    DATABASE_URL = f"postgresql://{DB_USER}:{DB_PASS}@{DB_HOST}:{DB_PORT}/{DB_NAME}"

# For Render, ensure the URL uses the correct SSL mode
if DATABASE_URL and "render.com" in DATABASE_URL:
    if "sslmode=" not in DATABASE_URL:
        DATABASE_URL += "?sslmode=require"

DB_URL = DATABASE_URL

def wait_for_db():
    """Wait for database to be ready with retries"""
    max_retries = 30
    retry_interval = 2
    
    logger.info(f"Attempting to connect to database: {DB_URL.replace(DB_URL.split('@')[0].split('//')[1], '***')}")
    
    for attempt in range(max_retries):
        try:
            logger.info(f"Attempting database connection (attempt {attempt + 1}/{max_retries})")
            # Create engine with appropriate settings for cloud databases
            engine_kwargs = {
                'pool_pre_ping': True,
                'pool_recycle': 300,
                'connect_args': {}
            }
            
            # Add SSL configuration for cloud databases
            if "render.com" in DB_URL or "amazonaws.com" in DB_URL:
                engine_kwargs['connect_args']['sslmode'] = 'require'
            
            engine = create_engine(DB_URL, **engine_kwargs)
            
            # Test connection with proper SQLAlchemy 2.0 syntax
            with engine.connect() as conn:
                conn.execute(text("SELECT 1"))
            logger.info("Database connection successful!")
            return engine
        except Exception as e:
            logger.warning(f"Database connection attempt {attempt + 1} failed: {e}")
            if attempt < max_retries - 1:
                logger.info(f"Retrying in {retry_interval} seconds...")
                time.sleep(retry_interval)
            else:
                logger.error("Max retries reached. Could not connect to database.")
                logger.error(f"Final connection string format: {DB_URL.split('@')[1] if '@' in DB_URL else 'Invalid URL format'}")
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
@app.route("/api/health")
def health():
    """Health check endpoint"""
    try:
        # Test database connection
        with engine.connect() as conn:
            conn.execute(text("SELECT 1"))
        db_status = "healthy"
        db_info = f"Connected to: {DB_URL.split('@')[1] if '@' in DB_URL else 'local'}"
    except Exception as e:
        db_status = f"error: {str(e)}"
        db_info = f"Failed to connect to: {DB_URL.split('@')[1] if '@' in DB_URL else 'local'}"
    
    try:
        # Test Redis connection
        r.ping()
        redis_status = "healthy"
    except Exception as e:
        redis_status = f"error: {str(e)}"
    
    return jsonify({
        "status": "running",
        "database": db_status,
        "database_info": db_info,
        "redis": redis_status,
        "traccar_base": TRACCAR_BASE,
        "environment_vars": {
            "DATABASE_URL": "set" if os.getenv("DATABASE_URL") else "not set",
            "DB_HOST": os.getenv("DB_HOST", "not set"),
            "DB_NAME": os.getenv("DB_NAME", "not set"),
            "DB_USER": os.getenv("DB_USER", "not set")
        }
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
    port = int(os.environ.get('PORT', 5000))
    app.run(host="0.0.0.0", port=port, debug=False)