<?php
namespace SKien\XFPDF;

/**
 * Helper class to hold information for a font in a pdf document
 * 
 * #### History
 * - *2020-11-18*   Moved into a separate file to correspond to PSR-0 / PSR-4 (one file per class) 
 * - *2020-11-18*   set namespace to fit PSR-4 recommendations for autoloading.
 * - *2020-11-18*   added missing PHP 7.4 type hints / docBlock changes 
 *
 * @package SKien/XFPDF
 * @since 1.1.0
 * @version 1.1.0
 * @author Stefanius <s.kien@online.de>
 * @copyright MIT License - see the LICENSE file for details
 */
class XPDFFont
{
    /** @var string fontname	 */
    public string $strFontname;
    /** @var int size	 */
    public int $iSize;
    /** @var string style	 */
    public string $strStyle;
    
    /**
     * Creates a Font-object
     * @param string $strFontname
     * @param string $strStyle		'B', 'I' or 'BI'
     * @param int $iSize
     */
    function __construct(string $strFontname, string $strStyle, int $iSize)
    {
        $this->strFontname = $strFontname;
        $this->strStyle = $strStyle;
        $this->iSize = $iSize;
    }
}
