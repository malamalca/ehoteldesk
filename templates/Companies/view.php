<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

$companyView = [
    'title_for_layout' => __('Company'),
    'menu' => [
        'edit' => [
            'title' => __('Edit'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'edit',
                $company->id,
            ]
        ],
        'delete' => [
            'title' => __('Delete'),
            'visible' => $this->getCurrentUser()->hasRole('root'),
            'url' => [
                'action' => 'delete',
                $company->id,
            ],
            'params' => [
                'confirm' => __('Are you sure you want to delete this company?')
            ]
        ],
        'add_user' => [
            'title' => __('Add User'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'controller' => 'Users',
                'action' => 'add',
                '?' => ['company' => $company->id]
            ],
        ],
    ],
    'entity' => $company,
    'panels' => [
        'company' => [
            'lines' => [
                'title' => sprintf('<h3>%s</h3>',  h($company->name)),
                'address' => h($company->street) . ', ' . h($company->zip) . ' ' . h($company->city),

            ],
        ],

        'users' => [
            'lines' => [
                sprintf('<h3>%s</h3>', __('Users')),
                'users' => ['table' => [
                    'parameters' => ['class' => 'index-static table'],
                    'head' => ['rows' => [0 => ['columns' => [
                        __('Name'),
                        __('Username'),
                        __('Email'),
                        __('Privileges'),
                        __('ETurizem'),
                        ''
                    ]]]]
                ]]
            ],
        ],
    ]
];

foreach ($company->users as $user) {
    $companyView['panels']['users']['lines']['users']['table']['body']['rows'][] = ['columns' => [
        h($user->name),
        h($user->username),
        h($user->email),
        (string)$user->privileges . ' ',
        h($user->etur_username) . ' ',
        $this->Lil->editLink(['controller' => 'Users', 'action' => 'edit', $user->id]) .
        $this->Lil->deleteLink(['controller' => 'Users', 'action' => 'delete', $user->id])
    ]];
}

echo $this->Lil->panels($companyView, 'Companies.view');
