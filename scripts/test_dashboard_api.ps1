# Test script for dashboard API
# Usage: Open PowerShell and run: .\test_dashboard_api.ps1
# This script calls dashboard_api.php?action=getAll and prints the JSON response.

$baseUrl = "http://localhost/RepairSystem-main/backend/api/dashboard_api.php?action=getAll"

try {
    Write-Host "Calling: $baseUrl"
    $resp = Invoke-RestMethod -Uri $baseUrl -Method Get -UseBasicParsing
    Write-Host "---- Response ----"
    $resp | ConvertTo-Json -Depth 5
} catch {
    Write-Host "Request failed: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.InvocationInfo.MyCommand.Path) {
        Write-Host "Check that XAMPP/Apache is running and the URL is correct." -ForegroundColor Yellow
    }
}
