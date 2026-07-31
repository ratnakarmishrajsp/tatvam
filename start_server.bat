@echo off
title TATVAM Localhost Server
cd /d "%~dp0"
echo ===================================================
echo Starting TATVAM Localhost Server...
echo Address: http://127.0.0.1:8000
echo ===================================================
start "" "http://127.0.0.1:8000/index.html"
"%~dp0php-bin\php.exe" -S 127.0.0.1:8000
pause
