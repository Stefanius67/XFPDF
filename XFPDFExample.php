<?php
require_once 'autoloader.php';
require_once 'XFPDFExample.class.php';

$pdf = new ExampleXPDF();

// set some file information
$pdf->SetInfo('XFPDF', 'Example', 'PHP classes', 'Keyword1, Keyword2, ...');
$pdf->SetPageHeader('Create PDF Table', 'using extpdf package from PHPClasses.org');

// prepare grid and just insert a bulk of lines
$pdf->Prepare();

$date = time();
for ($iRow = 1; $iRow <= 100; $iRow++) {
    // just a simple demo - most cases data comes from DB-query ;-)
    $row = array(
        'text' => 'Text in var Col, Line ' . $iRow,
        'weight' => (rand(10, 500) / 10),
        'date' => date('Y-m-d', $date),
        'price' => (rand(10, 2000) / 9),
        'grp_id' => rand(1, 4)
    );
    $pdf->Row($row);
    $date += 24 * 60 * 60;
}
// ...and end of the grid
$pdf->EndGrid();

$pdf->CreatePDF('example');
