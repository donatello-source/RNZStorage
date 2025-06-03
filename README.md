# Projekt RNZ Storage – System Zarządzania Magazynem

## Opis projektu

Aplikacja do zarządzania magazynem firmy **Robimy na Żywo**.  
Pozwala na kompleksową obsługę sprzętu, wycen oraz plików związanych z realizacjami eventowymi.

### Główne funkcjonalności

1. **Zarządzanie sprzętem**
   - Dodawanie nowego sprzętu do magazynu
   - Ustalanie ilości, ceny, dodatkowych informacji dla pracowników (np. lokalizacja w magazynie)
   - Informacje do wyceny (wyświetlane w ofertach)
   - Przypisywanie sprzętu do kategorii (wideo, dźwięk, światło)

2. **Wyszukiwanie i filtrowanie sprzętu**
   - Szybkie wyszukiwanie po nazwie
   - Filtrowanie po kategoriach

3. **Tworzenie wyceny**
   - Wybór Zamawiającego z listy
   - Nadawanie nazwy projektu i lokalizacji
   - Wybór dat (pojedynczy dzień lub przedział)
   - Tworzenie tabelek sprzętowych (kategorie), ustalanie ilości, dni, rabatów
   - Automatyczne podliczanie sum tabelki i całej wyceny

4. **Przeglądanie wycen**
   - Lista wycen z najważniejszymi informacjami
   - Wyszukiwanie i filtrowanie
   - Zmiana statusu (przyjęte, odrzucone)
   - Przejście do szczegółowego podglądu

5. **Podgląd i edycja wyceny**
   - Edycja wszystkich pól wyceny
   - Generowanie pliku wyceny (np. Excel)
   - Zapis pliku do wirtualnego drzewa plików

6. **Zarządzanie plikami wycen**
   - Przeglądanie, usuwanie, zmiana nazwy plików
   - Tworzenie folderów i zarządzanie strukturą katalogów

---

## Schemat architektury

```
+-------------------+         +-------------------+         +-------------------+
|    React Frontend | <-----> |     Symfony API   | <-----> |    PostgreSQL     |
| (Vite, MUI, etc.) |         | (REST, RabbitMQ)  |         |   (baza danych)   |
+-------------------+         +-------------------+         +-------------------+
         |                            |
         |                            v
         |                    +-------------------+
         |                    |    RabbitMQ       |
         |                    +-------------------+
         |                            |
         |                            v
         |                    +-------------------+
         |                    |  Worker Symfony   |
         |                    | (generowanie plików,|
         |                    |  obsługa kolejek) |
         |                    +-------------------+
         |                            |
         |                            v
         |                    +-------------------+
         |                    |    uploads/       |
         |                    | (pliki wycen)     |
         |                    +-------------------+
         v
+-------------------+
|      nginx        |
| (reverse proxy)   |
+-------------------+
```

---

## Instrukcja uruchomienia

Projekt jest w pełni zautomatyzowany przy użyciu **Docker Compose**.

### 1. Wymagania:
- Docker  
- Docker Compose

### 2. Uruchomienie projektu:
W głównym katalogu projektu uruchom:
```bash
docker-compose up -d --build
```

### 3. Konfiguracja Symfony:
Po uruchomieniu kontenerów wykonaj następujące kroki:

1. Utwórz plik `.env` w folderze głównym Symfony i dodaj do niego poniższą konfigurację:
   ```env
   APP_ENV=dev
   DATABASE_URL="pgsql://symfony:symfony@db:5432/symfony"
   DATABASE_NAME="symfony"
   DATABASE_USER="symfony"
   DATABASE_PASSWORD="symfony"
   CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
   MESSENGER_TRANSPORT_DSN=amqp://guest:guest@rabbitmq:5672/%2f/messages
   ```

2. Wejdź do kontenera Symfony:
   ```bash
   docker exec -it symfony_php bash
   ```

3. Ustaw odpowiednie uprawnienia:
   ```bash
   chmod -R 777 var
   chmod -R 777 tmp
   ```

4. Wejdź na stronę aplikacji, utwórz konto administratora, połącz się z bazą danych i edytuj uprawnienia tego konta na `ROLE_ADMIN`.  
   Po zalogowaniu na konto administratora możesz dodawać nowych użytkowników i edytować ich role.

5. W kontenerze Symfony uruchom workera:
   ```bash
   php bin/console messenger:consume async
   ```

### 4. Dostęp do aplikacji:
- Frontend: [http://localhost:5173](http://localhost:5173)
- Backend/API: [http://localhost:8080](http://localhost:8080)
- RabbitMQ panel: [http://localhost:15672](http://localhost:15672) (login: guest/guest)

---

## Użyte technologie i uzasadnienie wyboru

- **React + Vite**  
  Nowoczesny framework frontendowy, szybki development, świetna integracja z MUI i React Router. Vite zapewnia błyskawiczne przeładowania i prostą konfigurację.

- **Material UI (MUI)**  
  Gotowe, responsywne komponenty UI, szybkie prototypowanie i spójny wygląd aplikacji.

- **Symfony**  
  Stabilny, rozbudowany framework PHP do budowy API, z doskonałą obsługą Doctrine, kolejek i bezpieczeństwa.

- **Doctrine ORM**  
  Ułatwia pracę z bazą danych, pozwala na szybkie mapowanie encji i migracje.

- **PostgreSQL**  
  Wydajna, stabilna i skalowalna baza danych relacyjnych, dobrze wspierana przez Doctrine.

- **RabbitMQ**  
  Kolejkowanie zadań (np. generowanie plików), pozwala na asynchroniczne operacje i skalowanie.

- **nginx**  
  Szybki reverse proxy, obsługa statycznych plików i przekierowań do backendu.

- **Docker + Docker Compose**  
  Ułatwia uruchamianie i rozwój projektu na każdym systemie, zapewnia powtarzalność środowiska.

- **PhpSpreadsheet**  
  (backend) Generowanie plików Excel (xlsx) bezpośrednio z danych wyceny.

- **Pecl amqp**  
  Integracja PHP z RabbitMQ.

---

**Autor:**  
Mateusz Galski