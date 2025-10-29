<?php
$callbacks = [
    [
        'hook' => \mod_assign\hook\marking_agreement_get_names::class,
        'callback' => [\mod_assign\markingagreement\first::class, 'respond_to_get_names'],
        'priority' => 500,
    ],
    [
        'hook' => \mod_assign\hook\marking_agreement_get_names::class,
        'callback' => [\mod_assign\markingagreement\max::class, 'respond_to_get_names'],
        'priority' => 500,
    ],
    [
        'hook' => \mod_assign\hook\marking_agreement_get_names::class,
        'callback' => [\mod_assign\markingagreement\average::class, 'respond_to_get_names'],
        'priority' => 500,
    ],
    [
        'hook' => \mod_assign\hook\marking_agreement_get_names::class,
        'callback' => [\mod_assign\markingagreement\manual::class, 'respond_to_get_names'],
        'priority' => 500,
    ],
];