@echo off
echo ========================================
echo   MS Lettres - Demarrage du Serveur
echo ========================================
echo.

REM Verifier si PHP est disponible
php --version >nul 2>&1
if errorlevel 1 (
    echo [ERREUR] PHP n'est pas installe ou pas dans le PATH
    echo.
    echo Solutions:
    echo 1. Installez PHP 8.2 ou superieur
    echo 2. Ajoutez PHP au PATH systeme
    echo 3. Utilisez le chemin complet: C:\xampp\php\php.exe
    echo.
    pause
    exit /b 1
)

echo [OK] PHP detecte
php --version
echo.

REM Verifier si composer est disponible
composer --version >nul 2>&1
if errorlevel 1 (
    echo [ATTENTION] Composer n'est pas installe
    echo Certaines fonctionnalites peuvent ne pas fonctionner
    echo.
) else (
    echo [OK] Composer detecte
)
echo.

REM Verifier la connexion MySQL
echo Verification de la connexion MySQL...
php bin/console doctrine:schema:validate >nul 2>&1
if errorlevel 1 (
    echo [ATTENTION] Probleme de connexion a la base de donnees
    echo.
    echo Verifications:
    echo 1. MySQL est-il demarre? (XAMPP/WAMP/MAMP)
    echo 2. La base 'ms_db' existe-t-elle?
    echo 3. Le fichier .env est-il configure correctement?
    echo.
    echo Voulez-vous continuer quand meme? (O/N)
    set /p continue=
    if /i not "%continue%"=="O" exit /b 1
) else (
    echo [OK] Connexion MySQL etablie
)
echo.

REM Vider le cache
echo Nettoyage du cache...
php bin/console cache:clear >nul 2>&1
if errorlevel 1 (
    echo [ATTENTION] Impossible de vider le cache
) else (
    echo [OK] Cache vide
)
echo.

REM Demarrer le serveur
echo ========================================
echo   Demarrage du serveur sur localhost:8000
echo ========================================
echo.
echo URLs d'acces:
echo   - Site Client: http://localhost:8000
echo   - Admin:        http://localhost:8000/admin
echo   - Panier:       http://localhost:8000/panier
echo.
echo Identifiants admin:
echo   - Email:        admin@example.com
echo   - Mot de passe: admin123
echo.
echo Appuyez sur Ctrl+C pour arreter le serveur
echo ========================================
echo.

php -S localhost:8000 -t public

pause
