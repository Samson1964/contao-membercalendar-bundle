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

use Schachbulle\ContaoMembercalendarBundle\FrontendModule\CalendarFormModule;
use Schachbulle\ContaoMembercalendarBundle\FrontendModule\CalendarListModule;
use Schachbulle\ContaoMembercalendarBundle\Model\EventTypeModel;

/*
 * Frontend-Module
 *
 * Die Schlüssel „calendar_list“ und „calendar_form“ stehen in tl_module.type
 * der bestehenden Module und dürfen nicht verändert werden. Der
 * Gruppenschlüssel „dsb“ wird nicht gespeichert.
 */
$GLOBALS['FE_MOD']['dsb']['calendar_list'] = CalendarListModule::class;
$GLOBALS['FE_MOD']['dsb']['calendar_form'] = CalendarFormModule::class;

/*
 * Modell der Veranstaltungsarten
 *
 * Haste löst bei getRelated('eventType') die Zieltabelle über TL_MODELS auf.
 * Ohne diesen Eintrag bleiben die Veranstaltungsarten beim Bearbeiten eines
 * Termins leer. Ist das Kalenderkarten-Bundle installiert, registriert es
 * eine gleichwertige Klasse; wer zuerst kommt, gewinnt.
 */
$GLOBALS['TL_MODELS']['tl_event_type'] = $GLOBALS['TL_MODELS']['tl_event_type'] ?? EventTypeModel::class;

/*
 * Notification Center
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['member_calendar'] = [
    'event_form' => [
        'recipients' => ['admin_email', 'form_*', 'member_*'],
        'email_subject' => ['form_*', 'member_*'],
        'email_text' => ['form_*', 'member_*'],
        'email_html' => ['form_*', 'member_*'],
    ],
];
