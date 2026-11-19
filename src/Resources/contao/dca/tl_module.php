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

/*
 * Paletten
 *
 * Das Feld nc_notification bringt das Notification Center mit.
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['calendar_list'] = '{title_legend},name,type,headline;{config_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['calendar_form'] = '{title_legend},name,type;{config_legend},calendarUploadDir,jumpTo,nc_notification;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/*
 * Felder
 *
 * Name und SQL-Typ entsprechen exakt dem Redmind-Original.
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['calendarUploadDir'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['calendarUploadDir'],
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => 'binary(16) NULL',
];
