<?php
use Cake\TestSuite\TestCase;
use App\Lib\FiscalSign;

/**
 * App\FiscalSign Test Case
 */
class FiscalSignTest extends TestCase
{

    /**
     * Test subject
     *
     * @var Malamalca\FiscalPHP\FiscalSign
     */
    public $FiscalSign;

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

        $this->FiscalSign = new FiscalSign();
        $this->FiscalSign->setP12($this->resourcesPath . '/10039953-1.p12');
        $this->FiscalSign->setPassword('Geslo123#');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->FiscalSign);

        parent::tearDown();
    }

    /**
     * Test Sign Premise method
     *
     * @return void
     */
    public function testSignPremise()
    {
        $signed = $this->FiscalSign->sign(file_get_contents($this->resourcesPath . 'premise.xml'), 'fu:BusinessPremiseRequest');
        $this->assertEquals(file_get_contents($this->resourcesPath . 'premise_signed.xml'), $signed);
    }

    /**
     * Test Sign Invoice method
     *
     * @return void
     */
    public function testSignInvoice()
    {
        $signed = $this->FiscalSign->sign(file_get_contents($this->resourcesPath . 'invoice.xml'), 'fu:InvoiceRequest');
        $this->assertEquals(file_get_contents($this->resourcesPath . 'invoice_signed.xml'), $signed);
    }
}
