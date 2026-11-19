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
    ->addField('availableForMember', 'title_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_calendar');


$GLOBALS['TL_DCA']['tl_calendar']['fields']['availableForMember'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_calendar']['availableForMember'],
    'default' => 0,
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];