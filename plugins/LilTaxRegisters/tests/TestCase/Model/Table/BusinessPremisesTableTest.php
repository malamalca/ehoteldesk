<?php
namespace LilTaxRegisters\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use LilTaxRegisters\Model\Table\BusinessPremisesTable;

/**
 * LilTaxRegisters\Model\Table\BusinessPremisesTable Test Case
 */
class BusinessPremisesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \LilTaxRegisters\Model\Table\BusinessPremisesTable
     */
    public $BusinessPremises;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.lil_tax_registers.business_premises',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('BusinessPremises') ? [] : ['className' => 'LilTaxRegisters\Model\Table\BusinessPremisesTable'];
        $this->BusinessPremises = TableRegistry::get('BusinessPremises', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->BusinessPremises);

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
