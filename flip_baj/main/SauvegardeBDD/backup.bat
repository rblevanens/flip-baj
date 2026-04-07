
@echo off
setlocal enabledelayedexpansion

set USER=root
set PASSWORD=""
set HOST=localhost
set DATABASE=baj

:loop
rem Définir le nom du fichier de sauvegarde avec horodatage
for /f "tokens=1-4 delims=/ " %%a in ('date /t') do (
    set day=%%a
    set month=%%b
    set year=%%c
)
for /f "tokens=1-3 delims=: " %%a in ('time /t') do (
    set hour=%%a
    set minute=%%b
    set second=00
)

rem Remplacer les espaces et les caractères spéciaux dans les variables
set day=%day: =0%
set month=%month: =0%
set hour=%hour: =0%
set minute=%minute: =0%

rem Chemin complet vers mysqldump
set MYSQLDUMP="C:\xampp\mysql\bin\mysqldump.exe"

rem Définir le répertoire de sauvegarde
set BACKUP_DIR="C:\xampp\htdocs\FlipBAJ\flip_baj\main\SauvegardeBDD\bdd\BDD_%DATABASE%-%year%-%month%-%day%"

rem Définir le répertoire de sauvegarde sur la clé USB
set USB_DIR="E:\BddSave"

rem Définir le répertoire de sauvegarde du onedrive 
rem set ONEDRIVE_DIR = " "

rem Créer le répertoire de sauvegarde s'il n'existe pas
if not exist "%BACKUP_DIR%" (
    mkdir "%BACKUP_DIR%"
)

set BACKUP_FILE=%BACKUP_DIR%\sauvegarde_%DATABASE%_%year%%month%%day%_%hour%%minute%%second%.sql

rem Exécuter la commande de sauvegarde avec des options pour ignorer les problèmes de verrouillage et de routines
"%MYSQLDUMP%" -u %USER% --password=%PASSWORD% -h %HOST% %DATABASE% --skip-lock-tables --single-transaction --routines --events --triggers > "%BACKUP_FILE%" 2> "%BACKUP_DIR%\error.log"

rem Vérification de la réussite de la sauvegarde
rem Copie le fichier dans une clef usb
if %ERRORLEVEL% EQU 0 (
    echo Sauvegarde réussie : %BACKUP_FILE%
    copy "%BACKUP_FILE%" "%USB_DIR%"

    if %ERRORLEVEL% EQU 0 (
        echo Copie réussie sur la clé USB : %USB_DIR%
    ) else (
        echo Erreur lors de la copie sur la clé USB ou sur DROPBOX
    )
) else (
    echo Erreur lors de la sauvegarde, voir error.log pour plus de détails
)


rem Attendre 300 secondes (5 minutes) avant la prochaine sauvegarde
timeout /t 300

rem Revenir au début de la boucle
goto loop