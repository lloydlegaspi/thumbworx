# Production URL Testing Script
# Tests all deployed services to verify they're responding correctly

Write-Host "Testing Thumbworx Production Deployment..." -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

$urls = @{
    "Frontend (Vercel)" = "https://thumbworx.vercel.app/"
    "Laravel API Health" = "https://thumbworx-production.up.railway.app/"
    "Flask API Health" = "https://thumbworx.onrender.com/"
    "Laravel Devices API" = "https://thumbworx-production.up.railway.app/api/traccar/devices"
    "Laravel Positions API" = "https://thumbworx-production.up.railway.app/api/traccar/positions"
    "Flask Devices API" = "https://thumbworx.onrender.com/api/traccar/devices"
    "Flask Positions API" = "https://thumbworx.onrender.com/api/traccar/positions"
}

foreach ($service in $urls.GetEnumerator()) {
    Write-Host "`nTesting: $($service.Key)" -ForegroundColor Yellow
    Write-Host "URL: $($service.Value)" -ForegroundColor Gray
    
    try {
        $response = Invoke-WebRequest -Uri $service.Value -Method GET -TimeoutSec 30 -UseBasicParsing
        
        if ($response.StatusCode -eq 200) {
            Write-Host "✅ SUCCESS - Status: $($response.StatusCode)" -ForegroundColor Green
            
            # Show content length for API responses
            if ($service.Value -like "*api*") {
                $contentLength = $response.Content.Length
                Write-Host "   Response Size: $contentLength bytes" -ForegroundColor Gray
                
                # Try to parse JSON for API endpoints
                try {
                    $json = $response.Content | ConvertFrom-Json
                    if ($json -is [array]) {
                        Write-Host "   Data: Array with $($json.Count) items" -ForegroundColor Gray
                    } else {
                        Write-Host "   Data: JSON object returned" -ForegroundColor Gray
                    }
                } catch {
                    Write-Host "   Data: Non-JSON response" -ForegroundColor Gray
                }
            }
        } else {
            Write-Host "⚠️ WARNING - Status: $($response.StatusCode)" -ForegroundColor Yellow
        }
    }
    catch {
        Write-Host "❌ FAILED - Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "Environment Variables for Vercel:" -ForegroundColor Cyan
Write-Host "NEXT_PUBLIC_API_URL=https://thumbworx-production.up.railway.app/api" -ForegroundColor Green
Write-Host "NEXT_PUBLIC_FLASK_URL=https://thumbworx.onrender.com" -ForegroundColor Green

Write-Host "`nTesting Complete! Check results above." -ForegroundColor Cyan
