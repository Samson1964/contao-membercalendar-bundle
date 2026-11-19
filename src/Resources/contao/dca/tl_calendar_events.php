<?php

declare(strict_types=1);

/*
 * Deutscher Schachbund e.V.
 *
 * @copyright  Copyright (c) 2020, redmind GmbH
 * @author     redmind GmbH <info@redmind.de>
 * @license    proprietary
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addField('memberId', 'title')
    ->applyToPalette('default', 'tl_calendar_events');

PaletteManipulator::create()
    ->addField('memberId', 'title')
    ->applyToPalette('internal', 'tl_calendar_events');

PaletteManipulator::create()
    ->addField('memberId', 'title')
    ->applyToPalette('article', 'tl_calendar_events');

PaletteManipulator::create()
    ->addField('memberId', 'title')
    ->applyToPalette('external', 'tl_calendar_events');


$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['memberId'] = [
    'exclude' => true,
    'search' => true,
    'filter' => true,
    'sorting' => true,
    'flag' => 11,
    'inputType' => 'select',
    'foreignKey' => 'tl_member.email',
    'eval' => ['doNotCopy' => true, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default 0",
    'relation' => ['type' => 'hasOne', 'load'=>'lazy'],
];
