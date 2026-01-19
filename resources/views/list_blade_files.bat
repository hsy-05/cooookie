@echo off

set TARGET_DIR=%~dp0
set OUTPUT_FILE=%~dp0blade-files.txt

if exist "%OUTPUT_FILE%" del "%OUTPUT_FILE%"

for /r "%TARGET_DIR%" %%f in (*.blade.php) do (
    echo %%f >> "%OUTPUT_FILE%"
)

echo Done.
pause
