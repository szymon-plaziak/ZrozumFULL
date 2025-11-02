# Quick Start Guide - System Kart Szkół Zrozum

Szybki start w 5 minut! ⚡

## Wymagania

- PHP 7.4+
- MySQL 5.7+
- Serwer WWW (Apache/Nginx) LUB XAMPP/MAMP/WAMP

## Instalacja w 5 krokach

### 1. Pobierz kod 📥

```bash
git clone https://github.com/szymon-plaziak/ZrozumFULL.git
cd ZrozumFULL
```

### 2. Skonfiguruj bazę danych 🗄️

Otwórz phpMyAdmin (http://localhost/phpmyadmin) i:

**Krok A:** Kliknij zakładkę "SQL"

**Krok B:** Skopiuj i wklej całą zawartość pliku `config/init.sql`

**Krok C:** Kliknij "Wykonaj"

**OPCJONALNIE:** Wczytaj przykładowe dane testowe:
- Skopiuj zawartość pliku `config/sample_data.sql`
- Wklej w zakładce "SQL"
- Kliknij "Wykonaj"

### 3. Skonfiguruj połączenie 🔌

Edytuj plik `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'zrozum_schools');
define('DB_USER', 'root');          // Twój użytkownik MySQL
define('DB_PASS', '');               // Twoje hasło MySQL
```

💡 **Wskazówka**: W XAMPP hasło jest puste, w MAMP to `root`

### 4. Otwórz w przeglądarce 🌐

Przejdź do:
- XAMPP: `http://localhost/ZrozumFULL`
- MAMP: `http://localhost:8888/ZrozumFULL`
- Własny serwer: `http://localhost/twoja-sciezka`

### 5. Zaloguj się 🔐

**Domyślne konto administratora:**
- Login: `admin`
- Hasło: `admin123`

**⚠️ WAŻNE:** Zmień hasło zaraz po pierwszym logowaniu!

## Co dalej?

### Zmień hasło administratora
1. Zaloguj się jako admin
2. Kliknij swoją nazwę w prawym górnym rogu
3. Wybierz "Ustawienia"
4. Zmień hasło

### Załóż konto pracownika
1. Wyloguj się
2. Kliknij "Zarejestruj się"
3. Wypełnij formularz
4. Zaloguj się na nowe konto

### Dodaj pierwszą szkołę (jako admin)

**Metoda A - przez SQL:**
```sql
-- W phpMyAdmin zakładka SQL
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

**Metoda B - użyj przykładowych danych:**
Jeśli załadowałeś `config/sample_data.sql`, masz już 5 przykładowych szkół! 🎉

### Przypisz szkołę do pracownika

```sql
-- Sprawdź ID szkoły i użytkownika
SELECT id, name FROM schools;
SELECT id, username FROM users;

-- Przypisz (zamień 1 i 2 na właściwe ID)
INSERT INTO school_assignments (school_id, user_id) 
VALUES (1, 2);
```

## Struktura projektu

```
ZrozumFULL/
├── 📱 Frontend
│   ├── login.php           # Logowanie
│   ├── register.php        # Rejestracja
│   ├── dashboard.php       # Panel główny
│   ├── schools.php         # Lista szkół
│   ├── school_card.php     # Karta szkoły (★ główna funkcja!)
│   ├── events.php          # Wydarzenia
│   ├── tasks.php           # Zadania
│   └── export.php          # Eksport danych
│
├── 🔧 Backend
│   ├── api/                # API endpoints
│   └── config/             # Konfiguracja i SQL
│
├── 🎨 Assets
│   └── public/
│       ├── css/           # Style CSS
│       └── js/            # JavaScript
│
└── 📚 Dokumentacja
    ├── README.md          # Pełna dokumentacja
    ├── INSTALL.md         # Szczegółowa instalacja
    ├── API.md             # Dokumentacja API
    └── QUICKSTART.md      # Ten plik
```

## Główne funkcje systemu

### 🏫 Karta Szkoły (Priorytet #1)
- Wszystkie dane w jednym miejscu
- **Edycja inline**: Kliknij 2x w pole aby edytować
- **Historia zmian**: Kliknij 1x w pole aby zobaczyć historię
- **Personalizacja**: Przycisk "Widok" - wybierz widoczne sekcje
- **Drukowanie**: Przycisk "Drukuj" - wersja na papier (A4)

### 📊 Panel Główny
- Statystyki: szkoły, zadania, wydarzenia
- Wygasające umowy (60 dni)
- Szybki dostęp do szkół

### 🔍 Szukaj Szkół
- Wyszukiwanie po nazwie, mieście
- Filtrowanie po mieście
- Sortowanie

### 📅 Wydarzenia
- Rozpoczęcia roku szkolnego
- Zebrania
- Pokazy
- Filtrowanie po dacie i typie

### ✅ Zadania
- Lista zadań przypisanych do Ciebie
- Filtry: oczekujące, zaległe, ukończone
- Oznaczanie jako wykonane

### 📤 Eksport
- Lista szkół → CSV
- Rozpoczęcia roku → CSV
- Zebrania → CSV
- Pokazy → CSV
- Dni wolne → CSV
- Wygasające umowy → CSV

## Przykładowe dane testowe

Jeśli załadowałeś `config/sample_data.sql`, masz:

**👥 Użytkownicy** (hasło dla wszystkich: `test123`):
- jan.kowalski (worker)
- anna.nowak (worker)
- piotr.wisniewski (call_center)

**🏫 Szkoły**:
1. SP nr 1 - Warszawa
2. LO nr 5 - Kraków
3. Gimnazjum nr 3 - Poznań
4. SP "Słoneczko" - Wrocław
5. Zespół Szkół nr 2 - Gdańsk

**📅 Wydarzenia**: 8 wydarzeń (rozpoczęcia, zebrania, pokazy)

**📋 Zadania**: 5 przykładowych zadań

**📄 Umowy**: 4 umowy (niektóre wkrótce wygasają)

## Najczęstsze problemy

### ❌ Błąd: "Database connection failed"
```bash
# Sprawdź czy MySQL działa
# W XAMPP: Control Panel → sprawdź status MySQL
# Sprawdź config/database.php - czy dane są poprawne
```

### ❌ Nie mogę się zalogować
```bash
# Sprawdź czy użytkownik admin istnieje:
# W phpMyAdmin:
SELECT * FROM users WHERE username = 'admin';
```

### ❌ Pusty ekran lub błędy PHP
```bash
# Włącz wyświetlanie błędów w php.ini:
display_errors = On
error_reporting = E_ALL
```

## Wsparcie

📧 **Email**: support@zrozum.pl
🐙 **GitHub**: https://github.com/szymon-plaziak/ZrozumFULL
📖 **Pełna dokumentacja**: Zobacz [README.md](README.md)
🔧 **Szczegółowa instalacja**: Zobacz [INSTALL.md](INSTALL.md)

## Następne kroki

1. ✅ Zainstaluj system
2. ✅ Zaloguj się i zmień hasło
3. ✅ Załóż konto pracownika
4. ✅ Dodaj szkoły
5. ✅ Przypisz szkoły do pracowników
6. 📖 Przeczytaj [README.md](README.md) - pełne możliwości systemu
7. 🚀 Zacznij używać!

---

**Powodzenia!** 🎉

System został zaprojektowany z myślą o prostocie i efektywności.
Jeśli masz pytania, zajrzyj do dokumentacji lub skontaktuj się z nami.
