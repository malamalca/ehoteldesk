<?php
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
    use Cake\Event\EventManager;
    use Cake\Log\Log;

    use LilTaxRegisters\Event\LilTaxRegistersEvents;

    Configure::load('LilTaxRegisters.config');

    $LilTaxRegistersEvents = new LilTaxRegistersEvents();
    EventManager::instance()->on($LilTaxRegistersEvents);

    Log::setConfig('taxRegister', [
        'className' => 'File',
        'path' => LOGS,
        'levels' => [],
        'scopes' => ['taxrSign', 'taxrSoap'],
        'file' => 'tax_registers.log',
    ]);

    define('CONFIRMATION_ERROR_SIGN', -1);
    define('CONFIRMATION_ERROR_SOAP', -2);
    define('CONFIRMATION_ERROR_REQUEST', -3);
    define('CONFIRMATION_ERROR_XML', -4);
