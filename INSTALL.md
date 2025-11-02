# Instrukcja instalacji - System Kart Szkół Zrozum

## Krok po kroku - instalacja od podstaw

### 1. Wymagania wstępne

Upewnij się, że masz zainstalowane:
- **Serwer WWW**: Apache 2.4+ lub Nginx 1.18+
- **PHP**: 7.4 lub nowszy (zalecane 8.0+)
- **MySQL**: 5.7+ lub MariaDB 10.3+
- **Opcjonalnie**: phpMyAdmin dla łatwiejszego zarządzania bazą danych

#### Sprawdzenie wersji PHP
```bash
php -v
```

#### Sprawdzenie zainstalowanych rozszerzeń PHP
```bash
php -m | grep -E 'pdo|mysqli|mbstring|json'
```

Potrzebne rozszerzenia:
- `pdo_mysql`
- `mysqli`
- `mbstring`
- `json`

### 2. Instalacja w środowisku lokalnym (XAMPP/WAMP/MAMP)

#### Windows - XAMPP
1. Pobierz i zainstaluj [XAMPP](https://www.apachefriends.org/)
2. Uruchom XAMPP Control Panel
3. Wystartuj Apache i MySQL
4. Sklonuj repozytorium do `C:\xampp\htdocs\zrozum`:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/szymon-plaziak/ZrozumFULL.git zrozum
   ```

#### macOS - MAMP
1. Pobierz i zainstaluj [MAMP](https://www.mamp.info/)
2. Uruchom MAMP
3. Sklonuj repozytorium do `/Applications/MAMP/htdocs/zrozum`:
   ```bash
   cd /Applications/MAMP/htdocs
   git clone https://github.com/szymon-plaziak/ZrozumFULL.git zrozum
   ```

#### Linux - natywna instalacja
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 php php-mysql php-mbstring mysql-server git

# Sklonuj repozytorium
cd /var/www/html
sudo git clone https://github.com/szymon-plaziak/ZrozumFULL.git zrozum
sudo chown -R www-data:www-data zrozum
```

### 3. Konfiguracja bazy danych

#### Metoda A: Przez phpMyAdmin (najłatwiejsza)

1. Otwórz phpMyAdmin w przeglądarce:
   - XAMPP: http://localhost/phpmyadmin
   - MAMP: http://localhost:8888/phpMyAdmin
   - Linux: http://localhost/phpmyadmin

2. Kliknij zakładkę "SQL"

3. Skopiuj i wklej całą zawartość pliku `config/init.sql`

4. Kliknij "Wykonaj"

#### Metoda B: Przez wiersz poleceń

```bash
# Windows (XAMPP)
cd C:\xampp\mysql\bin
mysql -u root -p

# macOS (MAMP)
/Applications/MAMP/Library/bin/mysql -u root -p

# Linux
mysql -u root -p

# Następnie wykonaj:
source /ścieżka/do/zrozum/config/init.sql;
# LUB
# Importuj plik bezpośrednio:
mysql -u root -p < /ścieżka/do/zrozum/config/init.sql
```

#### Metoda C: Import przez MySQL Workbench

1. Otwórz MySQL Workbench
2. Połącz się z lokalnym serwerem MySQL
3. Wybierz: Server → Data Import
4. Wybierz "Import from Self-Contained File"
5. Wskaż plik `config/init.sql`
6. Kliknij "Start Import"

### 4. Konfiguracja połączenia z bazą danych

Edytuj plik `config/database.php`:

```php
<?php
define('DB_HOST', 'localhost');      // Adres serwera bazy danych
define('DB_NAME', 'zrozum_schools'); // Nazwa bazy danych
define('DB_USER', 'root');           // Użytkownik MySQL
define('DB_PASS', '');               // Hasło MySQL (puste dla XAMPP/MAMP)
define('DB_CHARSET', 'utf8mb4');
```

**Uwaga**: 
- W XAMPP domyślnie hasło dla root jest puste
- W MAMP domyślne hasło to `root`
- W środowisku produkcyjnym **zawsze** ustaw silne hasło!

### 5. Sprawdzenie instalacji

1. Otwórz przeglądarkę
2. Przejdź do:
   - XAMPP: http://localhost/zrozum
   - MAMP: http://localhost:8888/zrozum
   - Linux: http://localhost/zrozum

3. Powinno przekierować Cię do strony logowania

### 6. Pierwsze logowanie

Użyj domyślnego konta administratora:
- **Login**: `admin`
- **Hasło**: `admin123`

⚠️ **WAŻNE**: Natychmiast zmień hasło po pierwszym logowaniu!

1. Zaloguj się
2. Kliknij na swoją nazwę użytkownika w prawym górnym rogu
3. Wybierz "Ustawienia"
4. Zmień hasło

### 7. Tworzenie pierwszego użytkownika

1. Wyloguj się z konta admin
2. Kliknij "Zarejestruj się" na stronie logowania
3. Wypełnij formularz:
   - Nazwa użytkownika
   - Email
   - Hasło (min. 6 znaków)
4. Kliknij "Zarejestruj się"
5. Zaloguj się na nowe konto

### 8. Dodawanie pierwszej szkoły (jako admin)

1. Zaloguj się jako admin
2. Przejdź do "Szkoły" → "Dodaj szkołę" (funkcja w przygotowaniu)
3. Lub wykonaj SQL w phpMyAdmin:

```sql
INSERT INTO schools (name, city, address, phone, email, school_type, created_by) 
VALUES (
    'Szkoła Podstawowa nr 1',
    'Warszawa',
    'ul. Przykładowa 123',
    '22 123 45 67',
    'sp1@example.pl',
    'Szkoła Podstawowa',
    1
);
```

### 9. Przypisywanie szkoły do pracownika

```sql
-- Pobierz ID szkoły i użytkownika
SELECT id, name FROM schools;
SELECT id, username FROM users;

-- Przypisz szkołę do użytkownika
INSERT INTO school_assignments (school_id, user_id) 
VALUES (1, 2); -- ID szkoły 1, ID użytkownika 2
```

## Rozwiązywanie problemów

### Problem: Błąd połączenia z bazą danych

**Objaw**: "Database connection failed"

**Rozwiązanie**:
1. Sprawdź czy MySQL działa:
   ```bash
   # Windows (XAMPP)
   Otwórz XAMPP Control Panel i sprawdź status MySQL
   
   # Linux
   sudo systemctl status mysql
   ```

2. Sprawdź dane w `config/database.php`
3. Sprawdź czy baza `zrozum_schools` istnieje:
   ```sql
   SHOW DATABASES;
   ```

### Problem: Strona wyświetla kod PHP zamiast działać

**Objaw**: Widoczny kod źródłowy PHP na stronie

**Rozwiązanie**:
1. Sprawdź czy PHP jest zainstalowane:
   ```bash
   php -v
   ```

2. XAMPP/MAMP: Upewnij się że Apache jest uruchomiony

3. Linux: Sprawdź konfigurację Apache:
   ```bash
   sudo a2enmod php8.0
   sudo systemctl restart apache2
   ```

### Problem: Błąd 404 - strona nie została znaleziona

**Rozwiązanie**:
1. Sprawdź czy pliki są w odpowiednim katalogu
2. Sprawdź uprawnienia do plików:
   ```bash
   # Linux
   sudo chown -R www-data:www-data /var/www/html/zrozum
   sudo chmod -R 755 /var/www/html/zrozum
   ```

### Problem: Sesja wygasa zbyt szybko

**Rozwiązanie**:
Edytuj `php.ini`:
```ini
session.gc_maxlifetime = 3600    ; 1 godzina
session.cookie_lifetime = 0       ; Do zamknięcia przeglądarki
```

Restart serwera po zmianach.

### Problem: Nie mogę się zalogować

**Rozwiązanie**:
1. Sprawdź czy użytkownik admin został utworzony:
   ```sql
   SELECT * FROM users WHERE username = 'admin';
   ```

2. Jeśli nie istnieje, utwórz go:
   ```sql
   -- Hasło: admin123
   INSERT INTO users (username, email, password_hash, role) 
   VALUES (
       'admin', 
       'admin@zrozum.pl', 
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
       'admin'
   );
   ```

## Instalacja na serwerze produkcyjnym

### Wymagania dodatkowe
- Certyfikat SSL/TLS (HTTPS)
- Konfiguracja firewalla
- Backup bazy danych
- Monitoring

### Kroki dodatkowe

1. **Włącz HTTPS**:
   Edytuj `config/session.php`:
   ```php
   ini_set('session.cookie_secure', 1); // Zmień 0 na 1
   ```

2. **Zabezpiecz katalog config**:
   Utwórz `.htaccess` w katalogu `config/`:
   ```apache
   Deny from all
   ```

3. **Ustaw odpowiednie uprawnienia**:
   ```bash
   chown -R www-data:www-data /var/www/html/zrozum
   chmod -R 755 /var/www/html/zrozum
   chmod 644 config/database.php
   ```

4. **Skonfiguruj backup**:
   ```bash
   # Cron job - codziennie o 2:00 w nocy
   0 2 * * * mysqldump -u user -p'password' zrozum_schools > /backup/zrozum_$(date +\%Y\%m\%d).sql
   ```

5. **Monitoring logów**:
   ```bash
   tail -f /var/log/apache2/error.log
   ```

## Aktualizacja systemu

```bash
cd /ścieżka/do/zrozum
git pull origin main
# Jeśli są zmiany w bazie danych, wykonaj migracje SQL
```

## Wsparcie

W razie problemów:
1. Sprawdź logi błędów PHP
2. Sprawdź logi Apache/Nginx
3. Sprawdź logi MySQL
4. Skontaktuj się z administratorem systemu

## Następne kroki

Po zainstalowaniu:
1. Przeczytaj [README.md](README.md) - pełna dokumentacja
2. Zmień hasło administratora
3. Utwórz konta dla pracowników
4. Zaimportuj dane szkół
5. Przypisz szkoły do pracowników
6. Skonfiguruj backup bazy danych

Powodzenia! 🎉
