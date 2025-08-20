# Detailed API Diagnostics
$apiUrl = "https://thumbworx-production.up.railway.app"

Write-Host "=== DETAILED API DIAGNOSTICS ===" -ForegroundColor Magenta
Write-Host ""

# Test 1: Base Laravel Response
Write-Host "1. Testing Base Laravel Application..." -ForegroundColor Cyan
try {
    $baseResponse = Invoke-WebRequest -Uri $apiUrl -Method Get
    Write-Host "✅ Base App: $($baseResponse.StatusCode) $($baseResponse.StatusDescription)" -ForegroundColor Green
    Write-Host "Content-Type: $($baseResponse.Headers['Content-Type'])" -ForegroundColor Gray
} catch {
    Write-Host "❌ Base App: FAILED" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 2: Check if it's a routing issue - try different paths
$testPaths = @(
    "/api",
    "/api/",
    "/api/health",
    "/api/traccar/devices",
    "/health"
)

Write-Host "2. Testing Different API Paths..." -ForegroundColor Cyan
foreach ($path in $testPaths) {
    try {
        $response = Invoke-WebRequest -Uri "$apiUrl$path" -Method Get
        Write-Host "✅ $path : $($response.StatusCode)" -ForegroundColor Green
    } catch {
        $statusCode = $_.Exception.Response.StatusCode.Value__
        if ($statusCode -eq 404) {
            Write-Host "❌ $path : 404 (Not Found)" -ForegroundColor Red
        } elseif ($statusCode -eq 500) {
            Write-Host "⚠️ $path : 500 (Server Error)" -ForegroundColor Yellow
        } else {
            Write-Host "❌ $path : $statusCode" -ForegroundColor Red
        }
    }
}
Write-Host ""

# Test 3: Check CORS response headers
Write-Host "3. Checking CORS Headers..." -ForegroundColor Cyan
try {
    $corsResponse = Invoke-WebRequest -Uri "$apiUrl/api/health" -Method Options -Headers @{
        'Origin' = 'https://thumbworx.vercel.app'
        'Access-Control-Request-Method' = 'GET'
    }
    Write-Host "✅ OPTIONS Request: $($corsResponse.StatusCode)" -ForegroundColor Green
    
    if ($corsResponse.Headers['Access-Control-Allow-Origin']) {
        Write-Host "CORS Origin: $($corsResponse.Headers['Access-Control-Allow-Origin'])" -ForegroundColor Gray
    }
    if ($corsResponse.Headers['Access-Control-Allow-Methods']) {
        Write-Host "CORS Methods: $($corsResponse.Headers['Access-Control-Allow-Methods'])" -ForegroundColor Gray
    }
} catch {
    Write-Host "❌ CORS Check: FAILED" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 4: Check Laravel route list (won't work remotely, but good to know)
Write-Host "4. Recommendations..." -ForegroundColor Cyan
Write-Host "If all API paths return 404, the issue is likely:" -ForegroundColor Yellow
Write-Host "• Route caching is causing issues" -ForegroundColor White
Write-Host "• .env configuration is wrong" -ForegroundColor White
Write-Host "• APP_KEY is missing or invalid" -ForegroundColor White
Write-Host "• Laravel is not finding the routes/api.php file" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Check Railway logs for PHP errors" -ForegroundColor White
Write-Host "2. Verify APP_KEY is set in Railway env vars" -ForegroundColor White
Write-Host "3. Update CORS_ALLOWED_ORIGINS to: https://thumbworx.vercel.app" -ForegroundColor White
Write-Host "4. Redeploy the Laravel service" -ForegroundColor White
