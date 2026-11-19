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
use Contao\CalendarModel;
use Contao\Config;
use Contao\Database;
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use DateTime;
use Haste\Form\Form;
use NotificationCenter\Model\Notification;
use Schachbulle\ContaoMembercalendarBundle\Model\CalendarEventsHasteModel;

/**
 * Formular, mit dem angemeldete Mitglieder eigene Termine anlegen und bearbeiten.
 */
class CalendarFormModule extends AbstractModule
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_calendar_form';

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        if ($this->isBackendRequest()) {
            return $this->generateBackendWildcard('calendar_form');
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

        if (null === $objMember) {
            return;
        }

        $data = [];

        if (null !== $request && $request->query->has('event_id')) {
            $data = $this->loadEventData((int) $request->query->get('event_id'), (int) $objMember->id);
        }

        $form = $this->prepareCalendarEventForm($objMember, $data);

        if ($form->validate()) {
            $arrData = $form->fetchAll();

            $this->saveCalendarEvent($arrData, $objMember);
            $this->sendNotification($arrData, $objMember);

            if (null !== $request && $request->hasSession()) {
                $request->getSession()->getFlashBag()->add(
                    'member_event_msg',
                    $GLOBALS['TL_LANG']['calendar_form']['success_message'] ?? ''
                );
            }

            $objRedirect = PageModel::findByPk($this->jumpTo);

            if (null !== $objRedirect) {
                $this->redirect($objRedirect->getFrontendUrl());
            }
        }

        $this->Template->form = $form;
        $this->Template->eventData = $data;
    }

    /**
     * Lädt einen bestehenden Termin des Mitglieds zum Bearbeiten.
     *
     * @return array<string,mixed>
     */
    private function loadEventData(int $eventId, int $memberId): array
    {
        if ($eventId < 1) {
            return [];
        }

        $event = CalendarEventsHasteModel::findOneById($eventId);

        if (null === $event || (int) $event->memberId !== $memberId) {
            return [];
        }

        $data = $event->row();
        $eventTypes = $event->getRelated('eventType');

        if (is_iterable($eventTypes)) {
            foreach ($eventTypes as $type) {
                $data['eventTypes'][] = $type->id;
            }
        }

        if ($event->addEnclosure) {
            $this->addEnclosuresToTemplate($this->Template, $event->row());
        }

        return $data;
    }

    /**
     * Speichert die Formulardaten als Termin.
     *
     * @param array<string,mixed> $arrData
     */
    private function saveCalendarEvent(array $arrData, MemberModel $objMember): void
    {
        $startDateInput = (string) ($arrData['startDate'] ?? '');
        $startTimeInput = (string) ($arrData['startTime'] ?? '');
        $endDateInput = (string) ($arrData['endDate'] ?? '');
        $endTimeInput = (string) ($arrData['endTime'] ?? '');

        $addTime = '' !== $startTimeInput;

        $startDate = new DateTime($startDateInput);
        $startTime = $addTime
            ? new DateTime($startDateInput.' '.$startTimeInput)
            : new DateTime($startDateInput);

        $endDate = '' !== $endDateInput ? new DateTime($endDateInput) : null;

        // Ohne Endzeit läuft der Termin bis zum Tagesende
        $endBase = '' !== $endDateInput ? $endDateInput : $startDateInput;
        $endTime = new DateTime($endBase.' '.('' !== $endTimeInput ? $endTimeInput : '23:59:59'));

        $eventId = (int) ($arrData['event_id'] ?? 0);

        if ($eventId > 0) {
            $calendar = CalendarEventsModel::findOneById($eventId);
        } else {
            $calendar = null;
        }

        if (null === $calendar) {
            $calendar = new CalendarEventsHasteModel();
        }

        $calendar->tstamp = time();
        $calendar->published = 1;
        $calendar->pid = $arrData['calendar_id'] ?? 0;
        $calendar->title = $arrData['title'] ?? '';
        $calendar->startDate = $startDate->getTimestamp();
        $calendar->addTime = $addTime ? '1' : '';
        $calendar->startTime = $startTime->getTimestamp();
        $calendar->endTime = $endTime->getTimestamp();
        $calendar->endDate = null !== $endDate ? $endDate->getTimestamp() : null;
        $calendar->location = $arrData['location'] ?? '';
        $calendar->teaser = StringUtil::decodeEntities((string) ($arrData['teaser'] ?? ''));
        $calendar->eventType = empty($arrData['eventType']) ? [] : $arrData['eventType'];
        $calendar->memberId = $objMember->id;

        $this->applyUploads($calendar);

        $calendar->save();

        $calendar->alias = $this->generateAlias($calendar);
        $calendar->save();
    }

    /**
     * Übernimmt hochgeladene Dateien aus der Session in den Termin.
     */
    private function applyUploads(Model $calendar): void
    {
        if (empty($_SESSION['FILES']) || !\is_array($_SESSION['FILES'])) {
            return;
        }

        $image = $_SESSION['FILES']['image'] ?? null;

        if (\is_array($image) && 0 === ($image['error'] ?? 1) && !empty($image['uuid'])) {
            $calendar->addImage = 1;
            $calendar->singleSRC = StringUtil::uuidToBin($image['uuid']);
        }

        $enclosure = $_SESSION['FILES']['enclosure'] ?? null;

        if (\is_array($enclosure) && 0 === ($enclosure['error'] ?? 1) && !empty($enclosure['uuid'])) {
            $calendar->addEnclosure = 1;
            $calendar->enclosure = [StringUtil::uuidToBin($enclosure['uuid'])];
        }

        unset($_SESSION['FILES']);
    }

    /**
     * Baut das Formular auf.
     *
     * @param array<string,mixed> $data Daten eines bestehenden Termins
     */
    private function prepareCalendarEventForm(MemberModel $objMember, array $data = []): Form
    {
        $calendars = CalendarModel::findByAvailableForMember(1);

        $calendarOptions = [];

        if (null !== $calendars) {
            while ($calendars->next()) {
                $calendarOptions[$calendars->id] = $calendars->title;
            }
        }

        $objForm = new Form('calendar_event', 'POST', static function ($objHaste) {
            return Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        });

        if (!empty($data['id'])) {
            $objForm->addFormField('event_id', [
                'inputType' => 'hidden',
                'value' => $data['id'],
            ]);
        }

        // Ohne Uhrzeit-Angabe bleiben die Zeitfelder leer
        if (empty($data['addTime'])) {
            unset($data['startTime'], $data['endTime']);
        }

        // 23:59:59 ist der automatisch gesetzte Tagesabschluss, kein echter Nutzerwert
        if (isset($data['endTime']) && '23:59:59' === date('H:i:s', (int) $data['endTime'])) {
            unset($data['endTime']);
        }

        $objForm->addFormField('calendar_id', [
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['calendar'],
            'inputType' => 'select',
            'options' => $calendarOptions,
            'eval' => ['mandatory' => true, 'includeBlankOption' => true],
            'value' => empty($data['pid']) ? $objMember->default_calendar : $data['pid'],
        ]);

        $objForm->addFormField('eventType', [
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['eventType'],
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_event_type.name',
            'value' => $data['eventTypes'] ?? null,
        ]);

        $objForm->addFormField('title', [
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['title'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true],
            'value' => $data['title'] ?? '',
        ]);

        $objForm->addFormField('startDate', [
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['startDate'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'mandatory' => true, 'class' => 'date', 'autocomplete' => 'off'],
            'value' => $data['startDate'] ?? '',
        ]);

        $objForm->addFormField('startTime', [
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['startTime'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'time', 'mandatory' => false, 'class' => 'time', 'placeholder' => '00:00', 'autocomplete' => 'off'],
            'value' => $data['startTime'] ?? '',
        ]);

        $objForm->addFormField('endDate', [
            'default' => '',
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['endDate'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'mandatory' => false, 'class' => 'date', 'autocomplete' => 'off'],
            'value' => $data['endDate'] ?? '',
        ]);

        $objForm->addFormField('endTime', [
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['endTime'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'time', 'mandatory' => false, 'class' => 'time', 'autocomplete' => 'off'],
            'value' => $data['endTime'] ?? '',
        ]);

        $objForm->addFormField('location', [
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['location'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255],
            'value' => $data['location'] ?? '',
        ]);

        $objForm->addFormField('teaser', [
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['teaser'],
            'inputType' => 'textarea',
            'eval' => ['mandatory' => true, 'class' => 'tinymce', 'allowHtml' => true],
            'value' => $data['teaser'] ?? '',
        ]);

        $objForm->addFormField('image', [
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['image'],
            'inputType' => 'upload',
            'eval' => [
                'mandatory' => false,
                'extensions' => 'jpg,jpeg,gif,png',
                'storeFile' => true,
                'doNotOverwrite' => true,
                'uploadFolder' => $this->calendarUploadDir,
                'maxlength' => Config::get('maxFileSize'),
                'fSize' => Config::get('maxFileSize'),
            ],
        ]);

        $objForm->addFormField('enclosure', [
            'label' => &$GLOBALS['TL_LANG']['calendar_form']['enclosure'],
            'inputType' => 'upload',
            'eval' => [
                'mandatory' => false,
                'extensions' => 'jpg,jpeg,gif,png,pdf,doc,docx,zip,odt',
                'storeFile' => true,
                'doNotOverwrite' => true,
                'uploadFolder' => $this->calendarUploadDir,
                'maxlength' => Config::get('maxFileSize'),
                'fSize' => Config::get('maxFileSize'),
            ],
        ]);

        $objForm->addCaptchaFormField('captcha');
        $objForm->addSubmitFormField('submit', $GLOBALS['TL_LANG']['calendar_form']['submit'] ?? 'OK');

        return $objForm;
    }

    /**
     * Erzeugt einen eindeutigen Alias für den Termin.
     */
    private function generateAlias(Model $event): string
    {
        $aliasExists = static function (string $alias) use ($event): bool {
            return Database::getInstance()
                ->prepare('SELECT id FROM tl_calendar_events WHERE alias=? AND id!=?')
                ->execute($alias, $event->id)
                ->numRows > 0;
        };

        $calendar = CalendarModel::findByPk($event->pid);

        return System::getContainer()->get('contao.slug')->generate(
            (string) $event->title,
            (int) (null !== $calendar ? $calendar->jumpTo : 0),
            $aliasExists
        );
    }

    /**
     * Verschickt die Bestätigung über das Notification Center.
     *
     * @param array<string,mixed> $arrData
     */
    private function sendNotification(array $arrData, MemberModel $objMember): void
    {
        $objNotification = Notification::findByPk($this->nc_notification);

        if (null === $objNotification) {
            return;
        }

        $arrTokens = [
            'member_id' => $objMember->id,
            'member_email' => $objMember->email,
            'member_firstname' => $objMember->firstname,
            'member_lastname' => $objMember->lastname,
        ];

        foreach ($arrData as $key => $value) {
            // Tokens müssen skalar sein, sonst schlägt das Ersetzen fehl
            $arrTokens['form_'.$key] = \is_array($value) ? implode(', ', $value) : $value;
        }

        $calendar = CalendarModel::findByPk($arrData['calendar_id'] ?? 0);
        $arrTokens['form_calendar_title'] = null !== $calendar ? $calendar->title : '';

        $objNotification->send($arrTokens);
    }
}
