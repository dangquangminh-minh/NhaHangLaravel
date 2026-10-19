param([string]$LaragonRoot = 'C:\laragon')
$ErrorActionPreference = 'Stop'
$taskRoot = $PSScriptRoot
$taskRuntime = Join-Path $taskRoot '.runtime'
$taskEnvPath = Join-Path $taskRoot '.env'
if (!(Test-Path -LiteralPath $taskEnvPath)) { throw 'Chưa có .env. Xem README để cài theo Laragon.' }
$taskEnv = Get-Content -LiteralPath $taskEnvPath -Raw
if ($taskEnv -notmatch '(?m)^DB_PORT=33307\s*$' -or $taskEnv -notmatch '(?m)^DB_DATABASE=nhahang_demo\s*$') { throw 'Script này chỉ dùng cho bản demo riêng cổng 33307/database nhahang_demo. Cấu hình Laragon thông thường xem README.' }
$taskConfigPath = Join-Path $taskRuntime 'httpd.conf'
$taskDataPath = Join-Path $taskRuntime 'mysql-data'
if (!(Test-Path -LiteralPath $taskConfigPath) -or !(Test-Path -LiteralPath $taskDataPath)) { throw 'Không có runtime đã khởi tạo. Cài mới bằng README; script không tạo lại hoặc xóa database.' }
$taskConfigText = Get-Content -LiteralPath $taskConfigPath -Raw
$taskConfigText = [regex]::Replace($taskConfigText, '(?m)^(PidFile|ErrorLog|DocumentRoot) "[^"\r\n]+"', { param($m)
    switch ($m.Groups[1].Value) {
        'PidFile' { 'PidFile "'+(Join-Path $taskRuntime 'apache.pid').Replace('\','/')+'"' }
        'ErrorLog' { 'ErrorLog "'+(Join-Path $taskRuntime 'apache-error.log').Replace('\','/')+'"' }
        'DocumentRoot' { 'DocumentRoot "'+(Join-Path $taskRoot 'public').Replace('\','/')+'"' }
    }
})
$taskConfigText = [regex]::Replace($taskConfigText, '<Directory "[^"\r\n]+">', '<Directory "'+(Join-Path $taskRoot 'public').Replace('\','/')+'">')
[System.IO.File]::WriteAllText($taskConfigPath, $taskConfigText, [System.Text.UTF8Encoding]::new($false))
$taskMysqlDirectory = Join-Path $LaragonRoot 'bin\mysql\mysql-8.4.3-winx64'
$taskPhpDirectory = Join-Path $LaragonRoot 'bin\php\php-8.3.30-Win32-vs16-x64'
$taskApacheDirectory = Join-Path $LaragonRoot 'bin\apache\httpd-2.4.66-260223-Win64-VS18'
function Test-DemoProcess([string]$pidFile,[string]$processName) {
    if (!(Test-Path -LiteralPath $pidFile)) { return $false }
    $taskServerId = 0
    if (![int]::TryParse((Get-Content -LiteralPath $pidFile -Raw).Trim(),[ref]$taskServerId)) { return $false }
    $taskProcess = Get-Process -Id $taskServerId -ErrorAction SilentlyContinue
    return $null -ne $taskProcess -and $taskProcess.ProcessName -eq $processName
}
if (!(Test-DemoProcess (Join-Path $taskRuntime 'mysql.pid') 'mysqld')) {
    if (Get-NetTCPConnection -LocalPort 33307 -State Listen -ErrorAction SilentlyContinue) { throw 'Cổng 33307 đang được tiến trình khác dùng; không thay đổi tiến trình đó.' }
    $taskMysqlArgs = @('--no-defaults',('--basedir="'+$taskMysqlDirectory+'"'),('--datadir="'+$taskDataPath+'"'),'--port=33307','--bind-address=127.0.0.1','--mysqlx=OFF',('--log-error="'+(Join-Path $taskRuntime 'mysql-error.log')+'"'),('--pid-file="'+(Join-Path $taskRuntime 'mysql.pid')+'"'))
    Start-Process -FilePath (Join-Path $taskMysqlDirectory 'bin\mysqld.exe') -ArgumentList $taskMysqlArgs -WindowStyle Hidden
}
if (!(Test-DemoProcess (Join-Path $taskRuntime 'apache.pid') 'httpd')) {
    if (Get-NetTCPConnection -LocalPort 18080 -State Listen -ErrorAction SilentlyContinue) { throw 'Cổng 18080 đang được tiến trình khác dùng; không thay đổi tiến trình đó.' }
    $env:Path = $taskPhpDirectory+';'+$env:Path
    $taskHttpd = Join-Path $taskApacheDirectory 'bin\httpd.exe'
    & $taskHttpd -t -f $taskConfigPath
    if ($LASTEXITCODE -ne 0) { throw 'Cấu hình Apache chưa hợp lệ.' }
    Start-Process -FilePath $taskHttpd -ArgumentList @('-f',('"'+$taskConfigPath+'"'),'-DFOREGROUND') -WindowStyle Hidden
}
Write-Output 'Demo: http://127.0.0.1:18080/ (đợi vài giây nếu server vừa khởi động).'
Write-Output 'MySQL riêng: 127.0.0.1:33307 / nhahang_demo. Không dùng DB Laragon hiện có.'
