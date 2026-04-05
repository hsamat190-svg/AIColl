<?php

return [
    'materials' => [
        'steel' => ['restitution' => 0.92, 'label' => 'Steel'],
        'rubber' => ['restitution' => 0.75, 'label' => 'Rubber'],
        'clay' => ['restitution' => 0.05, 'label' => 'Clay (inelastic)'],
        'ice' => ['restitution' => 0.15, 'label' => 'Ice'],
    ],
    'scenario' => [
        'm_min' => 0.5,
        'm_max' => 5.0,
        'v_max' => 8.0,
        'restitution_min' => 0.2,
        'restitution_max' => 1.0,
    ],
];
