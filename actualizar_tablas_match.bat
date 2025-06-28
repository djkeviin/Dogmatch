@echo off
echo Actualizando tablas de match...
C:\xampp\mysql\bin\mysql.exe -u root -p dogmatch < database/actualizar_tablas_match.sql
echo Tablas actualizadas exitosamente!
pause 