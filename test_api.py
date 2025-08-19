#!/usr/bin/env python3
"""
Test script for Thumbworx API endpoints
"""

import requests
import json
import time
import sys

def test_endpoint(url, description):
    """Test a single endpoint"""
    print(f"Testing {description}: {url}")
    try:
        response = requests.get(url, timeout=10)
        if response.status_code == 200:
            print(f"✅ SUCCESS - Status: {response.status_code}")
            data = response.json()
            print(f"   Response length: {len(data) if isinstance(data, list) else 'N/A'}")
            return True
        else:
            print(f"❌ FAILED - Status: {response.status_code}")
            print(f"   Response: {response.text[:100]}...")
            return False
    except requests.exceptions.RequestException as e:
        print(f"❌ ERROR - {str(e)}")
        return False
    print()

def test_eta_prediction():
    """Test ETA prediction endpoint"""
    url = "http://localhost:5000/api/predict_eta"
    print(f"Testing ETA Prediction: {url}")
    
    payload = {
        "current_lat": 14.5995,
        "current_lng": 120.9842,
        "dropoff_lat": 14.6042,
        "dropoff_lng": 120.9822
    }
    
    try:
        response = requests.post(url, json=payload, timeout=10)
        if response.status_code == 200:
            print(f"✅ SUCCESS - Status: {response.status_code}")
            data = response.json()
            print(f"   ETA: {data.get('eta_minutes', 'N/A')} minutes")
            return True
        else:
            print(f"❌ FAILED - Status: {response.status_code}")
            print(f"   Response: {response.text[:100]}...")
            return False
    except requests.exceptions.RequestException as e:
        print(f"❌ ERROR - {str(e)}")
        return False
    print()

def main():
    """Main test function"""
    print("🚀 Thumbworx API Test Suite")
    print("=" * 50)
    
    # Wait a moment for services to start
    print("Waiting 5 seconds for services to be ready...")
    time.sleep(5)
    
    endpoints = [
        ("http://localhost:3000", "Next.js Frontend"),
        ("http://localhost:8000/api/traccar/devices", "Laravel - Devices"),
        ("http://localhost:8000/api/traccar/positions", "Laravel - Positions"),
        ("http://localhost:5000/api/traccar/devices", "Flask - Devices"),
        ("http://localhost:5000/api/traccar/positions", "Flask - Positions"),
        ("http://localhost:5000/api/positions_cached", "Flask - Cached Positions"),
    ]
    
    results = []
    
    # Test GET endpoints
    for url, description in endpoints:
        success = test_endpoint(url, description)
        results.append((description, success))
        time.sleep(1)  # Brief pause between tests
    
    # Test POST endpoint
    eta_success = test_eta_prediction()
    results.append(("ETA Prediction", eta_success))
    
    # Summary
    print("\n" + "=" * 50)
    print("📊 TEST SUMMARY")
    print("=" * 50)
    
    passed = 0
    total = len(results)
    
    for test_name, success in results:
        status = "✅ PASS" if success else "❌ FAIL"
        print(f"{status} - {test_name}")
        if success:
            passed += 1
    
    print(f"\nResults: {passed}/{total} tests passed")
    
    if passed == total:
        print("🎉 All tests passed! System is working correctly.")
        sys.exit(0)
    else:
        print("⚠️  Some tests failed. Check the logs above.")
        sys.exit(1)

if __name__ == "__main__":
    main()
