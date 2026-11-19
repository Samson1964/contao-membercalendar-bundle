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

use Contao\CalendarEventsModel;
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\PageModel;

/**
 * Listet die eigenen Termine eines angemeldeten Mitglieds auf.
 */
class CalendarListModule extends AbstractModule
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_calendar_list';

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        if ($this->isBackendRequest()) {
            return $this->generateBackendWildcard('calendar_list');
        }

        return parent::generate();
    }

    /**
     * {@inheritdoc}
     */
    protected function compile(): void
    {
        $request = $this->getRequest();

        $this->import(FrontendUser::class, 'User');
        $objMember = MemberModel::findByPk($this->User->id ?? 0);
        $memberId = null !== $objMember ? (int) $objMember->id : 0;

        // Termin soll gelöscht werden
        if (null !== $request && $request->query->has('delete') && $request->query->has('event_id')) {
            $this->deleteCalendarEvent((int) $request->query->get('event_id'), $memberId);

            if ($request->hasSession()) {
                $request->getSession()->getFlashBag()->add(
                    'member_event_msg',
                    $GLOBALS['TL_LANG']['calendar_form']['delete_message'] ?? ''
                );
            }

            // Hinweis: Der Redirect liefert ggf. die Seite aus dem Cache aus,
            // der gelöschte Termin kann dadurch kurzzeitig weiter erscheinen.
            $objCurrent = $request->attributes->get('pageModel');

            if ($objCurrent instanceof PageModel) {
                $this->redirect($objCurrent->getFrontendUrl());
            }
        }

        if (null !== $request && $request->hasSession() && $request->getSession()->getFlashBag()->has('member_event_msg')) {
            $messages = $request->getSession()->getFlashBag()->get('member_event_msg');
            $this->Template->message = $messages[0] ?? '';
        }

        $objTarget = PageModel::findByPk($this->jumpTo);
        $this->Template->url = null !== $objTarget ? $objTarget->getFrontendUrl() : '';

        $events = [];

        if ($memberId > 0) {
            $calendarEvents = CalendarEventsModel::findByMemberId($memberId, ['order' => 'startDate DESC']);

            if (null !== $calendarEvents) {
                while ($calendarEvents->next()) {
                    $events[] = $calendarEvents->row();
                }
            }
        }

        $this->Template->events = $events;
    }

    /**
     * Löscht einen Termin, sofern er dem Mitglied gehört.
     */
    private function deleteCalendarEvent(int $id, int $memberId): void
    {
        if ($id < 1 || $memberId < 1) {
            return;
        }

        $event = CalendarEventsModel::findOneById($id);

        if (null === $event || (int) $event->memberId !== $memberId) {
            return;
        }

        $event->delete();
    }
}
