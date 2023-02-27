<?php
namespace SKien\XFPDF;

/**
 * Helper class to hold information for a font in a pdf document
 *
 * @package SKien/XFPDF
 * @since 1.1.0
 * @version 1.1.0
 * @author Stefanius <s.kientzler@online.de>
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
