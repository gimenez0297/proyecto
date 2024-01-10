<?php
$duplicados = [
    "47400179240" => [
        "cantidad" => 2,
        "id_producto" => [
            213,
            2715
        ],
        "codigo_actual" => [
            100212,
            102715
        ]
    ],
    "42207573" => [
        "cantidad" => 2,
        "id_producto" => [
            381,
            6238
        ],
        "codigo_actual" => [
            100380,
            408573
        ]
    ],
    "7501001156176" => [
        "cantidad" => 2,
        "id_producto" => [
            792,
            4215
        ],
        "codigo_actual" => [
            100791,
            104215
        ]
    ],
    "79400110695" => [
        "cantidad" => 2,
        "id_producto" => [
            2565,
            2566
        ],
        "codigo_actual" => [
            102565,
            102566
        ]
    ],
    "47400179660" => [
        "cantidad" => 2,
        "id_producto" => [
            2717,
            4294
        ],
        "codigo_actual" => [
            102717,
            104294
        ]
    ],
    "7591083011098" => [
        "cantidad" => 2,
        "id_producto" => [
            3282,
            4073
        ],
        "codigo_actual" => [
            103282,
            104073
        ]
    ],
    "6914600213903" => [
        "cantidad" => 2,
        "id_producto" => [
            3766,
            4373
        ],
        "codigo_actual" => [
            103766,
            104373
        ]
    ],
    "2588329" => [
        "cantidad" => 2,
        "id_producto" => [
            5471,
            7059
        ],
        "codigo_actual" => [
            105471,
            409395
        ]
    ],
    "42360292" => [
        "cantidad" => 2,
        "id_producto" => [
            6057,
            6237
        ],
        "codigo_actual" => [
            106057,
            408572
        ]
    ],
    "77043355862" => [
        "cantidad" => 2,
        "id_producto" => [
            6183,
            7549
        ],
        "codigo_actual" => [
            408518,
            409885
        ]
    ],
    "7795373011052" => [
        "cantidad" => 2,
        "id_producto" => [
            11607,
            24304
        ],
        "codigo_actual" => [
            705160,
            719881
        ]
    ]
];

$no_validos = [
    "CP1102" => [
        "cantidad" => 1,
        "id_producto" => [
            2718
        ],
        "codigo_actual" => [
            102718
        ]
    ],
    "780006+013811" => [
        "cantidad" => 1,
        "id_producto" => [
            26504
        ],
        "codigo_actual" => [
            722101
        ]
    ]
];

echo "<b><u>Duplicados</u></b>";
var_dump($duplicados);
echo "<b><u>No validos</u></b>";
var_dump($no_validos);

