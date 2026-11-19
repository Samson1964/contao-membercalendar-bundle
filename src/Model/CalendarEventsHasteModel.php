<?php

declare(strict_types=1);

/*
 * Contao Membercalendar Bundle
 *
 * Ursprünglich entwickelt von der redmind GmbH für den Deutschen Schachbund e.V.
 *
 * @copyright  Copyright (c) 2020, redmind GmbH
 * @author     redmind GmbH <info@redmind.de>
 * @license    proprietary
 */

namespace Schachbulle\ContaoMembercalendarBundle\Model;

use Haste\Model\Model;

/**
 * Schlankes Termin-Modell zum Anlegen und Bearbeiten von Mitglieder-Terminen.
 *
 * Erbt bewusst von Haste\Model\Model und nicht von Contao\Model: Das Feld
 * „eventType“ in tl_calendar_events ist eine Haste-ManyToMany-Beziehung zur
 * Tabelle tl_event_type, gespeichert in tl_calendar_events_event_type. Nur das
 * Haste-Modell schreibt beim save() die Zuordnungen in diese Zwischentabelle
 * und liefert sie über getRelated('eventType') wieder aus. Mit Contao\Model
 * gingen die gewählten Veranstaltungsarten beim Speichern verloren, und
 * getRelated() kennt den Beziehungstyp „haste-ManyToMany“ nicht.
 *
 * Das Feld selbst, die Tabelle tl_event_type und das dafür in
 * $GLOBALS['TL_MODELS'] registrierte Modell, über das getRelated() die
 * Zieltabelle auflöst, kommen aus schachbulle/contao-eventtypes-bundle.
 *
 * Das contao-dsb-bundle 1.0.0 hatte hier auf Contao\Model umgestellt; diese
 * Fassung lief nie produktiv und ist mit diesem Bundle zurückgenommen.
 */
class CalendarEventsHasteModel extends Model
{
    /**
     * Tabellenname.
     *
     * @var string
     */
    protected static $strTable = 'tl_calendar_events';
}
