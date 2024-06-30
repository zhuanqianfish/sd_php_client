@echo off

cd  %~dp0

php -S localhost:8000 -t ./www

pause