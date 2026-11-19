# Changelog

## Version 1.0.0 (2026-11-19)

* Add: Eigenständiges Contao-Bundle `schachbulle/contao-membercalendar-bundle`
  (Namespace `Schachbulle\ContaoMembercalendarBundle`), herausgelöst aus
  `contao-dsb-bundle` 1.0.0, das die vier Redmind-Erweiterungen aus `src/redmind/`
  zusammengefasst hatte. Übernommen sind dessen PHP-8-Korrekturen.
* Add: Das Bundle registriert selbst ein Modell für `tl_event_type`, falls noch
  keines eingetragen ist. Bisher kam es aus dem Helper; ohne Modell lädt Haste die
  Veranstaltungsarten eines Termins nicht.
* Fix: `CalendarEventsHasteModel` erbt wieder von `Haste\Model\Model`. Das
  dsb-bundle 1.0.0 hatte auf `Contao\Model` umgestellt; damit wären die gewählten
  Veranstaltungsarten beim Speichern verloren gegangen und beim Bearbeiten leer
  geblieben. Die Fassung lief nie produktiv.
* Change: `CalenderFormModule` heißt `CalendarFormModule`; die Modultypen
  `calendar_list` und `calendar_form` sind unverändert.
* Change: Assets unter `bundles/contaomembercalendar/` statt `bundles/redmindmembercalendar/`.
* Change: Gruppe in der Modultyp-Auswahl heißt intern `dsb` statt `redmind`.
* Fix: `Patchwork\Utf8` entfernt, das in aktuellen Contao-Installationen fehlt;
  optionaler Parameter vor Pflichtparameter in der Formularmethode; zahlreiche
  „Undefined array key“-Warnungen (aus dsb-bundle 1.0.0).

## Historie vor der Bundle-Umstellung (src/redmind/MemberCalendarBundle)

* 23.09.2025 – Fix: Kalendereditor zeigte kein Beschreibungsfeld; der TinyMCE-Skin
  heißt jetzt `oxide` statt `contao`.
* 23.06.2022 – `mod_calendar_form`: Das optionale Bild wird auf 600 px Breite
  begrenzt; `picture` durch `image` ersetzt.
* 06.05.2021 – `memberId` fehlte bei besonderen Weiterleitungstypen.
* 30.11.2020 – Uhrzeit wurde nicht berücksichtigt.
* 17.09.2020 – Redmind-Code in die Versionsverwaltung übernommen.
