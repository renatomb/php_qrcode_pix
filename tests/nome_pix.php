<?php

require __DIR__ . '/../funcoes_pix.php';

$cases = [
    'JOAO  SILVA' => 'JOAO  SILVA',
    'Fulano de Tal' => 'Fulano de Tal',
    'BR.GOV.BCB.PIX' => 'BR.GOV.BCB.PIX',
    '+5599888887777' => '+5599888887777',
    'São Paulo' => 'Sao Paulo',
];

foreach ($cases as $in => $want) {
    $got = remove_char_especiais($in);
    if ($got !== $want) {
        fwrite(STDERR, "[$in] returned [$got], expected [$want]\n");
        exit(1);
    }
}

$pix = montaPix([59 => 'JOAO  SILVA']);
if ($pix !== '5911JOAO  SILVA') {
    fwrite(STDERR, "merchant name field was [$pix]\n");
    exit(1);
}

$amount = montaPix([54 => '10.1']);
if ($amount !== '540510.10') {
    fwrite(STDERR, "amount field was [$amount]\n");
    exit(1);
}

echo "ok\n";
