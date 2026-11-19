<?php

declare(strict_types=1);

/*
 * Deutscher Schachbund e.V.
 *
 * @copyright  Copyright (c) 2020, redmind GmbH
 * @author     redmind GmbH <info@redmind.de>
 * @license    proprietary
 */

use Contao\CalendarModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('calendar_legend', 'groups_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('default_calendar', 'calendar_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member');

$GLOBALS['TL_DCA']['tl_member']['fields']['default_calendar'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['default_calendar'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => static function () {
        $calendars = CalendarModel::findByAvailableForMember(1);
        $calendarOptions = [];
        if (null !== $calendars) {
            while ($calendars->next()) {
                $calendarOptions[$calendars->id] = $calendars->title;
            }
        }
        return $calendarOptions;
    },
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50', 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'address'],
    'sql' => "int(10) unsigned NOT NULL default 0",
];