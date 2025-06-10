@echo off
C:\xampp\mysql\bin\mysql -u root -h localhost dogmatch < config/schema.sql
C:\xampp\mysql\bin\mysql -u root -h localhost dogmatch < config/datos_prueba.sql 