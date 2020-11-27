<?php
use SKien\XFPDF\XPDF;

/**
 * example to use the XPDF - class:
 * first, define own class extending XPDF to define table coloumns und to handle
 * the output of dynamic content.
 * 
 * @author Stefanius
 */
class ExampleXPDF extends XPDF
{
    /** const for own column ID's */
    const MY_GRP_COL = 1;
    const MY_IMAGE_COL = 2;
    const MY_CALC_COL = 3;
    
    /** @var string remember month and year of the previous row */ 
    protected string $strMonth = '';
    
    /**
     * define table columns
     * @param string $orientation
     */
    public function __construct(string $orientation='P') 
    {
        // first call parent constructor for general initialization
        parent::__construct($orientation);
        
        // next we can specify the fonts and colors to use
        // This can be done by using the various font and color functions.
        // - SetXXXFont()
        // - SetXXXColors()
        //
        // However, it is faster and easier if all settings are made in a JSON file.
        // (and above reusable for multiple reports).
        // The file name have to be passed to the InitGrid() method.
        //
        // The structure of the JSON file is self-explanatory and can be seen in the example file
        // used here.
        $this->InitGrid('xfpdf-sample.json');
        
        // set Logo printed in the page header
        $this->SetLogo('images/elephpant.png');
        $this->SetLogoHeight(9.0);
        $this->SetPageFooter("Page: {PN}/{NP}\tAuthor: S.Kien\t{D} {T}");
        
        // now we define the columns of our report
        $this->AddCol('Row',            10, 'R', XPDF::COL_ROW_NR,     XPDF::FLAG_TOTALS_TEXT);
        $this->AddCol('Date',           35, 'C', 'date',               XPDF::FLAG_DATE);
        $this->AddCol('Text',           -1, 'L', 'text');
        $this->AddCol('Grp.',           12, 'C', self::MY_GRP_COL);
        $this->AddCol('Weight',         20, 'R', 'weight',             XPDF::FLAG_TOTALS_CALC | XPDF::FLAG_NUMBER);
        $iImgCol = $this->AddCol(-1,     8, 'C', self::MY_IMAGE_COL,   XPDF::FLAG_IMAGE | XPDF::FLAG_TOTALS_EMPTY);
        $this->AddCol('Price',          25, 'R', 'price',              XPDF::FLAG_TOTALS_CALC | XPDF::FLAG_CUR_SYMBOL);
        $this->AddCol('Cost per kg',    25, 'R', self::MY_CALC_COL,    XPDF::FLAG_TOTALS_EMPTY);
        
        // enable the totals/pagetotals and carry-over functionality
        $this->EnableTotals(XPDF::TOTALS | XPDF::PAGE_TOTALS | XPDF::CARRY_OVER);
        $this->SetTotalsText(
            "My Totals over all:",
            "Subtotal on Page {PN}:",
            "Carry over from Page {PN-1}:");
        
        // set date and number formating.
        $this->SetDateFormat('%a, %d.%m.%Y');
        $this->SetNumberFormat(1, '', ' kg');
        
        // and set meassuring for the image col
        $this->SetColImageInfo($iImgCol, 1.5, 2.5, 3 );
    }
    
    /**
     * handle special content of cell 
     * - any calculation
     * - formating
     * - ...
     * not included in raw data 
     * 
     * !! Important !!
     * dont't forget to call parent::Col($iCol, $row, $bFill) if data not processed... 
     * 
     * (non-PHPdoc)
     * @see XPDF::Col()
     */
    protected function Col(int $iCol, array $row, bool &$bFill) : string 
    {
        $strCol = '';
        switch ($iCol) {
            case self::MY_GRP_COL:
                $aValues = array( '', 'Grp. A', 'Grp. B', 'Grp. C', 'Grp. D');
                if ($row['grp_id'] > 0 && $row['grp_id'] <= 4) {
                    $strCol = $aValues[$row['grp_id']];
                }
                break;
            case self::MY_IMAGE_COL:
                $strCol = 'images/';
                $fltWeight = floatval($row['weight']);
                if ($fltWeight > 35.0) {
                    // ... to heavy
                    $strCol .= 'red.png';
                } else if ($fltWeight > 20.0) {
                    // ... just in the limit
                    $strCol .= 'yellow.png';
                } else {
                    $strCol .= 'green.png';
                }
                break;
            case self::MY_CALC_COL:
                $fltPricePerKg = 0.0;
                if (floatval($row['weight']) != 0) {
                    $fltPricePerKg = floatval($row['price']) / floatval($row['weight']);;
                }
                $strCol = $this->_FormatCurrency($fltPricePerKg, true);
                break;
            default:
                // very important to call parent class !!
                $strCol = parent::Col($iCol, $row, $bFill);
                break;
        }
        return $strCol;
    }
    
    /**
     * the PreRow() method may be used to
     * - process internal state or properties  
     * - start of grouping
     * - manipulate data of next row
     * - insert some separator or subtitle within the grid
     * 
     * pay attention on $row param is passed as reference so any changes affects
     * further output.  
     * 
     * (non-PHPdoc)
     * @see XPDF::PreRow()
     */
    protected function PreRow(array &$row) : string
    {
        // for grouping
        $date = strtotime($row['date']);
        $strMonth = date('Y-m', $date);
        if ( $this->strMonth != $strMonth) {
            // first row we have no subtotals...
            if ($this->strMonth != '') {
                $this->EndGroup();
            }
            $this->StartGroup('Totals ' . strftime('%B %Y', $date) . ':', strftime('%B %Y', $date));
            $this->strMonth = $strMonth;
        }
        $strSubRow = '';
        if ($this->iRow == 47) {
            $strSubRow = '... next Row have been manipulated in ExampleXPDF::PreRow(array &$row)!';
            $row['text'] = 'manipulated Rowdata!';
        }
        if ($this->iRow == 56) {
            $row['text'] = 'manipulated Rowdata without Subrow!';
        }
        return $strSubRow;
    }
    
    /**
     * Overwrite the EndGrid() method to close the last group.
     * {@inheritDoc}
     * @see \SKien\XFPDF\XPDF::EndGrid()
     */
    public function EndGrid() : void
    {
        // end last group for subtotals before we call the parent (!!! don't forget that!!)
        $this->EndGroup();
        parent::EndGrid();
    }
}
