<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * Reservations Controller
 *
 * @property \App\Model\Table\ReservationsTable $Reservations
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class ReservationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $this->loadModel('Registrations');

        $filter = $this->getRequest()->getQuery();
        $filter['owner'] = $this->getCurrentUser()->get('company_id');

        $reservations = $this->Reservations->filter(
            'list',
            $this->Authorization->applyScope($this->Reservations->find()),
            $filter
        );
        $registrations = $this->Registrations->filter(
            'list',
            $this->Authorization->applyScope($this->Registrations->find()),
            $filter
        );

        $minYear = $this->Reservations->getMinYear($filter['counter']);

        /** @var \App\Model\Table\RoomsTable $RoomsTable */
        $RoomsTable = TableRegistry::get('Rooms');
        $rooms = $RoomsTable->findForOwner('all', $this->getCurrentUser()->get('company_id'));

        /** @var \App\Model\Table\CountersTable $CountersTable */
        $CountersTable = TableRegistry::get('Counters');
        $counters = $CountersTable->findForOwner(
            'list',
            'V',
            $this->getCurrentUser()->get('company_id')
        );

        unset($filter['start']);
        unset($filter['end']);
        unset($filter['owner']);

        $this->set(compact('reservations', 'registrations', 'rooms', 'counters', 'filter', 'minYear'));
        $this->set('_serialize', ['reservations']);
    }

    /**
     * View method
     *
     * @param string|null $id Reservation id.
     * @return \Cake\Http\Response|void
     */
    public function view($id = null)
    {
        $reservation = $this->Reservations->get($id, [
            'contain' => ['Rooms'],
        ]);

        $this->Authorization->authorize($reservation);

        $invoices = [];
        if (Configure::read('useInvoices')) {
            $invoices = TableRegistry::get('LilInvoices.Invoices')->find()
                ->select()
                ->where([
                    'Invoices.owner_id' => $this->getCurrentUser()->get('company_id'),
                    'Invoices.reservation_id' => $reservation->id,
                ])
                ->contain(['InvoicesCounters'])
                ->order('Invoices.no')

                ->all();
        }

        $this->set(compact('reservation', 'invoices'));
        $this->set('_serialize', ['reservation']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->setAction('edit');
    }

    /**
     * Edit method
     *
     * @param string|null $id Reservation id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     */
    public function edit($id = null)
    {
        /** @var \App\Model\Table\CountersTable $CountersTable */
        $CountersTable = TableRegistry::get('Counters');

        /** @var \App\Model\Table\RoomsTable $RoomsTable */
        $RoomsTable = TableRegistry::get('Rooms');

        if (empty($id)) {
            $counterId = $this->getRequest()->getQuery('counter');
            if (!empty($counterId)) {
                $counter = $CountersTable->get($counterId);
            } else {
                $counter = $CountersTable->findDefaultCounter(
                    'V',
                    $this->getCurrentUser()->get('company_id')
                );
            }

            $reservation = $this->Reservations->newEmptyEntity();
            $reservation->company_id = $this->getCurrentUser()->get('company_id');
            $reservation->counter_id = $counter->id;
            $reservation->room_id = $this->getRequest()->getQuery('room');

            $reservation->no = $CountersTable->getNextNo($counter);
        } else {
            $reservation = $this->Reservations->get($id);
        }

        $this->Authorization->authorize($reservation);

        $room = null;
        if (!empty($reservation->room_id)) {
            $room = $RoomsTable->get($reservation->room_id);
            if ($reservation->isNew()) {
                $reservation->persons = $room->beds;
            }
        }

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $reservation = $this->Reservations->patchEntity($reservation, $this->getRequest()->getData());
            if ($this->Reservations->save($reservation)) {
                $this->Flash->success(__('The reservation has been saved.'));
                $referer = $this->getRequest()->getData('referer');
                if (!empty($referer)) {
                    return $this->redirect($referer);
                }

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The reservation could not be saved. Please, try again.'));
            }
        }

        $rooms = $RoomsTable->findForOwner('list', $this->getCurrentUser()->get('company_id'));

        $this->set(compact('reservation', 'room', 'rooms'));
        $this->set('_serialize', ['reservation']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Reservation id.
     * @return \Cake\Http\Response|null Redirects to index.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete', 'get']);
        $reservation = $this->Reservations->get($id);
        $this->Authorization->authorize($reservation);

        if ($this->Reservations->delete($reservation)) {
            $this->Flash->success(__('The reservation has been deleted.'));
        } else {
            $this->Flash->error(__('The reservation could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Create invoice for specified registration
     *
     * @param string $id Reservation id.
     * @return \Cake\Http\Response|void
     */
    public function createInvoice($id)
    {
        /** @var \LilInvoices\Model\Table\InvoicesTable $Invoices */
        $Invoices = TableRegistry::get('LilInvoices.Invoices');

        /** @var \LilInvoices\Model\Table\InvoicesCountersTable $InvoicesCounters */
        $InvoicesCounters = TableRegistry::get('LilInvoices.InvoicesCounters');

        /** @var \LilInvoices\Model\Table\InvoicesClientsTable $InvoicesClients */
        $InvoicesClients = TableRegistry::get('LilInvoices.InvoicesClients');

        /** @var \LilInvoices\Model\Table\ItemsTable $Items */
        $Items = TableRegistry::get('LilInvoices.Items');

        $reservation = $this->Reservations->get($id, ['contain' => ['Counters', 'Rooms' => ['Vats']]]);

        $this->Authorization->authorize($reservation, 'view');

        if (empty($reservation->counter->invoices_counter_id)) {
            $this->Flash->error(__('The invoices counter does not exist.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $counter = $InvoicesCounters->get($reservation->counter->invoices_counter_id);

        $invoice = $Invoices->newEmptyEntity();
        $invoice->title = __('Reservations Invoice');
        $invoice->location = $this->getCurrentUser()->get('company.city');
        $invoice->counter_id = $counter->id;
        $invoice->doc_type = $counter->doc_type;
        $invoice->no = $InvoicesCounters->generateNo($invoice->counter_id);
        //$invoice->reservation_id = $reservation->id;
        $invoice->user_id = $this->getCurrentUser()->get('id');
        $invoice->dat_issue = new FrozenDate();
        $invoice->dat_expire = $invoice->dat_issue->addDays(8);
        $invoice->dat_service = new FrozenDate($reservation->start);

        $invoice->issuer = $InvoicesClients->newEmptyEntity();
        $this->_patchWithAuth($invoice->issuer);
        $invoice->receiver = $InvoicesClients->newEmptyEntity();
        $this->_patchWithReservation($invoice->receiver, $reservation);
        $invoice->buyer = $InvoicesClients->newEmptyEntity();
        $this->_patchWithReservation($invoice->buyer, $reservation);
        $invoice->buyer->kind = 'BY';

        $invoice->invoices_items = [$Items->newEntity([
            'descript' => $reservation->room->toString(),
            'qty' => $reservation->end->diffInDays($reservation->start),
            'unit' => __('days'),
            'discount' => 0,
            'price' => $reservation->room->priceperday,
            'vat_id' => $reservation->room->vat->id ?? null,
            'vat_title' => isset($reservation->room->vat->descript) ? $reservation->room->vat->descript : null,
            'vat_percent' => $reservation->room->vat->percent ?? null,
        ])];

        /** @var \LilInvoices\Model\Table\VatsTable $VatsTable */
        $VatsTable = TableRegistry::get('LilInvoices.Vats');
        $vatLevels = $VatsTable->levels($this->getCurrentUser()->get('company_id'));

        $this->set(compact('invoice', 'counter', 'vatLevels', 'reservation'));
    }

    /**
     * Patch Client Entity with reservation data
     *
     * @param \LilInvoices\Model\Entity\InvoicesClient $client Client Entity
     * @param \App\Model\Entity\Reservation $reservation Reservation Entity
     * @return void
     */
    private function _patchWithReservation($client, $reservation)
    {
        $client->kind = 'IV';
        $client->title = $reservation->name;
        $client->street = $reservation->address;
        $client->country_code = $reservation->country_code;
    }

    /**
     * Patch Client Entity with Auth data
     *
     * @param \LilInvoices\Model\Entity\InvoicesClient $issuer Client Entity
     * @return void
     */
    private function _patchWithAuth($issuer)
    {
        $data = $this->getCurrentUser();

        /** @var \App\Model\Table\CompaniesTable $CompaniesTable */
        $CompaniesTable = TableRegistry::get('Companies');
        $company = $CompaniesTable->get($data->company_id);

        $issuer->kind = 'II';
        $issuer->contact_id = $company->id;
        $issuer->title = $company->name;
        //$issuer->mat_no = $company->mat_no;
        $issuer->tax_no = $company->tax_no;

        $issuer->street = $company->street;
        $issuer->city = $company->city;
        $issuer->zip = $company->zip;
        //$issuer->country = $company->country;
        //$issuer->country_code = $company->country_code;

        //$issuer->iban = $company->iban;
        //$issuer->bank = $company->bank;   // todo: convert bic to bank name

        $issuer->person = $data->name;
    }

    /**
     * Preview invoice in an iframe
     *
     * @param string $reservationId Reservation id
     * @param string $invoiceId Invoice id
     * @return void
     */
    public function previewInvoice($reservationId, $invoiceId)
    {
        $this->set(compact('reservationId', 'invoiceId'));
    }

    /**
     * Report Analytics method
     *
     * @return void
     */
    public function reportAnalytics()
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
            $reservations = $this->Authorization->applyScope($this->Reservations->find(), 'index')
                ->where([
                    'Reservations.counter_id' => $this->getRequest()->getQuery('counter'),
                    'Reservations.start' => $aDate,
                ])
                ->contain(['Rooms'])
                ->order('Reservations.no', 'title')
                ->all();
            $counter = $counters[$this->getRequest()->getQuery('counter')];
            $this->set(compact('reservations', 'aDate', 'counter'));

            $this->response = $this->response->withType('application/pdf');
        }
    }
}
