
### Architektura projektu
Na początku rozważałem podejście w stylu DDD, ale ze względu na czas zdecydowałem się na prostszą strukturę. 
Uważam, że DDD byłoby overkillem dla tego projektu.

### Product
Encja Product zawiera część logiki biznesowej, najważniejszą decyzją było rozdzielenie funkcji updateDetails i updatePrice.
Dlatego, że aktualizacja ceny musi zapisywać historię ceny.  Oddzielamy te dwie funkcje, aby zachować czytelność logiki i tworzyć historię cen tylko wtedy gdy ta się zmienia

Param price jest stringiem, ponieważ float może być problematyczny w przypadku precyzyjnych wartości pieniężnych,
a string pozwala na dokładne przechowywanie wartości bez ryzyka utraty precyzji.

### Optimistic locking

Product posiada pole ```version``` które jest używane do optimistic locking.
Jeśli dwie osoby próbują zmienić produkt w tym samym czasie:
- pierwsza operacja się zapisze,
- druga dostanie błąd


### Soft delete
Produkty nie są fizycznie usuwane z bazy.
Zamiast tego aktualizujemy pole:
````deletedAt````

Dzięki temu:
- nie gubimy historii danych,
- nie ma ryzyka przypadkowego usunięcia
- rekord nadal istnieje w bazie, ale jest traktowany jako usunięty

### Testy

Testy pokrywają główne scenariusze API:
- tworzenie produktu
- konflikt SKU
- pobieranie produktu
- zmiana ceny i zapis historii
- optimistic locking
- soft delete


## Możliwe usprawnienia

 ### Lepsza obsługa wyjątków

Obecnie część błędów zwracana jest na podstawie wyjątków ogólnych, np. DomainException.
W większym projekcie lepiej byłoby użyć bardziej precyzyjnych wyjątków, np.:

- ProductNotFoundException
- SkuAlreadyExistsException
- StaleProductVersionException
  Statyczna analiza

### Do projektu warto byłoby dodać statyczną analizę, np.:

- PHPStan
- Psalm

### Standaryzacja stylu kodu

Do utrzymania spójnego formatowania kodu dołożyłbym narzędzia typu:
- PHP CS Fixer
- ECS / PHPCS
- 

### Pipeline CI

Naturalnym kolejnym krokiem byłoby dodanie pipeline'u, np. w GitHub Actions.

Taki pipeline mógłby automatycznie uruchamiać:

- testy
- statyczną analizę
- sprawdzanie stylu kodu

Dzięki temu każda zmiana byłaby od razu weryfikowana.

### Lepsze modelowanie pieniędzy

Cena jest obecnie przechowywana jako string, co było świadomą decyzją ze względu na precyzję.
W większym systemie rozważyłbym:
- Value Object dla pieniędzy
- przechowywanie wartości w najmniejszych jednostkach, np. groszach
- dedykowaną bibliotekę do pracy z pieniędzmi

### Bardziej rozbudowana warstwa domenowa

Na początku rozważałem mocniejsze pójście w DDD, ale przy tym zakresie byłoby to przesadą.
W większym systemie można byłoby rozważyć:
- bardziej wyraźne rozdzielenie warstw
- dedykowane wyjątki domenowe
- bardziej formalne command/query handlers

### Audyt zmian użytkownika
 
W dalszym rozwoju dodałbym:
- użytkownika wykonującego zmianę
- zapis changedBy
- pełniejszy audyt operacji
