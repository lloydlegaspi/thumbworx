# Thumbworx API Test Script
# PowerShell version

Write-Host "🚀 Thumbworx API Test Suite" -ForegroundColor Green
Write-Host ("=" * 50) -ForegroundColor Green

# Wait for services to start
Write-Host "Waiting 5 seconds for services to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# Test endpoints
$endpoints = @(
    @{url="http://localhost:3000"; name="Next.js Frontend"},
    @{url="http://localhost:8000/api/traccar/devices"; name="Laravel - Devices"},
    @{url="http://localhost:8000/api/traccar/positions"; name="Laravel - Positions"},
    @{url="http://localhost:5000/api/traccar/devices"; name="Flask - Devices"},
    @{url="http://localhost:5000/api/traccar/positions"; name="Flask - Positions"},
    @{url="http://localhost:5000/api/positions_cached"; name="Flask - Cached Positions"}
)

$results = @()

foreach ($endpoint in $endpoints) {
    Write-Host "Testing $($endpoint.name): $($endpoint.url)" -ForegroundColor Cyan
    
    try {
        $response = Invoke-WebRequest -Uri $endpoint.url -Method GET -TimeoutSec 10 -ErrorAction Stop
        if ($response.StatusCode -eq 200) {
            Write-Host "✅ SUCCESS - Status: $($response.StatusCode)" -ForegroundColor Green
            $results += @{name=$endpoint.name; success=$true}
        } else {
            Write-Host "❌ FAILED - Status: $($response.StatusCode)" -ForegroundColor Red
            $results += @{name=$endpoint.name; success=$false}
        }
    }
    catch {
        Write-Host "❌ ERROR - $($_.Exception.Message)" -ForegroundColor Red
        $results += @{name=$endpoint.name; success=$false}
    }
    
    Write-Host ""
    Start-Sleep -Seconds 1
}

# Test ETA prediction
Write-Host "Testing ETA Prediction: http://localhost:5000/api/predict_eta" -ForegroundColor Cyan

$etaPayload = @{
    current_lat = 14.5995
    current_lng = 120.9842
    dropoff_lat = 14.6042
    dropoff_lng = 120.9822
} | ConvertTo-Json

try {
    $response = Invoke-WebRequest -Uri "http://localhost:5000/api/predict_eta" -Method POST -Body $etaPayload -ContentType "application/json" -TimeoutSec 10 -ErrorAction Stop
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ SUCCESS - Status: $($response.StatusCode)" -ForegroundColor Green
        $etaData = $response.Content | ConvertFrom-Json
        Write-Host "   ETA: $($etaData.eta_minutes) minutes" -ForegroundColor Green
        $results += @{name="ETA Prediction"; success=$true}
    } else {
        Write-Host "❌ FAILED - Status: $($response.StatusCode)" -ForegroundColor Red
        $results += @{name="ETA Prediction"; success=$false}
    }
}
catch {
    Write-Host "❌ ERROR - $($_.Exception.Message)" -ForegroundColor Red
    $results += @{name="ETA Prediction"; success=$false}
}

# Summary
Write-Host ""
Write-Host ("=" * 50) -ForegroundColor Green
Write-Host "📊 TEST SUMMARY" -ForegroundColor Green
Write-Host ("=" * 50) -ForegroundColor Green

$passed = 0
$total = $results.Count

foreach ($result in $results) {
    if ($result.success) {
        Write-Host "✅ PASS - $($result.name)" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "❌ FAIL - $($result.name)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Results: $passed/$total tests passed" -ForegroundColor $(if ($passed -eq $total) { "Green" } else { "Yellow" })

if ($passed -eq $total) {
    Write-Host "🎉 All tests passed! System is working correctly." -ForegroundColor Green
} else {
    Write-Host "⚠️  Some tests failed. Check the logs above." -ForegroundColor Yellow
}
