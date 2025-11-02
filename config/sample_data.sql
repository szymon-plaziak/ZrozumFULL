-- Sample data for testing the Zrozum School Management System
-- Run this AFTER running init.sql

USE zrozum_schools;

-- Add sample users (password for all: test123)
INSERT INTO users (username, email, password_hash, role) VALUES
('jan.kowalski', 'jan.kowalski@zrozum.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker'),
('anna.nowak', 'anna.nowak@zrozum.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker'),
('piotr.wisniewski', 'piotr.wisniewski@zrozum.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'call_center');

-- Add sample schools
INSERT INTO schools (name, city, address, postal_code, phone, email, website, school_type, student_count, created_by) VALUES
('Szkoła Podstawowa nr 1 im. Jana Pawła II', 'Warszawa', 'ul. Marszałkowska 123', '00-001', '22 123 45 67', 'sp1@warszawa.edu.pl', 'http://sp1-warszawa.pl', 'Szkoła Podstawowa', 450, 1),
('Liceum Ogólnokształcące nr 5', 'Kraków', 'ul. Floriańska 45', '31-019', '12 345 67 89', 'lo5@krakow.edu.pl', 'http://lo5-krakow.pl', 'Liceum', 600, 1),
('Gimnazjum nr 3', 'Poznań', 'ul. Święty Marcin 67', '61-808', '61 234 56 78', 'gim3@poznan.edu.pl', 'http://gim3-poznan.pl', 'Gimnazjum', 320, 1),
('Szkoła Podstawowa "Słoneczko"', 'Wrocław', 'ul. Świdnicka 89', '50-067', '71 345 67 89', 'sp-sloneczko@wroclaw.edu.pl', 'http://sp-sloneczko.pl', 'Szkoła Podstawowa', 280, 1),
('Zespół Szkół nr 2', 'Gdańsk', 'ul. Długa 112', '80-831', '58 456 78 90', 'zs2@gdansk.edu.pl', 'http://zs2-gdansk.pl', 'Zespół Szkół', 520, 1);

-- Assign schools to workers
INSERT INTO school_assignments (school_id, user_id) VALUES
(1, 2), -- SP nr 1 -> Jan Kowalski
(2, 2), -- LO nr 5 -> Jan Kowalski
(3, 3), -- Gimnazjum nr 3 -> Anna Nowak
(4, 3), -- SP Słoneczko -> Anna Nowak
(5, 4); -- Zespół Szkół -> Piotr Wiśniewski

-- Add school details (custom fields)
INSERT INTO school_details (school_id, section, field_name, field_value, display_order, updated_by) VALUES
-- Szkoła Podstawowa nr 1
(1, 'procedures', 'Procedury odbioru ze świetlicy', 'Rodzice odbierają dzieci ze świetlicy po okazaniu dowodu osobistego. W przypadku odbioru przez upoważnioną osobę, wymagane jest pisemne upoważnienie.', 1, 1),
(1, 'procedures', 'Usprawiedliwianie nieobecności', 'Nieobecność należy usprawiedliwić w ciągu 7 dni od powrotu ucznia do szkoły. Akceptowane są zwolnienia lekarskie oraz pisemne usprawiedliwienia od rodziców.', 2, 1),
(1, 'schedule', 'Godziny lekcji', 'Lekcje rozpoczynają się o 8:00. Przerwy: 10 min między lekcjami, 20 min przerwa śniadaniowa po 2. lekcji.', 1, 1),
(1, 'afterschool', 'Świetlica', 'Świetlica czynna 7:00-17:00. Opiekę sprawują wykwalifikowani wychowawcy. Zajęcia plastyczne, sportowe i edukacyjne.', 1, 1),

-- Liceum Ogólnokształcące nr 5
(2, 'procedures', 'System oceniania', 'Stosujemy sześciostopniową skalę ocen. Szczegółowe kryteria dostępne w statucie szkoły.', 1, 1),
(2, 'schedule', 'Godziny lekcji', 'Lekcje: 8:00-15:30. Opcjonalne zajęcia dodatkowe do godz. 17:00.', 1, 1);

-- Add events
INSERT INTO events (school_id, event_type, title, description, event_date, event_time, created_by) VALUES
-- School year starts
(1, 'school_year_start', 'Rozpoczęcie roku szkolnego 2024/2025', 'Uroczysta akademia z okazji rozpoczęcia roku szkolnego', '2024-09-02', '09:00:00', 1),
(2, 'school_year_start', 'Początek roku szkolnego', 'Apel inauguracyjny', '2024-09-02', '10:00:00', 1),
(3, 'school_year_start', 'Rozpoczęcie roku szkolnego', NULL, '2024-09-02', '08:30:00', 1),

-- Meetings
(1, 'meeting', 'Zebranie z rodzicami klas 1-3', 'Omówienie programu nauczania i organizacji roku', '2024-09-15', '17:00:00', 1),
(1, 'meeting', 'Rada Pedagogiczna', 'Comiesięczne posiedzenie rady', '2024-10-05', '14:00:00', 1),
(2, 'meeting', 'Spotkanie informacyjne dla maturzystów', 'Informacje o maturze 2025', '2024-09-20', '16:00:00', 1),

-- Demos
(1, 'demo', 'Dzień Otwarty', 'Prezentacja szkoły dla kandydatów', '2024-11-15', '10:00:00', 1),
(3, 'demo', 'Lekcja pokazowa z fizyki', 'Eksperymenty dla uczniów SP', '2024-10-25', '11:00:00', 1);

-- Add contracts
INSERT INTO contracts (school_id, contract_number, start_date, end_date, status, notes, created_by) VALUES
(1, 'ZR/2024/001', '2024-01-01', '2024-12-31', 'active', 'Umowa na zajęcia pozalekcyjne', 1),
(2, 'ZR/2024/002', '2024-01-01', '2024-12-31', 'active', 'Umowa na wsparcie dydaktyczne', 1),
(3, 'ZR/2023/045', '2023-09-01', '2024-06-30', 'active', 'Wygasa pod koniec roku szkolnego', 1),
(4, 'ZR/2024/003', '2024-02-01', '2025-01-31', 'active', 'Roczna umowa', 1);

-- Add tasks
INSERT INTO tasks (school_id, assigned_to, task_type, title, description, due_date, status, created_by) VALUES
(1, 2, 'data_update', 'Aktualizacja danych kontaktowych', 'Sprawdzić i zaktualizować numery telefonów i adresy email', '2024-11-15', 'pending', 1),
(1, 2, 'meeting_prep', 'Przygotowanie zebrania', 'Przygotować materiały na zebranie z rodzicami', '2024-09-12', 'pending', 1),
(2, 2, 'contract_renewal', 'Odnowienie umowy', 'Skontaktować się w sprawie przedłużenia umowy', '2024-12-01', 'pending', 1),
(3, 3, 'data_update', 'Weryfikacja liczby uczniów', 'Zweryfikować aktualną liczbę uczniów', '2024-10-31', 'pending', 1),
(4, 3, 'follow_up', 'Follow-up po pokazie', 'Zebrać opinie po dniu otwartym', '2024-11-20', 'pending', 1);

-- Add holidays
INSERT INTO holidays (school_id, holiday_name, start_date, end_date, description) VALUES
(NULL, 'Ferie zimowe 2025', '2025-01-20', '2025-02-02', 'Ferie zimowe - termin ogólnopolski'),
(NULL, 'Ferie letnie 2025', '2025-06-28', '2025-08-31', 'Wakacje letnie'),
(NULL, 'Święta Bożego Narodzenia', '2024-12-24', '2025-01-02', 'Przerwa świąteczna'),
(1, 'Dzień Patrona Szkoły', '2024-10-16', '2024-10-16', 'Dzień wolny z okazji święta patrona');

-- Add some change history (simulating past edits)
INSERT INTO change_history (table_name, record_id, field_name, old_value, new_value, changed_by, changed_at) VALUES
('school_details', 1, 'Procedury odbioru ze świetlicy', 'Rodzice odbierają dzieci ze świetlicy.', 'Rodzice odbierają dzieci ze świetlicy po okazaniu dowodu osobistego. W przypadku odbioru przez upoważnioną osobę, wymagane jest pisemne upoważnienie.', 1, '2024-08-15 10:30:00'),
('school_details', 3, 'Godziny lekcji', 'Lekcje 8:00-14:00', 'Lekcje rozpoczynają się o 8:00. Przerwy: 10 min między lekcjami, 20 min przerwa śniadaniowa po 2. lekcji.', 1, '2024-08-20 14:15:00');

-- Add user preferences (visible sections)
INSERT INTO user_preferences (user_id, preference_key, preference_value) VALUES
(2, 'school_card_visible_sections', '["basic_info","contact","schedule","events","contracts","tasks","procedures"]'),
(3, 'school_card_visible_sections', '["basic_info","contact","events","tasks"]');

-- Summary
SELECT 'Sample data loaded successfully!' as Status;
SELECT COUNT(*) as 'Total Users' FROM users;
SELECT COUNT(*) as 'Total Schools' FROM schools;
SELECT COUNT(*) as 'Total Events' FROM events;
SELECT COUNT(*) as 'Total Tasks' FROM tasks;
SELECT COUNT(*) as 'Total Contracts' FROM contracts;
