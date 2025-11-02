# System Kart Szkół Zrozum

System webowy do zarządzania informacjami o szkołach, przeznaczony dla pracowników firmy Zrozum. Aplikacja umożliwia przechowywanie, edycję i eksport danych związanych ze szkołami, wydarzeniami, umowami i zadaniami.

## 🎯 Główne założenia

### Filozofia Frontend > Backend
- **Priorytet**: Przejrzysta i wygodna karta szkoły dla pracowników
- **Kluczowe**: Wszystkie dane w jednym miejscu, łatwo dostępne
- **Dodatkowo**: Możliwość masowej obróbki i eksportu danych

### Kluczowe funkcje
1. **Karta szkoły** - główny widok z wszystkimi informacjami o szkole
2. **Automatyczna synchronizacja** - zmiany zapisywane od razu do bazy danych
3. **Historia zmian** - pełna historia modyfikacji każdego pola
4. **Personalizacja widoku** - możliwość ukrywania/pokazywania sekcji
5. **Eksport danych** - różne rodzaje eksportów do CSV/Excel

## 📋 Wymagania systemowe

- PHP 7.4 lub nowszy
- MySQL 5.7 lub nowszy (lub MariaDB)
- Serwer WWW (Apache/Nginx)
- Przeglądarka internetowa (Chrome, Firefox, Safari, Edge)

## 🚀 Instalacja

### 1. Sklonuj repozytorium
```bash
git clone https://github.com/szymon-plaziak/ZrozumFULL.git
cd ZrozumFULL
```

### 2. Skonfiguruj bazę danych

Edytuj plik `config/database.php` i ustaw odpowiednie dane dostępowe:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'zrozum_schools');
define('DB_USER', 'twoj_uzytkownik');
define('DB_PASS', 'twoje_haslo');
```

### 3. Utwórz bazę danych

Wykonaj skrypt SQL znajdujący się w `config/init.sql`:

```bash
mysql -u root -p < config/init.sql
```

Lub zaimportuj go przez phpMyAdmin/MySQL Workbench.

### 4. Skonfiguruj serwer WWW

#### Apache
Upewnij się, że masz włączony mod_rewrite. Opcjonalnie możesz utworzyć plik `.htaccess` w głównym katalogu:

```apache
RewriteEngine On
RewriteBase /

# Przekieruj do HTTPS (opcjonalne)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Domyślny plik index
DirectoryIndex index.php
```

#### Nginx
Przykładowa konfiguracja:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/ZrozumFULL;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Ustaw uprawnienia

```bash
chmod -R 755 .
chmod -R 777 config  # Tylko jeśli system potrzebuje zapisywać logi
```

### 6. Zaloguj się do systemu

Otwórz przeglądarkę i przejdź do adresu swojej instalacji. Domyślne dane logowania:

- **Login**: admin
- **Hasło**: admin123

⚠️ **WAŻNE**: Zmień hasło administratora po pierwszym logowaniu!

## 📖 Instrukcja użytkowania

### Logowanie i rejestracja
- Pracownicy mogą zakładać własne konta poprzez formularz rejestracji
- Administrator może zarządzać kontami użytkowników
- System obsługuje role: admin, worker, call_center

### Panel główny (Dashboard)
- Przegląd przypisanych szkół
- Lista zadań do wykonania
- Nadchodzące wydarzenia
- Wygasające umowy (w ciągu najbliższych 60 dni)

### Karta szkoły
- **Główny widok** - wszystkie informacje o szkole w jednym miejscu
- **Edycja inline** - podwójne kliknięcie w pole umożliwia edycję (Ctrl+Enter aby zapisać)
- **Historia zmian** - kliknij w pole aby zobaczyć historię modyfikacji
- **Personalizacja** - przycisk "Widok" pozwala wybrać widoczne sekcje
- **Drukowanie** - przycisk "Drukuj" generuje wersję do wydruku (A4 pionowo)

### Sekcje karty szkoły
1. **Podstawowe informacje** - nazwa, adres, typ, liczba uczniów
2. **Kontakt** - telefon, email, strona www
3. **Wydarzenia** - rozpoczęcia roku, zebrania, pokazy
4. **Umowy** - aktywne i historyczne umowy
5. **Zadania** - przypisane zadania do wykonania
6. **Własne sekcje** - można dodawać niestandardowe sekcje (np. procedury, świetlica)

### Wyszukiwanie szkół
- Wyszukiwanie po nazwie, mieście, kodzie pocztowym
- Filtrowanie po mieście
- Lista wszystkich przypisanych szkół

### Wydarzenia
- Przeglądanie wydarzeń w wybranym okresie
- Filtrowanie po typie (rok szkolny, zebranie, pokaz)
- Link do karty szkoły

### Zadania
- Lista zadań przypisanych do zalogowanego użytkownika
- Filtrowanie: oczekujące, zaległe, ukończone
- Oznaczanie jako wykonane

### Eksport danych
System umożliwia eksport następujących danych do plików CSV:

1. **Lista szkół** - wszystkie szkoły z podstawowymi danymi
2. **Rozpoczęcia roku szkolnego** - daty i godziny rozpoczęć
3. **Lista zebrań** - wszystkie zaplanowane zebrania
4. **Lista pokazowych** - zajęcia pokazowe
5. **Dni wolne** - lista świąt i dni wolnych
6. **Wygasające umowy** - umowy kończące się w najbliższym czasie

Pliki CSV można otworzyć w programie Excel, Google Sheets lub innym arkuszu kalkulacyjnym.

## 🔒 Bezpieczeństwo

- Hasła są hashowane przy użyciu PHP password_hash()
- Sesje zabezpieczone (httponly cookies)
- Wszystkie zapytania SQL używają prepared statements (ochrona przed SQL injection)
- Walidacja i sanityzacja danych wejściowych
- CSRF protection zalecany dla produkcji

### Zalecenia dla produkcji
1. Włącz HTTPS (SSL/TLS)
2. Zmień domyślne hasło administratora
3. Ustaw `session.cookie_secure = 1` w `config/session.php` (wymaga HTTPS)
4. Regularnie aktualizuj PHP i MySQL
5. Ogranicz dostęp do katalogu `config/`
6. Włącz backup bazy danych

## 🗄️ Struktura bazy danych

### Główne tabele
- **users** - użytkownicy systemu
- **schools** - podstawowe dane szkół
- **school_details** - szczegółowe informacje o szkołach (elastyczna struktura klucz-wartość)
- **school_assignments** - przypisania szkół do pracowników
- **events** - wydarzenia (zebrania, pokazy, rozpoczęcia roku)
- **contracts** - umowy ze szkołami
- **tasks** - zadania dla pracowników
- **change_history** - historia zmian wszystkich pól
- **user_preferences** - preferencje użytkowników (widoczne sekcje)
- **holidays** - dni wolne od zajęć

## 📁 Struktura projektu

```
ZrozumFULL/
├── config/
│   ├── database.php       # Konfiguracja bazy danych
│   ├── session.php        # Zarządzanie sesją
│   └── init.sql          # Skrypt inicjalizacji bazy danych
├── api/
│   ├── school_detail.php  # API dla szczegółów szkoły
│   ├── change_history.php # API historii zmian
│   └── preferences.php    # API preferencji użytkownika
├── includes/
│   └── header.php        # Wspólny nagłówek
├── public/
│   ├── css/
│   │   └── style.css     # Główne style
│   └── js/
│       ├── main.js       # Główny JavaScript
│       └── school_card.js # JS dla karty szkoły
├── index.php             # Strona główna (przekierowanie)
├── login.php             # Logowanie
├── register.php          # Rejestracja
├── logout.php            # Wylogowanie
├── dashboard.php         # Panel główny
├── schools.php           # Lista szkół
├── school_card.php       # Karta szkoły
├── events.php            # Wydarzenia
├── tasks.php             # Zadania
├── export.php            # Eksport danych
└── README.md             # Ten plik
```

## 🔄 Import danych z Excel

System wspiera import danych z plików Excel (w przygotowaniu):
- Automatyczne rozpoznawanie kolumn
- Mapowanie danych do pól systemu
- Podgląd przed importem
- Walidacja danych

## 🎨 Personalizacja

### Dodawanie własnych sekcji do karty szkoły

Możesz dodać własne sekcje poprzez wstawienie rekordów do tabeli `school_details`:

```sql
INSERT INTO school_details (school_id, section, field_name, field_value, updated_by)
VALUES (1, 'procedures', 'Procedury odbioru ze świetlicy', 'Opis procedur...', 1);
```

### Zmiana kolorów i stylów

Edytuj plik `public/css/style.css`, zmienne CSS w sekcji `:root`:

```css
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    /* ... */
}
```

## 🐛 Rozwiązywanie problemów

### Nie mogę się zalogować
- Sprawdź czy baza danych została poprawnie zainicjalizowana
- Upewnij się, że domyślny użytkownik admin został utworzony
- Sprawdź logi PHP w poszukiwaniu błędów

### Błąd połączenia z bazą danych
- Sprawdź dane dostępowe w `config/database.php`
- Upewnij się, że serwer MySQL działa
- Zweryfikuj, czy użytkownik ma uprawnienia do bazy danych

### Sesja wygasa zbyt szybko
- Zwiększ `session.gc_maxlifetime` w php.ini
- Sprawdź ustawienia `session.cookie_lifetime`

### Problemy z uprawnieniami do plików
```bash
# Ustaw odpowiednie uprawnienia
sudo chown -R www-data:www-data /path/to/ZrozumFULL
sudo chmod -R 755 /path/to/ZrozumFULL
```

## 🔮 Planowane funkcje

- [ ] Import danych z plików Excel/CSV
- [ ] Eksport kart szkół do PDF
- [ ] System powiadomień email
- [ ] Kalendarz zintegrowany z Google Calendar
- [ ] Zaawansowane raporty i statystyki
- [ ] Aplikacja mobilna
- [ ] Integracja z Zrozum Wiki (wspólne logowanie)
- [ ] API REST dla integracji z innymi systemami
- [ ] Automatyczne przypomnienia o zadaniach i wydarzeniach
- [ ] Wersje językowe (EN, DE)

## 🤝 Wkład w projekt

Zapraszamy do współpracy! Jeśli chcesz dodać nowe funkcje lub poprawić istniejące:

1. Utwórz fork repozytorium
2. Stwórz branch dla swojej funkcji (`git checkout -b feature/AmazingFeature`)
3. Commituj zmiany (`git commit -m 'Add some AmazingFeature'`)
4. Push do brancha (`git push origin feature/AmazingFeature`)
5. Otwórz Pull Request

## 📄 Licencja

Ten projekt jest własnością prywatną firmy Zrozum.

## 📞 Kontakt

Pytania i wsparcie techniczne:
- Email: support@zrozum.pl
- GitHub: https://github.com/szymon-plaziak/ZrozumFULL

## 📝 Historia zmian

### Wersja 1.0.0 (2024)
- Pierwsza wersja systemu
- Podstawowa funkcjonalność kart szkół
- System logowania i rejestracji
- Historia zmian
- Eksport do CSV
- Zarządzanie wydarzeniami, umowami i zadaniami

---

**Dziękujemy za używanie Systemu Kart Szkół Zrozum!** 🎉
