@echo off
echo ========================================
echo   MS Lettres - Reparation du Panier
echo ========================================
echo.

REM Verifier si PHP est disponible
php --version >nul 2>&1
if errorlevel 1 (
    echo [ERREUR] PHP n'est pas installe ou pas dans le PATH
    pause
    exit /b 1
)

echo Etape 1/4: Synchronisation des metadonnees...
php bin/console doctrine:migrations:sync-metadata-storage
if errorlevel 1 (
    echo [ATTENTION] Erreur lors de la synchronisation
) else (
    echo [OK] Metadonnees synchronisees
)
echo.

echo Etape 2/4: Mise a jour du schema de la base de donnees...
php bin/console doctrine:schema:update --force
if errorlevel 1 (
    echo [ERREUR] Impossible de mettre a jour le schema
    echo Verifiez que MySQL est demarre et que la base ms_db existe
    pause
    exit /b 1
) else (
    echo [OK] Schema mis a jour
)
echo.

echo Etape 3/4: Nettoyage du cache...
php bin/console cache:clear
if errorlevel 1 (
    echo [ATTENTION] Erreur lors du nettoyage du cache
) else (
    echo [OK] Cache nettoye
)
echo.

echo Etape 4/4: Validation du schema...
php bin/console doctrine:schema:validate
echo.

echo ========================================
echo   Reparation terminee!
echo ========================================
echo.
echo Le panier devrait maintenant fonctionner correctement.
echo.
echo Pour demarrer le serveur, executez: start-server.bat
echo.
pause
