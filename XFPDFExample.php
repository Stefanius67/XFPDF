<?php
require_once 'autoloader.php';
require_once 'XFPDFExample.class.php';

$pdf = new ExampleXPDF();

// set some file information
$pdf->setInfo('XFPDF', 'Example', 'PHP classes', 'Keyword1, Keyword2, ...');
$pdf->setPageHeader('Create PDF Table', 'using extpdf package from PHPClasses.org');

// prepare grid and just insert a bulk of lines
$pdf->prepare();

$date = time();
for ($iRow = 1; $iRow <= 100; $iRow++) {
    // just a simple demo - most cases data comes from DB-query ;-)
    $row = [
        'text' => 'Text in var Col, Line ' . $iRow . ' and should be truncated',
        'weight' => (rand(10, 500) / 10),
        'date' => date('Y-m-d H:i', $date),
        'price' => (rand(10, 2000) / 9),
        'grp_id' => rand(1, 4),
    ];
    $pdf->row($row);
    $date += 24 * 60 * 60;
}
// ...and end of the grid
$pdf->endGrid();

$pdf->createPDF('example');
