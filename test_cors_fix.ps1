# Test API endpoints and CORS configuration
# Run this script after updating Railway environment variables

$apiUrl = "https://thumbworx-production.up.railway.app"
$frontendOrigin = "https://thumbworx.vercel.app"

Write-Host "Testing Thumbworx API Endpoints and CORS..." -ForegroundColor Green
Write-Host "API URL: $apiUrl" -ForegroundColor Yellow
Write-Host "Frontend Origin: $frontendOrigin" -ForegroundColor Yellow
Write-Host ""

# Test 1: Health Check
Write-Host "1. Testing Health Check Endpoint..." -ForegroundColor Cyan
try {
    $healthResponse = Invoke-RestMethod -Uri "$apiUrl/api/health" -Method GET
    Write-Host "✅ Health Check: SUCCESS" -ForegroundColor Green
    Write-Host "Response: $($healthResponse | ConvertTo-Json -Depth 2)" -ForegroundColor Gray
} catch {
    Write-Host "❌ Health Check: FAILED" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 2: CORS Preflight
Write-Host "2. Testing CORS Preflight..." -ForegroundColor Cyan
try {
    $corsHeaders = @{
        'Origin' = $frontendOrigin
        'Access-Control-Request-Method' = 'GET'
        'Access-Control-Request-Headers' = 'Content-Type'
    }
    
    $corsResponse = Invoke-WebRequest -Uri "$apiUrl/api/traccar/devices" -Method OPTIONS -Headers $corsHeaders
    Write-Host "✅ CORS Preflight: SUCCESS" -ForegroundColor Green
    Write-Host "Access-Control-Allow-Origin: $($corsResponse.Headers['Access-Control-Allow-Origin'])" -ForegroundColor Gray
} catch {
    Write-Host "❌ CORS Preflight: FAILED" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 3: Devices Endpoint
Write-Host "3. Testing Devices Endpoint..." -ForegroundColor Cyan
try {
    $devicesResponse = Invoke-RestMethod -Uri "$apiUrl/api/traccar/devices" -Method GET
    Write-Host "✅ Devices Endpoint: SUCCESS" -ForegroundColor Green
    Write-Host "Devices Count: $($devicesResponse.Count)" -ForegroundColor Gray
} catch {
    Write-Host "❌ Devices Endpoint: FAILED" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 4: Positions Cached Endpoint
Write-Host "4. Testing Positions Cached Endpoint..." -ForegroundColor Cyan
try {
    $positionsResponse = Invoke-RestMethod -Uri "$apiUrl/api/traccar/positions-cached" -Method GET
    Write-Host "✅ Positions Cached: SUCCESS" -ForegroundColor Green
    Write-Host "Positions Count: $($positionsResponse.Count)" -ForegroundColor Gray
} catch {
    Write-Host "❌ Positions Cached: FAILED" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 5: Positions Endpoint
Write-Host "5. Testing Positions Endpoint..." -ForegroundColor Cyan
try {
    $positionsResponse = Invoke-RestMethod -Uri "$apiUrl/api/traccar/positions" -Method GET
    Write-Host "✅ Positions Endpoint: SUCCESS" -ForegroundColor Green
    Write-Host "Positions Count: $($positionsResponse.Count)" -ForegroundColor Gray
} catch {
    Write-Host "❌ Positions Endpoint: FAILED" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

Write-Host "=== TEST SUMMARY ===" -ForegroundColor Magenta
Write-Host "If all tests pass, your CORS and API issues should be resolved." -ForegroundColor White
Write-Host "If tests fail, check:" -ForegroundColor White
Write-Host "1. Railway environment variables are updated correctly" -ForegroundColor White
Write-Host "2. Laravel service has been redeployed after env changes" -ForegroundColor White
Write-Host "3. Routes are properly configured in api.php" -ForegroundColor White
