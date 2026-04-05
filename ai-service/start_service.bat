@echo off
cd /d "%~dp0"
if not exist ".venv\Scripts\uvicorn.exe" (
  echo Run: python -m venv .venv ^& .venv\Scripts\pip install -r requirements.txt
  exit /b 1
)
.venv\Scripts\uvicorn.exe main:app --host 127.0.0.1 --port 8001
