# Instructions for Deployment

## Vercel Deployment Environment Variables
# Add these in your Vercel dashboard under Settings > Environment Variables

# Production API URL (Replace with your actual backend URL)
NEXT_PUBLIC_API_URL=https://your-backend-domain.com

# Example URLs based on your architecture:
# If Laravel backend is on Vercel: https://thumbworx-backend.vercel.app
# If Laravel backend is on Render: https://thumbworx-backend.onrender.com
# If using Railway: https://thumbworx-backend.up.railway.app

## Backend Environment Variables (Railway/Render)
# Make sure your Laravel backend has these configured:

# Database (Railway PostgreSQL)
DATABASE_URL=postgresql://username:password@host:port/database

# Traccar Configuration
TRACCAR_BASE_URL=https://your-traccar-server.com
TRACCAR_USER=admin
TRACCAR_PASS=your-password

# Flask AI Service URL (Render)
FLASK_SERVICE_URL=https://your-flask-app.onrender.com

## CORS Configuration
# Make sure your Laravel backend allows requests from your Vercel domain
# In config/cors.php, add your Vercel domain to allowed_origins
