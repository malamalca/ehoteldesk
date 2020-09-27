<?php
namespace LilTaxRegisters\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TaxRegistersFixture
 *
 */
class TaxRegistersFixture extends TestFixture
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
        [
            'id' => 'f736e1ef-2b78-4bca-b690-c5ade12b6525',
            'owner_id' => 'a8fe9f50-1e49-45cd-967d-4ee0d916be9b',
            'no' => 'Lorem ipsum dolor ',
            'title' => 'Lorem ipsum dolor sit amet',
            'kind' => '',
            'casadral_number' => 'Lo',
            'building_number' => 'Lor',
            'building_section_number' => 'Lo',
            'street' => 'Lorem ipsum dolor sit amet',
            'house_number' => 'Lorem ip',
            'house_number_additional' => 'Lorem ip',
            'community' => 1,
            'city' => 1,
            'postal_code' => 'Lo',
            'mo_type' => 'Lorem ipsum dolor sit ame',
            'validity_date' => '2016-11-06',
            'closed' => 1,
            'sw_taxno' => 'Lorem ',
            'sw_title' => 'Lorem ipsum dolor sit amet',
            'notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'created' => '2016-11-06 16:34:11',
            'modified' => '2016-11-06 16:34:11'
        ],
    ];
}
