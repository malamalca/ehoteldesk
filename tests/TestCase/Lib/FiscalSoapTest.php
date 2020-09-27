<?php
use Cake\TestSuite\TestCase;
use App\Lib\FiscalSoap;

/**
 * FiscalSoap Test Case
 */
class FiscalSoapTest extends TestCase
{

    /**
     * Test subject
     *
     * @var Malamalca\FiscalPHP\FiscalSoap
     */
    public $FiscalSoap;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [];

    /**
     * Path to certificates
     *
     * @var string
     */
    private $resourcesPath = '';

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->resourcesPath = dirname(dirname(dirname(__FILE__))) . DS . 'resources' . DS;

        $this->FiscalSoap = new FiscalSoap();
        $this->FiscalSoap->setP12($this->resourcesPath . '10039953-1.p12');
        $this->FiscalSoap->setPassword('Geslo123#');
        $this->FiscalSoap->setCert($this->resourcesPath . 'sitest-ca.cer');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->FiscalSoap);

        parent::tearDown();
    }

    /**
     * Test Echo method
     *
     * @return void
     */
    public function testEcho()
    {
        $result = $this->FiscalSoap->sendEcho('ping');
        $this->assertEquals('ping', $result);
    }

    /**
     * Test sendPremise method
     *
     * @return void
     */
    public function testSendPremise()
    {
        $result = $this->FiscalSoap->sendPremise(file_get_contents($this->resourcesPath . 'premise_signed.xml'));
        $this->assertTrue($result);
    }

    /**
     * Test sendInvoice method
     *
     * @return void
     */
    public function testSendInvoice()
    {
        $result = $this->FiscalSoap->sendInvoice(file_get_contents($this->resourcesPath . 'invoice_signed.xml'));
        $this->assertNotEmpty($result);
    }
}
