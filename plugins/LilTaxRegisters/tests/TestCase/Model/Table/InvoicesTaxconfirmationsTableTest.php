<?php
namespace LilTaxRegisters\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use LilTaxRegisters\Model\Table\InvoicesTaxconfirmationsTable;

/**
 * LilTaxRegisters\Model\Table\InvoicesTaxconfirmationsTable Test Case
 */
class InvoicesTaxconfirmationsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \LilTaxRegisters\Model\Table\InvoicesTaxconfirmationsTable
     */
    public $InvoicesTaxconfirmations;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.lil_tax_registers.invoices_taxconfirmations',
        'plugin.lil_invoices.invoices'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('InvoicesTaxconfirmations') ? [] : ['className' => 'LilTaxRegisters\Model\Table\InvoicesTaxconfirmationsTable'];
        $this->InvoicesTaxconfirmations = TableRegistry::get('InvoicesTaxconfirmations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->InvoicesTaxconfirmations);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
