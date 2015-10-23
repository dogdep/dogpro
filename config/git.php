<?php return [
    'private_key' => storage_path('keys/dogpro'),
    'public_key' => storage_path('keys/dogpro.pub'),

    'options' => [
        'environment_variables' => [
            'GIT_SSH' => base_path("scripts/git_ssh.sh"),
            'GIT_SSH_KEY' => storage_path("keys/dogpro"),
        ],
    ]
];
