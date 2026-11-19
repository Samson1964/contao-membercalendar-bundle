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

use Contao\Model;

/**
 * Modell für die Veranstaltungsarten.
 *
 * Die Tabelle wird von einer anderen Erweiterung bereitgestellt.
 */
class EventTypeModel extends Model
{
    /**
     * Tabellenname.
     *
     * @var string
     */
    protected static $strTable = 'tl_event_type';
}
