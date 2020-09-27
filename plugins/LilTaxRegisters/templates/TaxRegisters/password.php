<?php
$PKPasswordForm = [
	'title_for_layout' => __d('lil_tax_registers', 'Private Key Password'),
	'form' => [
		'pre' => '<div class="form">',
		'post' => '</div>',
		'defaultHelper' => $this->Form,
		'lines' => [
			'form_start' => [
				'method' => 'create',
				'parameters' => [$PKPassword]
			],
			'referer' => [
				'method' => 'hidden',
				'parameters' => ['referer', ['default' => $this->request->referer()]]
			],
			'password' => [
				'method' => 'control',
				'parameters' => ['password', [
					'type' => 'password',
					'label' => __d('lil_tax_registers', 'Password') . ':',
				]]
			],
			'submit' => [
				'method' => 'submit',
				'parameters' => [
					'label' => __d('lil_tax_registers', 'Send')
				]
			],
			'form_end' => [
				'method' => 'end',
				'parameters' => []
			],
		]
	]
];

echo $this->Lil->form($PKPasswordForm, 'LilTaxRegisters.TaxRegisters.password');
