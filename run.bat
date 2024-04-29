@echo off

cd \t %~dp0

php -S localhost:8000 -t ./www

pause