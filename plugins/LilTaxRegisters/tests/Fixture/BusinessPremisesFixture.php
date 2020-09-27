<?php
namespace LilTaxRegisters\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BusinessPremisesFixture
 *
 */
class BusinessPremisesFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'owner_id' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'no' => ['type' => 'string', 'fixed' => true, 'length' => 20, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null],
        'title' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'kind' => ['type' => 'string', 'fixed' => true, 'length' => 2, 'null' => false, 'default' => 'RL', 'collate' => 'utf8mb4_unicode_ci', 'comment' => 'ReaLestate or MOvable', 'precision' => null],
        'casadral_number' => ['type' => 'string', 'fixed' => true, 'length' => 4, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null],
        'building_number' => ['type' => 'string', 'fixed' => true, 'length' => 5, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null],
        'building_section_number' => ['type' => 'string', 'fixed' => true, 'length' => 4, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null],
        'street' => ['type' => 'string', 'length' => 100, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'house_number' => ['type' => 'string', 'length' => 10, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'house_number_additional' => ['type' => 'string', 'length' => 10, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'community' => ['type' => 'integer', 'length' => 100, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'city' => ['type' => 'integer', 'length' => 40, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'postal_code' => ['type' => 'string', 'fixed' => true, 'length' => 4, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null],
        'mo_type' => ['type' => 'string', 'fixed' => true, 'length' => 1, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null],
        'validity_date' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'closed' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'sw_taxno' => ['type' => 'string', 'length' => 8, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'sw_title' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'notes' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [

    ];
}
