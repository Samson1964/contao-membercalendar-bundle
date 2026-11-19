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

namespace Schachbulle\ContaoMembercalendarBundle\ContaoManager;

use Contao\CalendarBundle\ContaoCalendarBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Schachbulle\ContaoMembercalendarBundle\ContaoMembercalendarBundle;

/**
 * Meldet das Bundle beim Contao Manager an.
 */
class Plugin implements BundlePluginInterface
{
    /**
     * Liefert die Bundle-Konfiguration für den Contao Manager.
     *
     * Das Bundle muss nach dem Kalender-Bundle geladen werden, weil es die
     * Paletten von tl_calendar und tl_calendar_events per PaletteManipulator
     * erweitert. Haste und Notification Center sind in den 4.x- bzw.
     * 1.x-Fassungen Contao-Module mit den Namen „haste“ und
     * „notification_center“; sie stehen ebenfalls davor, damit deren
     * config.php den hier ergänzten Benachrichtigungstyp nicht überschreibt.
     *
     * @param ParserInterface $parser Parser des Contao Managers (hier ungenutzt)
     *
     * @return array<int,BundleConfig> Genau ein Eintrag für dieses Bundle
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoMembercalendarBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    ContaoCalendarBundle::class,
                    'haste',
                    'notification_center',
                ]),
        ];
    }
}
