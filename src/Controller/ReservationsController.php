<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use Lil\View\PdfView;

/**
 * Reservations Controller
 *
 * @property \App\Model\Table\ReservationsTable $Reservations
 */
class ReservationsController extends AppController
{
    /**
     * isAuthorized method.
     *
     * @param array $user User
     * @return bool
     */
    /*public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'index':
            case 'reportAnalytics':
                return true;
            case 'add':
                return $this->userLevel('admin');
            case 'edit':
            case 'delete':
            case 'createInvoice':
                return !empty($this->getRequest()->getParam('pass.0')) && $this->userLevel('admin') &&
                    $this->Reservations->isOwnedBy($this->getRequest()->getParam('pass.0'), $this->getCurrentUser()->get('company_id'));
            case 'view':
            case 'previewInvoice':
                return !empty($this->getRequest()->getParam('pass.0')) &&
                    $this->Reservations->isOwnedBy($this->getRequest()->getParam('pass.0'), $this->getCurrentUser()->get('company_id'));
            default:
                return false;
        }
    }*/

    /**
     * Index method
     *
     * @return \Cake\Network\Response|void
     */
    public function index()
    {
        $this->loadModel('Registrations');

        $filter = $this->getRequest()->getQuery();
        $filter['owner'] = $this->getCurrentUser()->get('company_id');

        $reservations = $this->Reservations->filter('list', $this->Authorization->applyScope($this->Reservations->find()), $filter);
        $registrations = $this->Registrations->filter('list', $this->Authorization->applyScope($this->Registrations->find()), $filter);

        $minYear = $this->Reservations->getMinYear($filter['counter']);
        $rooms = $this->Reservations->Rooms->findForOwner('all', $this->getCurrentUser()->get('company_id'));
        $counters = $this->Reservations->Counters->findForOwner('list', 'V', $this->getCurrentUser()->get('company_id'));

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
     * @return \Cake\Network\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $reservation = $this->Reservations->get($id, [
            'contain' => ['Rooms']
        ]);

        $this->Authorization->authorize($reservation);

        if (Configure::read('useInvoices')) {
            $invoices = TableRegistry::get('LilInvoices.Invoices')->find()
                ->select()
                ->where([
                    'Invoices.owner_id' => $this->getCurrentUser()->get('company_id'),
                    'Invoices.reservation_id' => $reservation->id
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
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->setAction('edit');
    }

    /**
     * Edit method
     *
     * @param string|null $id Reservation id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        if (empty($id)) {
            if ($counterId = $this->getRequest()->getQuery('counter')) {
                $counter = $this->Reservations->Counters->get($counterId);
            } else {
                $counter = $this->Reservations->Counters->findDefaultCounter('V', $this->getCurrentUser()->get('company_id'));
            }

            $reservation = $this->Reservations->newEmptyEntity();
            $reservation->company_id = $this->getCurrentUser()->get('company_id');
            $reservation->counter_id = $counter->id;
            $reservation->room_id = $this->getRequest()->getQuery('room');

            $reservation->no = $this->Reservations->Counters->getNextNo($counter);
        } else {
            $reservation = $this->Reservations->get($id);
        }

        $this->Authorization->authorize($reservation);

        $room = null;
        if ($reservation->room_id && ($room = $this->Reservations->Rooms->get($reservation->room_id))) {
            if ($reservation->isNew()) {
                $reservation->persons = $room->beds;
            }
        }

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $reservation = $this->Reservations->patchEntity($reservation, $this->getRequest()->getData());
            if ($this->Reservations->save($reservation)) {
                $this->Flash->success(__('The reservation has been saved.'));
                if ($referer = $this->getRequest()->getData('referer')) {
                    return $this->redirect($referer);
                }

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The reservation could not be saved. Please, try again.'));
            }
        }

        $rooms = $this->Reservations->Rooms->findForOwner('list', $this->getCurrentUser()->get('company_id'));

        $this->set(compact('reservation', 'room', 'rooms'));
        $this->set('_serialize', ['reservation']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Reservation id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
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
     * @return \Cake\Network\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function createInvoice($id)
    {
        $Invoices = TableRegistry::get('LilInvoices.Invoices');
        $InvoicesCounters = TableRegistry::get('LilInvoices.InvoicesCounters');
        $InvoicesClients = TableRegistry::get('LilInvoices.InvoicesClients');
        $Items = TableRegistry::get('LilInvoices.Items');

        $reservation = $this->Reservations->get($id, ['contain' => ['Counters', 'Rooms' => ['Vats']]]);

        $this->Authorization->authorize($reservation, 'view');

        if (empty($reservation->counter->invoices_counter_id)) {
            $this->Flash->error(__('The invoices counter does not exist.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $counter = $InvoicesCounters->get($reservation->counter->invoices_counter_id);
        if (!$counter) {
            $this->Flash->error(__('The invoices counter does not exist.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $invoice = $Invoices->newEmptyEntity();
        $invoice->title = __('Reservations Invoice');
        $invoice->location = $this->getCurrentUser()->get('company.city');
        $invoice->counter_id = $counter->id;
        $invoice->doc_type = $counter->doc_type;
        $invoice->no = $InvoicesCounters->generateNo($invoice->counter_id);
        $invoice->reservation_id = $reservation->id;
        $invoice->user_id = $this->getCurrentUser()->get('id');
        $invoice->dat_issue = new FrozenDate();
        $invoice->dat_expire = $invoice->dat_issue->addDays(8);
        $invoice->dat_service = $reservation->start;

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
            'vat_id' => isset($reservation->room->vat->id) ? $reservation->room->vat->id : null,
            'vat_title' => isset($reservation->room->vat->descript) ? $reservation->room->vat->descriptid : null,
            'vat_percent' => isset($reservation->room->vat->percent) ? $reservation->room->vat->percent : null,
        ])];

        $vatLevels = TableRegistry::get('LilInvoices.Vats')->levels($this->getCurrentUser()->get('company_id'));
        $this->set(compact('invoice', 'counter', 'vatLevels', 'reservation'));
    }

    /**
     * Patch Client Entity with reservation data
     *
     * @param \Cake\ORM\Entity $client Client Entity
     * @param \Cake\ORM\Entity $reservation Reservation Entity
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
     * @param \Cake\ORM\Entity $issuer Client Entity
     * @return void
     */
    private function _patchWithAuth($issuer)
    {
        $data = $this->getCurrentUser();

        $company = TableRegistry::get('Companies')->get($data->company_id);

        $issuer->kind = 'II';
        $issuer->contact_id = $company->id;
        $issuer->title = $company->name;
        $issuer->mat_no = $company->mat_no;
        $issuer->tax_no = $company->tax_no;

        $issuer->street = $company->street;
        $issuer->city = $company->city;
        $issuer->zip = $company->zip;
        $issuer->country = $company->country;
        $issuer->country_code = $company->country_code;

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
        $CountersTable = TableRegistry::get('Counters');
        $counters = $CountersTable->findForOwner('list', 'V', $this->getCurrentUser()->get('company_id'))->combine('id', 'title')->toArray();;
        $this->set(compact('counters'));

        $this->Authorization->skipAuthorization();

        if ($aDate = $this->getRequest()->getQuery('on')) {
            error_reporting(0);
            $aDate = new FrozenDate($aDate);
            $this->viewBuilder()->setClassName('Lil.Pdf');
            $reservations = $this->Authorization->applyScope($this->Reservations->find(), 'index')
                ->where([
                    'Reservations.counter_id' => $this->getRequest()->getQuery('counter'),
                    'Reservations.start' => $aDate
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
