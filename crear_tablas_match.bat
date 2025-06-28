@echo off
echo Creando tablas de match...
C:\xampp\mysql\bin\mysql.exe -u root -p dogmatch < database/solicitudes_match.sql
echo Tablas creadas exitosamente!
pause 