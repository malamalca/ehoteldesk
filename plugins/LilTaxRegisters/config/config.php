<?php
return ['LilTaxRegisters' => [
    'vendorTaxNo' => '55736645',
    'moveableTypes' => [
        'A' => __d('lil_tax_registers', 'Moveable Object'),
        'B' => __d('lil_tax_registers', 'Object at Permantent Location'),
        'C' => __d('lil_tax_registers', 'Individual Electronic Device'),
    ],
    'security' => [
        'p12' => dirname(__FILE__) . DS . 'certs' . DS . '10039953-1.p12',
        'password' => 'Geslo123#',
        'cert' => dirname(__FILE__) . DS . 'certs' . DS . 'sitest-ca.cer',
    ]
]];
