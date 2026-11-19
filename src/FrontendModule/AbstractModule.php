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

namespace Schachbulle\ContaoMembercalendarBundle\FrontendModule;

use Contao\BackendTemplate;
use Contao\Module;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

/**
 * Gemeinsame Basis der Frontend-Module dieses Bundles.
 *
 * Kapselt die Backend-Erkennung, damit die in Contao 5 entfernte
 * Konstante TL_MODE nicht mehr benötigt wird.
 */
abstract class AbstractModule extends Module
{
    /**
     * Liefert den aktuellen Request oder null (z. B. im CLI-Kontext).
     */
    protected function getRequest(): ?Request
    {
        return System::getContainer()->get('request_stack')->getCurrentRequest();
    }

    /**
     * Prüft, ob das Modul im Backend dargestellt wird.
     */
    protected function isBackendRequest(): bool
    {
        $request = $this->getRequest();

        if (null === $request) {
            return false;
        }

        return System::getContainer()
            ->get('contao.routing.scope_matcher')
            ->isBackendRequest($request)
        ;
    }

    /**
     * Erzeugt die Platzhalter-Ausgabe für das Backend.
     *
     * @param string $type Modultyp, z. B. "mapcalendar"
     */
    protected function generateBackendWildcard(string $type): string
    {
        $template = new BackendTemplate('be_wildcard');

        $label = $GLOBALS['TL_LANG']['FMD'][$type][0] ?? $type;

        $template->wildcard = '### '.strtoupper((string) $label).' ###';
        $template->title = $this->headline;
        $template->id = $this->id;
        $template->link = $this->name;
        $template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

        return $template->parse();
    }
}
