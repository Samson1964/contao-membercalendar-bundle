# Mitglieder-Termine für Contao

Zwei Frontendmodule für die Website des Deutschen Schachbundes: Angemeldete
Mitglieder tragen eigene Termine in freigegebene Kalender ein, sehen ihre
Termine in einer Liste und können sie dort bearbeiten oder löschen. Bei jedem
Speichern verschickt das Notification Center eine Nachricht.

Ursprünglich von der redmind GmbH für den DSB entwickelt, seit 2020 mehrfach
gepflegt und hier als eigenständiges Contao-Bundle verpackt.

## Voraussetzungen

* PHP 7.4 oder 8.x
* Contao 4.13 mit Kalender-Bundle
* [Haste](https://packagist.org/packages/codefog/contao-haste) **^4.24**
* [Notification Center](https://packagist.org/packages/terminal42/notification_center) ^1.6
* Die Tabelle `tl_event_type` und das Feld `eventType` in `tl_calendar_events`
  als Haste-Beziehung (siehe unten) – **beides bringt dieses Bundle nicht mit**.

### Warum Haste 4?

Das Formular nutzt `Haste\Form\Form` und das Termin-Modell `Haste\Model\Model`.
Haste 5 hat beide Namespaces geändert; das Notification Center verlangt ab 1.7
zwingend Haste 5. Deshalb hängt das Bundle an Haste ^4.24 und Notification
Center 1.6.x. Vor der Installation auf dem Zielsystem nachsehen:

```bash
composer show codefog/contao-haste terminal42/notification_center
```

## Modultypen

| Typ | Name im Backend | Template |
| --- | --- | --- |
| `calendar_list` | Liste der Kalendereinträge | `mod_calendar_list` |
| `calendar_form` | Formular Kalendereintrag | `mod_calendar_form` |

Die Liste verlinkt zum Bearbeiten auf die Seite aus „Weiterleitungsseite“; dort
liegt das Formular und öffnet den Termin über `?event_id=<id>`.

Das Formular braucht einen **Ordner für Datei-Uploads** (Bild und Anhang) und
eine Benachrichtigung vom Typ „Benachrichtigung bei Kalender-Eintrag eines
Mitglieds“. Verfügbare Platzhalter: `##form_*##` mit allen Formularfeldern und
`##member_*##` mit den Daten des Mitglieds.

## Ergänzte Felder

| Tabelle | Feld | Zweck |
| --- | --- | --- |
| `tl_calendar` | `availableForMember` | Kalender im Formular anbieten |
| `tl_calendar_events` | `memberId` | Mitglied, dem der Termin gehört |
| `tl_member` | `default_calendar` | vorausgewählter Kalender im Formular |
| `tl_module` | `calendarUploadDir` | Zielordner der Uploads |

## Veranstaltungsarten

Im Formular wählt das Mitglied eine oder mehrere Veranstaltungsarten aus
`tl_event_type`. Gespeichert werden sie über das Feld `eventType` in
`tl_calendar_events`, eine Haste-Beziehung vom Typ `haste-ManyToMany` mit der
Zwischentabelle `tl_calendar_events_event_type`. Diese DCA-Definition gehört
**nicht** zu diesem Bundle und auch nicht zum früheren Redmind-Code in
`src/redmind/`; sie stammt aus einem anderen Teil der schachbund.de-Installation
und muss dort bleiben.

Damit Haste die Arten beim Bearbeiten wieder laden kann, registriert das Bundle
ein Modell `EventTypeModel` für `tl_event_type`, falls noch keines registriert ist.

## Installation

Solange das Paket nicht auf Packagist steht, das GitHub-Repository als Quelle in
die `composer.json` des Projekts eintragen:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/Samson1964/contao-membercalendar-bundle" }
]
```

Danach:

```bash
composer require schachbulle/contao-membercalendar-bundle:^1.0
vendor/bin/contao-console contao:install
vendor/bin/contao-console cache:clear --env=prod
```

## Umstieg von `src/redmind/MemberCalendarBundle`

1. In der `composer.json` des Servers den psr-4-Eintrag
   `"Redmind\\MemberCalendarBundle\\": "src/redmind/MemberCalendarBundle/"` entfernen und
   `RedmindMemberCalendarBundle` aus `app/ContaoManagerPlugin.php` (bzw. `config/bundles.php`) austragen.
2. Den Ordner `src/redmind/MemberCalendarBundle/` löschen.
3. Die Assets liegen jetzt unter `bundles/contaomembercalendar/` statt
   `bundles/redmindmembercalendar/`. Eigene Template-Kopien im Theme anpassen:

   ```bash
   grep -rl "bundles/redmindmembercalendar" templates/
   ```

4. Datenbank-Aktualisierung im Backend **prüfen**: Für `tl_calendar`,
   `tl_calendar_events`, `tl_member` und `tl_module` darf nichts vorgeschlagen
   werden. Werden `DROP`-Anweisungen angeboten, nicht ausführen.
5. Einmal einen Termin mit Veranstaltungsart über das Formular anlegen und
   wieder öffnen – die Häkchen müssen erhalten bleiben.

Die Modultypen in `tl_module.type` und der Benachrichtigungstyp bleiben gleich.

## Lizenz

Proprietär. Der ursprüngliche Code stammt von der redmind GmbH und wurde für den
Deutschen Schachbund e.V. entwickelt; die Urheberrechtsvermerke sind erhalten.
