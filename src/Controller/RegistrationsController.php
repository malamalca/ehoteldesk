<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * Registrations Controller
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 * @property \App\Model\Table\CountersTable $Counters
 * @property \App\Model\Table\RoomsTable $Rooms
 */
class RegistrationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $filter = (array)$this->getRequest()->getQuery();
        $filter['owner'] = $this->getCurrentUser()->get('company_id');

        if (empty($filter['start'])) {
            $filter['start'] = new FrozenDate();
        } else {
            $filter['start'] = FrozenDate::parseDate($filter['start'], 'yyyy-MM-dd');
            if (!$filter['start']) {
                $filter['start'] = new FrozenDate();
            }
        }

        $q = $this->Authorization->applyScope($this->Registrations->find());
        $registrations = $this->Registrations->filter('all', $q, $filter);

        $this->loadModel('Counters');
        $counters = $this->Counters->findForOwner('list', 'V', $this->getCurrentUser()->get('company_id'));

        $this->loadModel('Rooms');
        $rooms = $this->Rooms->findForOwner('list', $this->getCurrentUser()->get('company_id'));

        unset($filter['owner']);
        unset($filter['end']);

        $this->set(compact('registrations', 'filter', 'rooms', 'counters'));
        $this->set('_serialize', ['registrations']);
    }

    /**
     * View method
     *
     * @param string|null $id Registration id.
     * @return \Cake\Http\Response|void
     */
    public function view($id = null)
    {
        $registration = $this->Registrations->get($id, ['contain' => ['Rooms', 'ServiceTypes']]);

        $this->Authorization->authorize($registration);

        $this->set(compact('registration'));
        $this->set('_serialize', ['registration']);
    }

    /**
     * Add method
     *
     * @return void
     */
    public function add()
    {
        $this->setAction('edit');
    }

    /**
     * Edit method
     *
     * @param string|null $id Registration id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     */
    public function edit($id = null)
    {
        if ($id) {
            $registration = $this->Registrations->get($id);
            if (!empty($registration->etur_guid)) {
                $this->Flash->error(__('Cannot edit registration that was already sent to ETurizem.'));

                return $this->redirect(['action' => 'view', $registration->id]);
            }
        } else {
            $Contacts = TableRegistry::get('LilCrm.Contacts');
            $ContactsAddresses = TableRegistry::get('LilCrm.ContactsAddresses');

            $registration = $this->Registrations->newEmptyEntity();
            $registration->company_id = $this->getCurrentUser()->get('company_id');
            $registration->counter_id = $this->getRequest()->getQuery('counter');

            // duplicate reservation
            $reservationId = $this->getRequest()->getQuery('reservation');
            if (!empty($reservationId)) {
                /** @var \App\Model\Table\ReservationsTable $Reservations */
                $Reservations = TableRegistry::get('Reservations');
                $reservation = $Reservations->get($reservationId);

                $registration->counter_id = $reservation->counter_id;
                $registration->start = $reservation->start;
                $registration->end = $reservation->end;
                $registration->room_id = $reservation->room_id;
                $registration->surname = $reservation->name;
                $registration->street = $reservation->address;
                //todo: $this->getRequest()->data['referer'] = false;
            }

            $copyId = $this->getRequest()->getQuery('copy');
            if (!empty($copyId)) {
                $sourceRegistration = $this->Registrations->get($copyId);

                $registration = $sourceRegistration;
                $registration->id = '';
                $registration->isNew(true);
            }
        }

        $this->Authorization->authorize($registration);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $registration = $this->Registrations->patchEntity($registration, $this->getRequest()->getData());

            if ($this->Registrations->save($registration)) {
                // delete reservation based on this registration
                $reservationId = $this->getRequest()->getData('reservation_id');
                if (!empty($reservationId)) {
                    $Reservations = TableRegistry::get('Reservations');
                    $reservation = $Reservations->get($this->getRequest()->getData('reservation_id'));
                    $Reservations->delete($reservation);
                }

                $this->Flash->success(__('The registration has been saved.'));

                return $this->redirect([
                    'action' => 'index',
                    '?' => [
                        'counter' => $registration->counter_id,
                        'start' => $registration->start->toDateString(),
                    ],
                ]);
            } else {
                $this->Flash->error(__('The registration could not be saved. Please, try again.'));
            }
        }

        /** @var \App\Model\Table\RoomsTable $RoomsTable */
        $RoomsTable = TableRegistry::get('Rooms');
        $rooms = $RoomsTable->findForOwner('list', $this->getCurrentUser()->get('company_id'));
        if (empty($rooms)) {
            $this->Flash->error(__('No rooms defined. Please add a first counter.'));

            $this->redirect(['controller' => 'Rooms', 'action' => 'add']);
        }

        /** @var \App\Model\Table\ServiceTypesTable $ServiceTypesTable */
        $ServiceTypesTable = TableRegistry::get('ServiceTypes');
        $serviceTypes = $ServiceTypesTable->findForOwner('list', $this->getCurrentUser()->get('company_id'));

        $this->set(compact('registration', 'rooms', 'serviceTypes'));
        $this->set('_serialize', ['registration']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Registration id.
     * @return \Cake\Http\Response|null Redirects to index.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete', 'get']);
        $registration = $this->Registrations->get($id);

        $this->Authorization->authorize($registration);

        if (!empty($registration->etur_guid)) {
            $this->Flash->error(__('Cannot edit registration that was already sent to ETurizem.'));

            return $this->redirect(['action' => 'view', $registration->id]);
        }

        if ($this->Registrations->delete($registration)) {
            $this->Flash->success(__('The registration has been deleted.'));
        } else {
            $this->Flash->error(__('The registration could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index', 'filter' => ['counter' => $registration->counter_id]]);
    }

    /**
     * Report Analytics method
     *
     * @return void
     */
    public function reportAnalytics()
    {
        $this->loadModel('Counters');
        $counters = $this->Counters
            ->findForOwner('list', 'V', $this->getCurrentUser()->get('company_id'))
            ->combine('id', 'title')
            ->toArray();
        $this->set(compact('counters'));

        $this->Authorization->skipAuthorization();

        $aDate = $this->getRequest()->getQuery('on');
        if (!empty($aDate)) {
            error_reporting(0);
            $aDate = new FrozenDate($aDate);
            $this->viewBuilder()->setClassName('Lil.Pdf');
            $registrations = $this->Authorization->applyScope($this->Registrations->find(), 'index')
                ->where([
                    'Registrations.counter_id' => $this->getRequest()->getQuery('counter'),
                    'Registrations.start' => $aDate,
                ])
                ->contain(['Rooms'])
                ->order('Rooms.no')
                ->all();
            $counter = $counters[$this->getRequest()->getQuery('counter')];
            $this->set(compact('registrations', 'aDate', 'counter'));

            $this->response = $this->response->withType('application/pdf');
        }
    }

    /**
     * Report Services method
     *
     * @return void
     */
    public function reportServices()
    {
        /** @var \App\Model\Table\CountersTable $CountersTable */
        $CountersTable = TableRegistry::get('Counters');
        $counters = $CountersTable
            ->findForOwner('list', 'V', $this->getCurrentUser()->get('company_id'))
            ->combine('id', 'title')
            ->toArray();
        $this->set(compact('counters'));

        $this->Authorization->skipAuthorization();

        $aDate = $this->getRequest()->getQuery('on');
        if (!empty($aDate)) {
            error_reporting(0);
            $aDate = new FrozenDate($aDate);
            $this->viewBuilder()->setClassName('Lil.Pdf');
            $registrations = $this->Authorization->applyScope($this->Registrations->find(), 'index')
                ->where([
                    'Registrations.counter_id' => $this->getRequest()->getQuery('counter'),
                    'AND' => [
                        'Registrations.start <=' => $aDate,
                        'Registrations.end >=' => $aDate,
                    ],
                ])
                ->contain(['Rooms', 'ServiceTypes'])
                ->order(['ServiceTypes.title', 'Rooms.no'])
                ->all();
            $counter = $counters[$this->getRequest()->getQuery('counter')];
            $this->set(compact('registrations', 'aDate', 'counter'));

            $this->response = $this->response->withType('application/pdf');
        }
    }

    /**
     * Report Guests method
     *
     * @return void
     */
    public function reportGuests()
    {
        /** @var \App\Model\Table\CountersTable $CountersTable */
        $CountersTable = TableRegistry::get('Counters');
        $counters = $CountersTable
            ->findForOwner('list', 'V', $this->getCurrentUser()->get('company_id'))
            ->combine('id', 'title')
            ->toArray();
        $this->set(compact('counters'));

        $this->Authorization->skipAuthorization();

        $aDate = $this->getRequest()->getQuery('on');
        if (!empty($aDate)) {
            error_reporting(0);
            $aDate = new FrozenDate($aDate);
            $this->viewBuilder()->setClassName('Lil.Pdf');
            $registrations = $this->Authorization->applyScope($this->Registrations->find(), 'index')
                ->where([
                    'Registrations.counter_id' => $this->getRequest()->getQuery('counter'),
                    'AND' => [
                        'Registrations.start <=' => $aDate,
                        'Registrations.end >=' => $aDate,
                    ],
                ])
                ->contain(['Rooms'])
                ->order(['Rooms.no'])
                ->all();
            $counter = $counters[$this->getRequest()->getQuery('counter')];
            $this->set(compact('registrations', 'aDate', 'counter'));

            $this->response = $this->response->withType('application/pdf');
        }
    }
}
