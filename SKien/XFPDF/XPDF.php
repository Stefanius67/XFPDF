<?php
declare(strict_types=1);

namespace SKien\XFPDF;

use OPlathey\FPDF\FPDF;
use IntlDateFormatter;

/**
 * Extension of FPDF-Class to generate table/datagrid.
 * Supports: <ul>
 * <li> Page Header/Footer including Logo </li>
 * <li> Colheaders </li>
 * <li> Totals, Subtotals, Pagetotals and Carry over </li>
 * <li> Use defined fonts, colors and stripped datarows </li>
 * <li> Specify print format with JSON template file </li></ul>
 *
 * @package SKien/XFPDF
 * @version 2.0.0
 * @author Stefanius <s.kientzler@online.de>
 * @copyright MIT License - see the LICENSE file for details
 */
class XPDF extends FPDF
{
    /** predifined Col-ID for automated row number */
    public const COL_ROW_NR = 1000;

    /** Bottom margin for trigger of the auto pagebreak */
    public const BOTTOM_MARGIN = 12;

    /** totals info                         */
    public const FLAG_TOTALS = 0x0007;
    /** calc total for column               */
    public const FLAG_TOTALS_CALC = 0x0001;
    /** print text in totals row            */
    public const FLAG_TOTALS_TEXT = 0x0002;
    /** leave empty in totals row           */
    public const FLAG_TOTALS_EMPTY = 0x0004;
    /** create internal link                */
    public const FLAG_INT_LINK = 0x0008;
    /** special format for the cell         */
    public const FLAG_FORMAT = 0x00F0;
    /** format cell as currency with symbol */
    public const FLAG_CUR_SYMBOL = 0x0010;
    /** format cell as currency without symbol  */
    public const FLAG_CUR_PLAIN = 0x0020;
    /** format cell as date/time            */
    public const FLAG_DATE = 0x0030;
    /** format cell as date/time            */
    public const FLAG_TIME = 0x0040;
    /** format cell as date/time            */
    public const FLAG_DATE_TIME = 0x0050;
    /** format cell as number               */
    public const FLAG_NUMBER = 0x0060;
    /** format cell IBAN                    */
    public const FLAG_IBAN = 0x0070;
    /** limit length to width with elipsis  */
    public const FLAG_ELIPSIS = 0x0080;
    /** cell containes image                */
    public const FLAG_IMAGE = 0x0100;
    /** suppress zero values                */
    public const FLAG_NO_ZERO = 0x0200;


    /** crate totals row on the end of report  */
    public const TOTALS = 0x01;
    /** create totals row on each pagebreak    */
    public const PAGE_TOTALS = 0x02;
    /** create carry over on the beginning of each new page    */
    public const CARRY_OVER = 0x04;
    /** create     */
    public const SUB_TOTALS = 0x08;

    /** default format for date cells */
    protected const DEF_DATE_FORMAT = IntlDateFormatter::MEDIUM;
    /** default format for time cells */
    protected const DEF_TIME_FORMAT = IntlDateFormatter::SHORT;

    /** @var string pageheader  */
    protected string $strPageTitle;
    /** @var string logo    */
    protected string $strLogo;
    /** @var float height of the loge in user units    */
    protected float $fltLogoHeight;
    /** @var string subject in page header  */
    protected string $strPageSubject;
    /** @var string pagefooter  */
    protected string $strPageFooter;
    /** @var XPDFFont font to use in header of the document */
    protected XPDFFont $fontHeader;
    /** @var XPDFFont font to use for subject in the header of the document */
    protected XPDFFont $fontSubject;
    /** @var XPDFFont font to use in footer of the document */
    protected XPDFFont $fontFooter;
    /** @var XPDFFont font to use in col headers of the grid    */
    protected XPDFFont $fontColHeader;
    /** @var XPDFFont font to use in sub headers of the grid    */
    protected XPDFFont $fontSubHeader;
    /** @var XPDFFont font to use in rows of a grid */
    protected XPDFFont $fontRows;
    /** @var string textcolor to use in header of the document  */
    protected string $strHeaderTextColor = '#000000';
    /** @var string textcolor to use in footer of the document  */
    protected string $strFooterTextColor = '#000000';
    /** @var string textcolor to use in colheader of the document   */
    protected string $strColHeaderTextColor = '#00007F';
    /** @var string textcolor to use in subheader of the document   */
    protected string $strSubHeaderTextColor = '#000000';
    /** @var string textcolor to use in rows of the document    */
    protected string $strRowTextColor = '#000000';
    /** @var string textcolor to use for internal links */
    protected string $strLinkTextColor = '#0000FF';
    /** @var string drawcolor to use in header of the document  */
    protected string $strHeaderDrawColor = '#404040';
    /** @var string drawcolor to use in footer of the document  */
    protected string $strFooterDrawColor = '#404040';
    /** @var string fillcolor to use in colheader of the document   */
    protected string $strColHeaderFillColor = '#D7D7D7';
    /** @var string fillcolor to use in subheader of the document   */
    protected string $strSubHeaderFillColor = '#A7A7A7';
    /** @var bool   strip datarows for better contrast   */
    protected bool $bStripped = true;
    /** @var string drawcolor to use in rows of the document (striped)  */
    protected string $strRowDrawColor = '#404040';
    /** @var string fillcolor to use in rows of the document (striped)  */
    protected string $strRowFillColor = '#E0EBFF';
    /** @var bool   currently inside of of grid      */
    protected bool $bInGrid = false;
    /** @var int|string border   */
    protected $border = 1;
    /** @var int        index of last col    */
    protected int $iMaxCol = -1;
    /** @var int        index of last title col (in case of colspans in header < $iMaxCol        */
    protected int $iMaxColHeader = -1;
    /** @var array      titles for the table header      */
    protected array $aColHeader = Array();
    /** @var array      width of each col in percent         */
    protected array $aColWidth = Array();
    /** @var array      align of each datacol (header always center)         */
    protected array $aColAlign = Array();
    /** @var array      flags for each datacol       */
    protected array $aColFlags = Array();
    /** @var array      fieldname or number of each datacol      */
    protected array $aColField = Array();
    /** @var array      colspan of the headercols        */
    protected array $aColSpan  = Array();
    /** @var array      info for image cols      */
    protected array $aImgInfo  = Array();
    /** @var bool       enable automatic calculation of totals       */
    protected bool $bCalcTotals = false;
    /** @var string     text for totals      */
    protected string $strTotals = 'Total:';
    /** @var bool       print subtotals on each pagebreak        */
    protected bool $bPageTotals = false;
    /** @var string     text for subtotals       */
    protected string $strPageTotals = '';
    /** @var bool       print carry over on top of each new page         */
    protected bool $bCarryOver = false;
    /** @var string     text for carry over      */
    protected string $strCarryOver = 'Carry over:';
    /** @var string     text for subtotals       */
    protected string $strSubTotals = 'Subtotal:';
    /** @var int        index of last totals col         */
    protected int $iMaxColTotals = -1;
    /** @var array      calculated totals        */
    protected array $aTotals = Array();
    /** @var array      calculated subtotals         */
    protected array $aSubTotals = Array();
    /** @var array      colspan of the totals        */
    protected array $aTotalsColSpan = Array();
    /** @var int        current rownumber    */
    protected int $iRow;
    /** @var float      lineheight in mm     */
    protected float $fltLineHeight = 8.0;
    /** @var string      */
    protected ?string $strLocale = null;
    /** @var IntlDateFormatter the formatter for date cells   */
    protected IntlDateFormatter $oDateFormatter;
    /** @var IntlDateFormatter the formatter for time cells   */
    protected IntlDateFormatter $oTimeFormatter;
    /** @var IntlDateFormatter the formatter for datetime cells   */
    protected IntlDateFormatter $oDTFormatter;
    /** @var int        decimals for number format       */
    protected int $iNumberDecimals = 2;
    /** @var string     prefix for number format */
    protected string $strNumberPrefix = '';
    /** @var string     suffix for number format */
    protected string $strNumberSuffix = '';
    /** @var bool       setlocale() not called or returned false!   */
    protected bool $bInvalidLocale = true;
    /** @var bool       Multiline rows!   */
    protected bool $bMultiline = false;

    /**
     * class constructor.
     * allows to set up the page size, the orientation and the unit of measure used in all methods (except for font sizes).
     * @param string $orientation
     * @param string $unit
     * @param string|array $size
     * @see FPDF::__construct()
     */
    public function __construct(string $orientation = 'P', string $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);

        $this->setDisplayMode('fullpage', 'single');
        $this->setAutoPageBreak(true, self::BOTTOM_MARGIN);
        $this->aliasNbPages('{NP}');
        $this->setLocale("en_US.utf8, en_US");

        $this->strPageTitle = '';
        $this->strPageFooter = "{PN}/{NP}\t{D} {T}";
        $this->strLogo = '';
        $this->fltLogoHeight = 8.0;

        $this->iRow = 0;

        $this->fontHeader = new XPDFFont('Arial', 'B', 12);
        $this->fontSubject = new XPDFFont('Arial', 'I', 8);
        $this->fontFooter = new XPDFFont('Arial', 'I', 8);
        $this->fontColHeader = new XPDFFont('Arial', 'B', 10);
        $this->fontSubHeader = new XPDFFont('Arial', 'B', 10);
        $this->fontRows = new XPDFFont('Arial', '', 10);

        $this->oDateFormatter = new IntlDateFormatter($this->strLocale, self::DEF_DATE_FORMAT, IntlDateFormatter::NONE);
        $this->oTimeFormatter = new IntlDateFormatter($this->strLocale, IntlDateFormatter::NONE, self::DEF_TIME_FORMAT);
        $this->oDTFormatter = new IntlDateFormatter($this->strLocale, self::DEF_DATE_FORMAT, self::DEF_TIME_FORMAT);
    }

    /**
     * Set information for document to create.
     * @param string $strTitle
     * @param string $strSubject
     * @param string $strAuthor
     * @param string $strKeywords
     * @param bool $isUTF8  Indicates if the strings encoded in ISO-8859-1 (false) or UTF-8 (true). (Default: true)
     */
    public function setInfo(string $strTitle, string $strSubject = '', string $strAuthor = '', string $strKeywords = '', bool $isUTF8 = true) : void
    {
        $this->setTitle($strTitle, $isUTF8);
        $this->setSubject($strSubject, $isUTF8);
        $this->setAuthor($strAuthor, $isUTF8);
        $this->setKeywords($strKeywords, $isUTF8);
        $this->setCreator('FPDF - Dokument Generator');
    }

    /**
     * Set locale for formating.
     * If $strLocale is an comma separated list each value is tried to be
     * set as new locale until success. <br/>
     * Example: <i>"de_DE.utf8, de_DE, de, DE"</i><br/>
     * <br/>
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @param string $strLocale
     * @see XPDF::initGrid()
     * @link http://www.php.net/manual/en/function.setlocale.php
     */
    public function setLocale(string $strLocale) : void
    {
        if ($this->strLocale != $strLocale) {
            $this->strLocale = $strLocale;

            // if locale contains multiple coma separated values, just explode and trim...
            $locale = $this->strLocale;
            if (strpos($this->strLocale, ',')) {
                $locale = array_map('trim', explode(',', $this->strLocale));
            }
            $this->bInvalidLocale = false;
            if (setlocale(LC_ALL, $locale) === false) {
                trigger_error('setlocale("' . $this->strLocale . '") failed! Check if the requested language is available on the server!', E_USER_NOTICE);
                $this->bInvalidLocale = true;
            }
        }
    }

    /**
     * Set the pageheader of the document.
     * The title is printed in the left of the pageheader using the font set with XPDF:SetHeaderFont() <br/>
     * Optional the subject and/or the logo can be set.
     * Subject and logo can also be set using XPDF:SetSubject() and XPDF:SetLogo() <br/>
     * The page header is separated from the report by a double line.
     * @param string $strTitle         Title of the Report
     * @param string $strHeaderSubject Subject of the Report
     * @param string $strLogo          Logo to print.
     * @see XPDF:SetHeaderFont()
     * @see XPDF:SetSubject()
     * @see XPDF:SetLogo()
     */
    public function setPageHeader(string $strTitle, string $strHeaderSubject = '', string $strLogo = '') : void
    {
        $this->strPageTitle = $strTitle;
        if (strlen($strLogo) > 0) {
            $this->strLogo = $strLogo;
        }
        $this->strPageSubject = $strHeaderSubject;
    }

    /**
     * Set the subject in the pageheader of the document.
     * The subject is printed in the left of the pageheader in the 2'nd line using the font set
     * with XPDF:SetSubjectFont() <br/>
     * @param string $strPageSubject
     * @see XPDF:SetSubjectFont()
     */
    public function setPageSubject(string $strPageSubject) : void
    {
        $this->strPageSubject = $strPageSubject;
    }

    /**
     * Set logo printed in the document header.
     * The logo is printed right-aligned in the header, and by default,  the logo will be
     * scaled to a height of 8mm. Another height can be set with XPDF::setLogoHeight(). <br/>
     * For convinience, the loge can be set directly within XPDF::setPageHeader().
     * @param string $strLogo  image file to print.
     * @see XPDF::setLogoHeight()
     * @see XPDF::setPageHeader()
     */
    public function setLogo(string $strLogo) : void
    {
        $this->strLogo = $strLogo;
    }

    /**
     * Set height of the logo in the document header.
     * @param float $fltLogoHeight height of the logo image
     */
    public function setLogoHeight(float $fltLogoHeight) : void
    {
        $this->fltLogoHeight = $fltLogoHeight;
    }

    /**
     * Set the pagefooter of the document.
     * @param string $strFooter     The footer can consist of up to three sections delimitet by TAB <b>('\t')</b><br/>
     *      possible placeholders are <ul>
     *      <li> '{D}'  -> current date (DD.MM.YYYY) </li>
     *      <li> '{T}'  -> current time (HH:ii) </li>
     *      <li> '{PN}' -> pagenumber </li>
     *      <li> '{NP}' -> total number of pages </li></ul>
     * default footer is: <b>'Page {PN}/{NP}\t{D}  {T}' </b>
     */
    public function setPageFooter($strFooter) : void
    {
        $this->strPageFooter = $strFooter;
    }

    /**
     * Initialisation of grid.
     * <ul>
     * <li> fonts </li>
     * <li> colors </li>
     * <li> totals/subtotals/carry over text </li>
     * <li> charset </li>
     * <li> formating </li></ul>
     * See xfpdf-sample.json for more information about this file.
     * @param string $strFilename
     */
    public function initGrid(string $strFilename) : void
    {
        if (file_exists($strFilename)) {
            $strJSON = file_get_contents($strFilename);
            $jsonData = json_decode($strJSON);
            if ($jsonData) {
                $this->fontHeader = $this->propertyFont($jsonData, 'fontHeader', $this->fontHeader);
                $this->fontSubject = $this->propertyFont($jsonData, 'fontSubject', $this->fontSubject);
                $this->fontFooter = $this->propertyFont($jsonData, 'fontFooter', $this->fontFooter);
                $this->fontColHeader = $this->propertyFont($jsonData, 'fontColHeader', $this->fontColHeader);
                $this->fontSubHeader = $this->propertyFont($jsonData, 'fontSubHeader', $this->fontSubHeader);
                $this->fontRows = $this->propertyFont($jsonData, 'fontRows', $this->fontRows);

                $this->fltLineHeight = $this->property($jsonData, 'dblLineHeight', $this->fltLineHeight);
                $this->fltLineHeight = $this->property($jsonData, 'fltLineHeight', $this->fltLineHeight);

                $this->strHeaderTextColor = $this->property($jsonData, 'strHeaderTextColor', $this->strHeaderTextColor);
                $this->strHeaderDrawColor = $this->property($jsonData, 'strHeaderDrawColor', $this->strHeaderDrawColor);

                $this->strFooterTextColor = $this->property($jsonData, 'strFooterTextColor', $this->strFooterTextColor);
                $this->strFooterDrawColor = $this->property($jsonData, 'strFooterDrawColor', $this->strFooterDrawColor);

                $this->strColHeaderTextColor = $this->property($jsonData, 'strColHeaderTextColor', $this->strColHeaderTextColor);
                $this->strColHeaderFillColor = $this->property($jsonData, 'strColHeaderFillColor', $this->strColHeaderFillColor);
                $this->strSubHeaderTextColor = $this->property($jsonData, 'strSubHeaderTextColor', $this->strSubHeaderTextColor);
                $this->strSubHeaderFillColor = $this->property($jsonData, 'strSubHeaderFillColor', $this->strSubHeaderFillColor);
                $this->strRowTextColor = $this->property($jsonData, 'strRowTextColor', $this->strRowTextColor);
                $this->strRowDrawColor = $this->property($jsonData, 'strRowDrawColor', $this->strRowDrawColor);
                $this->strRowFillColor = $this->property($jsonData, 'strRowFillColor', $this->strRowFillColor);
                $this->strLinkTextColor = $this->property($jsonData, 'strLinkTextColor', $this->strLinkTextColor);

                $this->bStripped = $this->property($jsonData, 'bStripped', $this->bStripped);
                $this->border = $this->property($jsonData, 'border', $this->border);

                $this->bCalcTotals = $this->property($jsonData, 'bCalcTotals', $this->bCalcTotals);
                $this->bPageTotals = $this->property($jsonData, 'bPageTotals', $this->bPageTotals);
                $this->bCarryOver = $this->property($jsonData, 'bCarryOver', $this->bCarryOver);

                $this->strTotals = $this->property($jsonData, 'strTotals', $this->strTotals);
                $this->strPageTotals = $this->property($jsonData, 'strPageTotals', $this->strPageTotals);
                $this->strCarryOver = $this->property($jsonData, 'strCarryOver', $this->strCarryOver);

                $this->charset = $this->property($jsonData, 'strCharset', $this->charset);
                $this->setLocale($this->property($jsonData, 'strLocale', $this->strLocale));

                $strFormat = $this->property($jsonData, 'strFormatDT', null);
                if ($strFormat !== null) {
                    $this->setDateTimeFormat($strFormat);
                } else {
                    $this->setDateTimeFormat(self::DEF_DATE_FORMAT, self::DEF_TIME_FORMAT);
                }
                $strFormat = $this->property($jsonData, 'strFormatD', null);
                if ($strFormat !== null) {
                    $this->setDateFormat($strFormat);
                } else {
                    $this->setDateFormat(self::DEF_DATE_FORMAT);
                }
                $strFormat = $this->property($jsonData, 'strFormatT', null);
                if ($strFormat !== null) {
                    $this->setTimeFormat($strFormat);
                } else {
                    $this->setTimeFormat(self::DEF_TIME_FORMAT);
                }
            } else {
                trigger_error('unable to decode contents of JSON file [' . $strFilename . ']', E_USER_NOTICE);
            }
        }
    }

    /**
     * Set the Date Format to use.
     * Date values are formatet using the language set by setLocale().
     *
     * The $format parameter can either be one of the IntlDateFormatter int Constants
     *
     * | const                      | en_US              | de_DE              |
     * |----------------------------|--------------------|--------------------|
     * | IntlDateFormatter::SHORT   | M/d/yy             | dd.MM.yy           |
     * | IntlDateFormatter::MEDIUM  | MMM d, yyyy        | dd.MM.yyyy         |
     * | IntlDateFormatter::LONG    | MMMM d, yyyy       | dd. MMMM yyyy      |
     * | IntlDateFormatter::FULL    | EEEE, MMMM d, yyyy | EEEE dd. MMMM yyyy |
     *
     * or a string pattern following the definition of IntlDateFormatter (see link below)
     *
     * [Pattern to Format Date and Time](./pattern-to-format-date-and-time)
     *
     * > Note: This property can also be set in the JSON file used in initGrid() !!
     * @param int|string $format
     * @see XPDF::initGrid()
     * @link https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     */
    public function setDateFormat($format) : void
    {
        if (is_numeric($format)) {
            $this->oDateFormatter = new IntlDateFormatter($this->strLocale, $format, IntlDateFormatter::NONE);
        } else {
            if (strpos($format, '%') !== FALSE) {
                trigger_error('XFPDF::setDateFormat("' . $format . '"): call with old strftime() formated Parameter!', E_USER_ERROR);
            } else {
                $this->oDateFormatter = new IntlDateFormatter($this->strLocale, 0, 0, null, null, $format);
            }
        }
    }

    /**
     * Set the Time Format to use.
     *
     * The $format parameter can either be one of the IntlDateFormatter int Constants
     *
     * | const                      | en_US           | de_DE         |
     * |----------------------------|-----------------|---------------|
     * | IntlDateFormatter::SHORT   | KK:mm a         | HH:mm         |
     * | IntlDateFormatter::MEDIUM  | KK:mm:ss a      | HH:mm:ss      |
     * | IntlDateFormatter::LONG    | KK:mm:ss a o    | HH:mm:ss o    |
     * | IntlDateFormatter::FULL    | KK:mm:ss a vvvv | HH:mm:ss vvvv |
     *
     * or a string pattern following the definition of IntlDateFormatter (see link below)
     *
     * [Pattern to Format Date and Time](./pattern-to-format-date-and-time)
     *
     * > Note: This property can also be set in the JSON file used in initGrid() !!
     * @param int|string $format
     * @see XPDF::initGrid()
     * @link https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     */
    public function setTimeFormat($format) : void
    {
        if (is_numeric($format)) {
            $this->oTimeFormatter = new IntlDateFormatter($this->strLocale, IntlDateFormatter::NONE, $format);
        } else {
            if (strpos($format, '%') !== FALSE) {
                trigger_error('XFPDF::setTimeFormat("' . $format . '"): call with old strftime() formated Parameter!', E_USER_ERROR);
            } else {
                $this->oTimeFormatter = new IntlDateFormatter($this->strLocale, 0, 0, null, null, $format);
            }
        }
    }

    /**
     * Set the DateTime Format to use.
     * You can either set the $formatD and $formatT parameter to one of the
     * IntlDateFormatter int Constants (see setDateFormat, setTimeFormat)
     * or set a string pattern following the definition of IntlDateFormatter as
     * first parameter (see link below).
     *
     * [Pattern to Format Date and Time](./pattern-to-format-date-and-time)
     *
     * > Note: This property can also be set in the JSON file used in initGrid() !!
     * @param int|string $formatD
     * @param int $formatT
     * @see XPDF::setDateFormat()
     * @see XPDF::setTimeFormat()
     * @see XPDF::initGrid()
     * @link https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     */
    public function setDateTimeFormat($formatD, int $formatT = 0) : void
    {
        if (is_numeric($formatD)) {
            $this->oDTFormatter = new IntlDateFormatter($this->strLocale, $formatD, $formatT);
        } else {
            if (strpos($formatD, '%') !== FALSE) {
                trigger_error('XFPDF::setDateTimeFormat("' . $formatD . '"): call with old strftime() formated Parameter!', E_USER_ERROR);
            } else {
                $this->oDTFormatter = new IntlDateFormatter($this->strLocale, 0, 0, null, null, $formatD);
            }
        }
    }

    /**
     * Set format for numbers.
     * Decimal point and thousands separator from locale settings is used if available. <br/>
     * <br/>
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @param int $iDecimals
     * @param string $strPrefix
     * @param string $strSuffix
     * @see XPDF::initGrid()
     */
    public function setNumberFormat(int $iDecimals, string $strPrefix = '', string $strSuffix = '') : void
    {
        $this->iNumberDecimals = $iDecimals;
        $this->strNumberPrefix = $strPrefix;
        $this->strNumberSuffix = $strSuffix;
    }

    /**
     * Set font for page header.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strFontname
     * @param string $strStyle
     * @param int $iSize
     */
    public function setHeaderFont(string $strFontname, string $strStyle, int $iSize) : void
    {
        $this->fontHeader = new XPDFFont($strFontname, $strStyle, $iSize);
    }

    /**
     * Set font for subject in the page header.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strFontname
     * @param string $strStyle
     * @param int $iSize
     */
    public function setSubjectFont(string $strFontname, string $strStyle, int $iSize) : void
    {
        $this->fontSubject = new XPDFFont($strFontname, $strStyle, $iSize);
    }

    /**
     * Set font for page footer.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strFontname
     * @param string $strStyle
     * @param int $iSize
     */
    public function setFooterFont(string $strFontname, string $strStyle, int $iSize) : void
    {
        $this->fontFooter = new XPDFFont($strFontname, $strStyle, $iSize);
    }

    /**
     * Set font for col headers.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strFontname
     * @param string $strStyle
     * @param int $iSize
     */
    public function setColHeaderFont(string $strFontname, string $strStyle, int $iSize) : void
    {
        $this->fontColHeader = new XPDFFont($strFontname, $strStyle, $iSize);
    }

    /**
     * Set font for sub headers.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strFontname
     * @param string $strStyle
     * @param int $iSize
     */
    public function setSubHeaderFont(string $strFontname, string $strStyle, int $iSize) : void
    {
        $this->fontSubHeader = new XPDFFont($strFontname, $strStyle, $iSize);
    }

    /**
     * Set font for data rows.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strFontname
     * @param string $strStyle
     * @param int $iSize
     */
    public function setRowFont(string $strFontname, string $strStyle, int $iSize) : void
    {
        $this->fontRows = new XPDFFont($strFontname, $strStyle, $iSize);
        $this->selectFont($this->fontRows);
    }

    /**
     * Set lineheight.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param float $fltLineHeight  lineheight in mm
     */
    public function setLineHeight(float $fltLineHeight) : void
    {
        $this->fltLineHeight = $fltLineHeight;
    }

    /**
     * Set colors for text and drawing in the pageheader.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strTextColor
     * @param string $strDrawColor
     */
    public function setHeaderColors(string $strTextColor, string $strDrawColor) : void
    {
        $this->strHeaderTextColor = $strTextColor;
        $this->strHeaderDrawColor = $strDrawColor;
    }

    /**
     * Set colors for text and drawing in the colheader.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strTextColor
     * @param string $strFillColor
     */
    public function setColHeaderColors(string $strTextColor, string $strFillColor) : void
    {
        $this->strColHeaderTextColor = $strTextColor;
        $this->strColHeaderFillColor = $strFillColor;
    }

    /**
     * Set colors for text and drawing in the grid.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strTextColor
     * @param string $strDrawColor
     * @param string $strFillColor
     * @param bool $bStripped
     */
    public function setRowColors(string $strTextColor, string $strDrawColor, string $strFillColor, bool $bStripped = true) : void
    {
        $this->strRowTextColor = $strTextColor;
        $this->strRowDrawColor = $strDrawColor;
        $this->strRowFillColor = $strFillColor;
        $this->bStripped = $bStripped;
    }

    /**
     * Set colors for text and drawing in the pagefooter.
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strTextColor
     * @param string $strDrawColor
     */
    public function setFooterColors(string $strTextColor, string $strDrawColor) : void
    {
        $this->strFooterTextColor = $strTextColor;
        $this->strFooterDrawColor = $strDrawColor;
    }

    /**
     * Enables automatic calclation of totals.
     * <ul>
     * <li> totals over all at end of grid  (self::TOTALS) </li>
     * <li> subtotals at end of each page (self::PAGE_TOTALS) </li>
     * <li> carry over at beginning of new page (self::CARRY_OVER) </li></ul>
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param int $iTotals  combination of
     */
    public function enableTotals(int $iTotals = self::TOTALS) : void
    {
        $this->bCalcTotals = ($iTotals & self::TOTALS) != 0;
        $this->bPageTotals = ($iTotals & self::PAGE_TOTALS) != 0;
        $this->bCarryOver = ($iTotals & self::CARRY_OVER) != 0;
        if ($this->bPageTotals) {
            // we must increase the bottom margin to trigger the pagebreak
            $this->setAutoPageBreak(true, self::BOTTOM_MARGIN + $this->fltLineHeight);
        }
    }

    /**
     * Set text for totals, subtotals and carry over row.
     * Following placeholders will be replaced: <ul>
     * <li>  {PN} -> current page  </li>
     * <li>  {PN-1} -> previous page </li></ul>
     * <b>!! Can be controled/overwritten through JSON-Format in InitGrid() !!</b>
     * @see XPDF::initGrid()
     * @param string $strTotals
     * @param string $strPageTotals
     * @param string $strCarryOver
     */
    public function setTotalsText(string $strTotals, string $strPageTotals = '', string $strCarryOver = '') : void
    {
        $this->strTotals = $strTotals;
        if (strlen($strPageTotals) > 0) {
            $this->strPageTotals = $strPageTotals;
        }
        if (strlen($strCarryOver) > 0) {
            $this->strCarryOver = $strCarryOver;
        }
    }

    /**
     * reset coldefinitions
     */
    public function resetCols() : void
    {
        $this->iMaxCol = -1;
        $this->aColWidth = array();
        $this->aColAlign = array();
        $this->aColFlags = array();
        $this->aColField = array();
        $this->iMaxColHeader = -1;
        $this->aColHeader = array();
        $this->aColSpan = array();
        $this->aTotalsColSpan = array();
        $this->aSubTotals = array();
        $this->aTotals = array();
    }

    /**
     * Add Column to the Grid.
     * String is directly mapped to field in datarow, number is requested through Col() method
     * @param string $strColHeader title text in the header, if equal -1, colspan of previous col ist increased
     * @param float $fltWidth      width in mm (if -1, col is enhanced so table uses on full page width)
     * @param string $strAlign     alignment of datacol 'L', 'C' or 'R' - headerer cells allways centered
     * @param string|int $strField     data-field or Column ID.
     * @param int $wFlags          Flags to define behaviour for column
     * @return int                 Index of the inserted col
     */
    public function addCol(string $strColHeader, float $fltWidth, string $strAlign, $strField, int $wFlags = 0) : int
    {
        $this->iMaxCol++;
        $this->aColWidth[$this->iMaxCol] = $fltWidth;
        $this->aColAlign[$this->iMaxCol] = $strAlign;
        $this->aColFlags[$this->iMaxCol] = $wFlags;
        $this->aColField[$this->iMaxCol] = $strField;
        if ($strColHeader != -1) {
            $this->iMaxColHeader++;
            $this->aColHeader[$this->iMaxColHeader] = $strColHeader;
            $this->aColSpan[$this->iMaxColHeader] = 1;
        } else {
            $this->aColSpan[$this->iMaxColHeader]++;
        }
        if ($this->iMaxCol == 0 || ($wFlags & self::FLAG_TOTALS) != 0) {
            $this->iMaxColTotals++;
            $this->aTotals[$this->iMaxCol] = 0.0;
            $this->aSubTotals[$this->iMaxCol] = 0.0;
            $this->aTotalsColSpan[$this->iMaxColTotals] = 1;
        } else {
            $this->aTotalsColSpan[$this->iMaxColTotals]++;
        }
        return $this->iMaxCol;
    }

    /**
     * Set infos for image col.
     * Set the margin from the top left corner of the cell and the height/width of the image. <ul>
     * <li> If no height and width specified, the image is printed in its origin size </li>
     * <li> If only one of both is specified, the image is scaled and the aspect ratio is retained </li></ul>
     * @param int $iCol        index of the col (usually the return value of the AddCol() Method)
     * @param float $fltTop    top margin from row in user units
     * @param float $fltLeft   left margin from cell in user units
     * @param float $fltHeight height of the image in user units
     * @param float $fltWidth  width of the image in user units
     */
    public function setColImageInfo(int $iCol, float $fltTop, float $fltLeft, float $fltHeight = -1, float $fltWidth = -1) : void
    {
        if ($this->aColFlags[$iCol] & self::FLAG_IMAGE == 0) {
            trigger_error('Col #' . $iCol . ' is not defined as image col!', E_USER_WARNING);
        }
        $this->aImgInfo[$iCol] = array(
            'fltTop' => $fltTop,
            'fltLeft' => $fltLeft,
            'fltWidth' => $fltHeight,
            'fltHeight' => $fltWidth);
    }

    /**
     * Have to be called once before datarows be added to the document.
     */
    public function prepare() : void
    {
        $this->calcColWidth();
        $this->bInGrid = true;
        $this->addPage();

        $this->selectDrawColor($this->strRowDrawColor);
        $this->selectFillColor($this->strRowFillColor);
        $this->selectTextColor($this->strRowTextColor);
        $this->selectFont($this->fontRows);
        $this->setLineWidth(0.2);
    }

    /**
     * Build row.
     * If fieldname specified in AddCol(), directly the value from the associative array is inserted
     * (in case of DATE-Field value is formated d.m.Y)
     * all other columns are requested through GetCol() - method
     * @param array $row    current row as associative array (may comes from DB query)
     */
    public function row(array $row) : void
    {
        $a = $this->saveSettings();
        $this->iRow++;
        if (($strPreRow = $this->preRow($row)) != '') {
            $this->subHeader($strPreRow);
        }
        if (!$this->isRowVisible($row)) {
            return;
        }
        $this->rowInner($row);
        $this->postRow($row);
        $this->restoreSettings($a);
    }

    /**
     * Mark the end of the grid.
     * If totals enabled, total row will be printed. <br/>
     */
    public function endGrid() : void
    {
        $this->totalsRow(self::TOTALS);
        // Internal flag is needed to suppress any printing of colheaders or subtotals after end of grid!
        $this->bInGrid = false;
    }

    /**
     * Starts group for new subtotals.
     * Reset calculated subtotals and print subheader if strHeader is set
     * @param string $strTotals
     * @param string $strHeader
     */
    public function startGroup(string $strTotals, ?string $strHeader = null) : void
    {
        $this->strSubTotals = $strTotals;
        $iCount = count($this->aSubTotals);
        for ($i = 0; $i < $iCount; $i++) {
            $this->aSubTotals[$i] = 0.0;
        }
        if ($strHeader) {
            $this->subHeader($strHeader);
        }
    }

    /**
     * End group and print subtotals row.
     */
    public function endGroup() : void
    {
        $this->totalsRow(self::SUB_TOTALS);
        $this->strSubTotals = '';
    }

    /**
     * Selects given font.
     * @param XPDFFont $font
     */
    public function selectFont(?XPDFFont $font) : void
    {
        if ($font !== null) {
            $this->setFont($font->strFontname, $font->strStyle, $font->iSize);
        }
    }

    /**
     * Set color for text.
     * @param string $strColor color to select in HTML-Format #RRGGBB
     */
    public function selectTextColor(string $strColor) : void
    {
        $r = 0; $g = 0; $b = 0;
        $this->getRGB($strColor, $r, $g, $b);
        $this->setTextColor($r, $g, $b);
    }

    /**
     * Set color for drawing.
     * @param string $strColor color to select in HTML-Format #RRGGBB
     */
    public function selectDrawColor(string $strColor) : void
    {
        $r = 0; $g = 0; $b = 0;
        $this->getRGB($strColor, $r, $g, $b);
        $this->setDrawColor($r, $g, $b);
    }

    /**
     * Set fillcolor.
     * @param string $strColor color to select in HTML-Format #RRGGBB
     */
    public function selectFillColor(string $strColor) : void
    {
        $r = 0; $g = 0; $b = 0;
        $this->getRGB($strColor, $r, $g, $b);
        $this->setFillColor($r, $g, $b);
    }

    /**
     * Last step: create the document.
     * If nor filename is given, the title set with SetInfo() or SetTitle()
     * method is used (or 'XFPDF.pdf' if no title set so far).
     * If the filename not ending with .pdf (case insensitive), the extension ist appended.
     * @param string $strFilename  Filename
     */
    public function createPDF(string $strFilename = '') : void
    {
        if (empty($strFilename)) {
            $strFilename = isset($this->metadata['Title']) ? $this->metadata['Title'] : 'XFPDF.pdf';
        }
        if (strtolower(substr($strFilename, -4)) !== '.pdf') {
            $strFilename .= '.pdf';
        }
        $this->output($strFilename, 'I');
    }

    /**
     * Print pageheader / logo / colheaders.
     * {@inheritDoc}
     * @see \OPlathey\FPDF\FPDF::header()
     */
    public function header() : void
    {
        if (!empty($this->strPageTitle)) {
            $fltLogoHeight = 0.0;
            if (!empty($this->strLogo)) {
                list($iWidth, $iHeight) = getimagesize($this->strLogo);
                if ($iWidth > 0 && $iHeight > 0) {
                    // scale image to desired high
                    $iWidth *= $this->fltLogoHeight / $iHeight;
                    $x = $this->w - $this->rMargin - $iWidth - 1;
                    $y = $this->tMargin + 0.5;
                    $this->Image($this->strLogo, $x, $y, $iWidth);
                    $fltLogoHeight = $this->fltLogoHeight;
                }
            }

            $this->selectDrawColor($this->strHeaderDrawColor);
            $this->selectFont($this->fontHeader);
            $this->selectTextColor($this->strHeaderTextColor);
            $this->setLineWidth(0.2);
            $strPageTitle = $this->replacePlaceholder($this->strPageTitle);
            $strPageSubject = $this->replacePlaceholder($this->strPageSubject);
            $this->cell(0, $this->FontSize, $strPageTitle, 0, 0, 'L');
            $this->ln();
            if (strlen($strPageSubject) > 0) {
                $this->selectFont($this->fontSubject);
                $this->cell(0, $this->FontSize, $strPageSubject, 0, 0, 'L');
                $this->ln();
            }

            $y = $this->getY();
            if (($fltLogoHeight + $this->tMargin) > $y) {
                $y = $fltLogoHeight + $this->tMargin;
                $this->setY($y);
            }
            $y += 2.0;
            $this->line($this->lMargin, $y, $this->w - $this->rMargin, $y);
            $y += 0.5;
            $this->line($this->lMargin, $y, $this->w - $this->rMargin, $y);
            $this->ln(6);
        }
        if ($this->iMaxCol > 0 && $this->bInGrid) {
            $this->colHeader();
            if ($this->bCarryOver && $this->page > 1) {
                $this->totalsRow(self::CARRY_OVER);
            }
        }
    }

    /**
     * Print pagefooter.
     * {@inheritDoc}
     * @see \OPlathey\FPDF\FPDF::footer()
     */
    public function footer() : void
    {
        if ($this->bPageTotals) {
            $this->totalsRow(self::PAGE_TOTALS);
        }
        if (!empty($this->strPageFooter)) {
            $this->selectDrawColor($this->strFooterDrawColor);
            $this->selectFont($this->fontFooter);
            $this->selectTextColor($this->strFooterTextColor);
            $this->setLineWidth(0.2);

            // Position 2mm from the bottom border
            $this->setY(-self::BOTTOM_MARGIN + 2);
            $this->line($this->lMargin, $this->GetY() - 0.5, $this->w - $this->rMargin, $this->getY() - 0.5);
            $iWidth = $this->w - $this->rMargin - $this->lMargin;
            $aCell = explode("\t", $this->strPageFooter, 3);
            $aAlign = array('', '', '');
            switch (count($aCell)) {
                case 1:
                    $aAlign = array('C', '', '');
                    break;
                case 2:
                    $aAlign = array('L', 'R', '');
                    break;
                case 3:
                    $aAlign = array('L', 'C', 'R');
                    break;
            }
            $i = 0;
            foreach ($aCell as $strCell) {
                $strCell = $this->replacePlaceholder($strCell);
                $this->cell($iWidth / count($aCell), 7, $strCell, 'T', 0, $aAlign[$i++]);
            }
        }
    }

    /**
     * Create headercols for grid.
     */
    protected function colHeader() : void
    {
        $this->selectFillColor($this->strColHeaderFillColor);
        $this->selectTextColor($this->strColHeaderTextColor);
        $this->selectDrawColor($this->strRowDrawColor);
        $this->setLineWidth(0.2);
        $this->selectFont($this->fontColHeader);

        $iCol = 0;
        for ($i = 0; $i <= $this->iMaxColHeader; $i++) {
            $iWidth = 0;
            if ($this->aColSpan[$i] > 1) {
                $j = 0;
                while ($j < $this->aColSpan[$i]) {
                    $iWidth += $this->aColWidth[$iCol++];
                    $j++;
                }
            } else {
                $iWidth = $this->aColWidth[$iCol++];
            }

            $strHeader = $this->aColHeader[$i];
            $this->cell($iWidth, $this->fltLineHeight, $strHeader, $this->border, 0, 'C', true);
        }
        $this->ln();
    }

    /**
     * Insert Subheader into the grid.
     * @param string $strText
     */
    protected function subHeader(string $strText) : void
    {
        $a = $this->saveSettings();

        $this->selectFillColor($this->strSubHeaderFillColor);
        $this->selectTextColor($this->strSubHeaderTextColor);
        $this->selectFont($this->fontSubHeader);

        // increase pagebreak trigger to ensure not only subheader fits on current page
        $iBottomMargin = $this->bMargin;
        $this->setAutoPageBreak(true, $iBottomMargin + $this->fltLineHeight);
        $iWidth = $this->w - $this->lMargin - $this->rMargin;
        $this->cell($iWidth, $this->fltLineHeight, $strText, $this->border, 0, 'L', true);
        $this->ln();
        $this->restoreSettings($a);
        // reset pagebreak trigger
        $this->setAutoPageBreak(true, $iBottomMargin);
    }

    /**
     * Calculates width for dynamic col.
     * Dynamic col is specified with a width of -1. <br/>
     * <b>Only one col with width of -1 is allowed!</b> <br/>
     * If no dyn. Col is specified, last col is assumed as dynamic. <br/><br/>
     * Sum of all other cols is subtracted from page width. <br/>
     */
    protected function calcColWidth() : void
    {
        $iGridWidth = $this->w - $this->lMargin - $this->rMargin;
        $iCol = -1;
        for ($i = 0; $i <= $this->iMaxCol; $i++) {
            if ($this->aColWidth[$i] < 0) {
                if ($iCol >= 0) {
                    trigger_error('Only one dynamic col is allowed!', E_USER_WARNING);
                }
                $iCol = $i;
            }
        }
        if ($iCol < 0) {
            $iCol = $this->iMaxCol;
        }
        $iWidth = 0;
        for ($i = 0; $i <= $this->iMaxCol; $i++) {
            if ($i != $iCol) {
                $iWidth += $this->aColWidth[$i];
            }
        }
        $this->aColWidth[$iCol] = $iGridWidth - $iWidth;
    }

    /**
     * Inner 'pure' function to build the row.
     * @param array $row
     */
    protected function rowInner(array $row) : void
    {
        $this->bInGrid = true;

        if ($this->bMultiline) {
            if ($this->getY() + (2 * $this->fltLineHeight) > $this->PageBreakTrigger)
                $this->AddPage($this->CurOrientation);
        }

        for ($i = 0; $i <= $this->iMaxCol; $i++) {
            $strCell = '';
            $field = $this->aColField[$i];
            $wFlags = $this->aColFlags[$i];
            $bFill = $this->bStripped && (($this->iRow % 2) == 0);

            // calc totals
            if (isset($row[$field])) {
                $this->calcTotals($i, $row[$field]);
            }

            // save for restore, if changed for current col
            $a = $this->saveSettings();

            if (is_numeric($field)) {
                $strCell = $this->col(intval($field), $row, $bFill);
            } else {
                // directly get value from row data
                if (!isset($row[$field])) {
                    $strCell = '';
                } else {
                    $strCell = $row[$field];
                }
                if (($wFlags & self::FLAG_FORMAT) == self::FLAG_ELIPSIS) {
                    $fltWidth = $this->getStringWidth($strCell);
                    if ($fltWidth > $this->aColWidth[$i] - 2) {
                        while ($fltWidth > $this->aColWidth[$i] - 3) {
                            $strCell = substr($strCell, 0, -1);
                            $fltWidth = $this->getStringWidth($strCell);
                        }
                        $strCell .= '..';
                    }
                }
                $strCell = $this->formatValue($strCell, $wFlags);
            }

            if ($this->isImageCol($i)) {
                $this->drawImageCol($i, $strCell, $bFill);
            } else {
                $link = $this->getColLink($i, $row);
                if ($this->bMultiline) {
                    $x = $this->x;
                    $y = $this->y;
                    $this->multiCell($this->aColWidth[$i], $this->fltLineHeight, $strCell, $this->border, $this->aColAlign[$i], $bFill);
                    if ($i != $this->iMaxCol) {
                        $this->x = $x + $this->aColWidth[$i];
                        $this->y = $y;
                    }
                } else {
                    $this->cell($this->aColWidth[$i], $this->fltLineHeight, $strCell, $this->border, 0, $this->aColAlign[$i], $bFill, $link);
                }
            }

            $this->restoreSettings($a);
        }
        if (!$this->bMultiline) {
            $this->ln();
        }
    }

    /**
     * Print totals/subtotals row.
     * @param int $iTotals
     */
    protected function totalsRow(int $iTotals) : void
    {
        if ($this->bInGrid && $this->bCalcTotals) {
            $a = $this->saveSettings();
            $this->setTotalsRowFormat($iTotals);
            $aTotals = $this->getTotalsRowValues($iTotals);
            $strText = $this->getTotalsRowText($iTotals);
            $iCol = 0;
            for ($iTotalsCol = 0; $iTotalsCol <= $this->iMaxColTotals; $iTotalsCol++) {
                $strCol = '';
                $strAlign = 'C';

                if ($this->isTotalsTextCol($iCol)) {
                    $strCol = $strText;
                    $strAlign = 'L';
                } elseif ($this->isTotalsCalcCol($iCol)) {
                    $strCol = $this->formatValue($aTotals[$iCol], $this->aColFlags[$iCol]);
                    $strAlign = 'R';
                }
                $iWidth = $this->calcTotalsColWidth($iTotalsCol, $iCol);
                $this->cell($iWidth, $this->fltLineHeight, $strCol, $this->border, 0, $strAlign, true);
                $iCol += $this->aTotalsColSpan[$iTotalsCol];
            }
            $this->ln();
            $this->restoreSettings($a);
        }
    }

    /**
     * Set the format for the requested totals row.
     * - totals and pagetotals use colors and font from ColHeader <br/>
     * - all other types uses format from subheader <br/>
     * @param int $iTotals
     */
    protected function setTotalsRowFormat(int $iTotals) : void
    {
        if ($iTotals == self::TOTALS || $iTotals == self::PAGE_TOTALS) {
            $this->selectFillColor($this->strColHeaderFillColor);
            $this->selectTextColor($this->strColHeaderTextColor);
            $this->setLineWidth(0.2);
            $this->selectFont($this->fontColHeader);
        } else {
            $this->selectFillColor($this->strSubHeaderFillColor);
            $this->selectTextColor($this->strSubHeaderTextColor);
            $this->setLineWidth(0.2);
            $this->selectFont($this->fontSubHeader);
        }
    }

    /**
     * Get the tect for requested totals row.
     * Get the text dependent on the type of the totals row and replaace
     * all supported placeholders.
     * @param int $iTotals
     * @return string
     */
    protected function getTotalsRowText(int $iTotals) : string
    {
        $strText = '';
        switch ($iTotals) {
            case self::TOTALS:
                $strText = $this->strTotals;
                break;
            case self::PAGE_TOTALS:
                $strText = $this->strPageTotals;
                break;
            case self::CARRY_OVER:
                $strText = $this->strCarryOver;
                break;
            case self::SUB_TOTALS:
                $strText = $this->strSubTotals;
                break;
            default:
                break;
        }
        // replace supported placeholders
        $strText = str_replace('{PN}', strval($this->page), $strText);
        $strText = str_replace('{PN-1}', strval($this->page - 1), $strText);
        return $strText;
    }

    /**
     * Get the calculated values for the requested totals row.
     * @param int $iTotals
     * @return array
     */
    protected function getTotalsRowValues(int $iTotals) : array
    {
        if ($iTotals == self::SUB_TOTALS) {
            return $this->aSubTotals;
        } else {
            return $this->aTotals;
        }
    }

    /**
     * Check, if requested col is set for the output of totals text.
     * @param int $iCol
     * @return bool
     */
    protected function isTotalsTextCol(int $iCol) : bool
    {
        return ($this->aColFlags[$iCol] & self::FLAG_TOTALS_TEXT) != 0;
    }

    /**
     * Check, if requested col is defined for totals calculation.
     * @param int $iCol
     * @return bool
     */
    protected function isTotalsCalcCol(int $iCol) : bool
    {
        return ($this->aColFlags[$iCol] & self::FLAG_TOTALS_CALC) != 0;
    }

    /**
     * Calculates the width of the requested totals col.
     *
     * @param int $iTotalsCol
     * @param int $iCol
     * @return float
     */
    protected function calcTotalsColWidth(int $iTotalsCol, int $iCol) : float
    {
        $fltWidth = 0;
        if ($this->aTotalsColSpan[$iTotalsCol] > 1) {
            $j = 0;
            while ($j < $this->aTotalsColSpan[$iTotalsCol]) {
                $fltWidth += $this->aColWidth[$iCol++];
                $j++;
            }
        } else {
            $fltWidth = $this->aColWidth[$iCol++];
        }
        return $fltWidth;
    }

    /**
     * Calculate totals for given col.
     * @param int $iCol
     * @param mixed $value
     */
    protected function calcTotals(int $iCol, $value) : void
    {
        // calc totals if enabled
        if ($this->bCalcTotals && $this->isTotalsCalcCol($iCol)) {
            if (is_numeric($value)) {
                $this->aTotals[$iCol] += $value;
                $this->aSubTotals[$iCol] += $value;
            }
        }
    }
    /**
     * Check, if requested col is iamge col.
     * @param int $iCol
     * @return bool
     */
    protected function isImageCol(int $iCol) : bool
    {
        return ($this->aColFlags[$iCol] & self::FLAG_IMAGE) != 0;
    }

    /**
     * Draw the image for a image col.
     * @param int $iCol
     * @param string $strImage
     */
    protected function drawImageCol(int $iCol, string $strImage, bool $bFill) : void
    {
        $fltTop = $this->getY();
        $fltLeft = $this->getX();
        $fltHeight = 0;
        $fltWidth = 0;
        if (isset($this->aImgInfo[$iCol])) {
            $fltTop += $this->aImgInfo[$iCol]['fltTop'];
            $fltLeft += $this->aImgInfo[$iCol]['fltLeft'];
            if ($this->aImgInfo[$iCol]['fltHeight'] > 0) {
                $fltHeight = $this->aImgInfo[$iCol]['fltHeight'];
            }
            if ($this->aImgInfo[$iCol]['fltWidth'] > 0) {
                $fltWidth = $this->aImgInfo[$iCol]['fltWidth'];
            }
        }
        $this->cell($this->aColWidth[$iCol], $this->fltLineHeight, '', $this->border, 0, 'C', $bFill);
        $this->image($strImage, $fltLeft, $fltTop, $fltWidth, $fltHeight);
    }

    /**
     * Hook to hide a row dependend on row data.
     * Can be overloaded in subclass to hide a row.
     * @param array $row
     * @return bool    function must return false, if row should not be printed
     */
    protected function isRowVisible(/** @scrutinizer ignore-unused */ array $row) : bool
    {
        return true;
    }

    /**
     * Get link information for requested col.
     * Set text color and underline style if col contains link
     * @param int $iCol
     * @param array $row
     * @return string|int
     */
    protected function getColLink(int $iCol, array $row)
    {
        $strLink = '';
        if (($this->aColFlags[$iCol] & self::FLAG_INT_LINK) != 0) {
            $strLink = $this->internalLink($iCol, $row);
            $this->setFont('', 'U');
            $this->selectTextColor($this->strLinkTextColor);
        }
        return $strLink;
    }

    /**
     * Called before next row is printed.
     * This function can be overloaded in derived class to <ul>
     * <li> change row data or add values </li>
     * <li> specify text for subtitle before row is printed </li></ul>
     * The $row parameter is defined as reference so $row may be changed within function in derived class. <br/>
     * If the method returns a non-empty string, a subtitle containing the text is printed before the row
     * @param array $row
     * @return string
     */
    protected function preRow(/** @scrutinizer ignore-unused */ array &$row) : string
    {
        return '';
    }

    /**
     * Called after the output of a row.
     * To be overloaded in derived class
     * @param array $row
     */
    protected function postRow(/** @scrutinizer ignore-unused */ array $row) : void
    {
    }

    /**
     * Get content of a col for current row - to overide in derived class.
     * @param int $iCol     requested colnumber defined in AddCol()
     * @param array $row    current record from DB
     * @param bool          $bFill
     * @return string
     */
    protected function col(int $iCol, array $row, /** @scrutinizer ignore-unused */ bool &$bFill) : string
    {
        $strCol = '';
        switch ($iCol) {
            case self::COL_ROW_NR:
                $strCol = (string) $this->iRow;
                break;
            default:
                break;
        }
        return $strCol;
    }

    /**
     * @param int $iCol
     * @param array $row
     * @return int
     */
    protected function internalLink(/** @scrutinizer ignore-unused */ int $iCol, /** @scrutinizer ignore-unused */ array $row) : int
    {
        return $this->addLink();
    }

    /**
     * Divides color in HTML notation into red, green and blue component
     * @param string $strColor color in HTML notation #RRGGBB
     * @param int $r    red component of color
     * @param int $g    green component of color
     * @param int $b    blue component of color
     */
    protected function getRGB(string $strColor, int &$r, int &$g, int &$b) : void
    {
        if ($strColor[0] == '#') {
            if (strlen($strColor) == 7) {
                $r = intval(substr($strColor, 1, 2), 16);
                $g = intval(substr($strColor, 3, 2), 16);
                $b = intval(substr($strColor, 5, 2), 16);
            } elseif (strlen($strColor) == 4) {
                $r = intval(substr($strColor, 1, 1), 16);
                $g = intval(substr($strColor, 2, 1), 16);
                $b = intval(substr($strColor, 3, 1), 16);
                $r = $r + (16 * $r);
                $g = $g + (16 * $g);
                $b = $b + (16 * $b);
            }
        }
    }

    /**
     * Save some setting to restore after operations changing settings.
     * @return array
     */
    protected function saveSettings() : array
    {
        $a = array();

        $a['family'] = $this->FontFamily;
        $a['style']  = $this->FontStyle;
        $a['size']   = $this->FontSizePt;
        $a['ul']     = $this->underline;
        $a['lw']     = $this->LineWidth;
        $a['dc']     = $this->DrawColor;
        $a['fc']     = $this->FillColor;
        $a['tc']     = $this->TextColor;
        $a['cf']     = $this->ColorFlag;

        return $a;
    }

    /**
     * Restore settings.
     * Restore only values differing
     * @param array $a
     */
    protected function restoreSettings(array $a) : void
    {
        // Restore line width
        if ($this->LineWidth != $a['lw']) {
            $this->LineWidth = $a['lw'];
            $this->out(sprintf('%.2F w', $a['lw'] * $this->k));
        }
        // Restore font
        if (($a['family'] != $this->FontFamily) ||
             $a['style'] != $this->FontStyle ||
             $a['size'] != $this->FontSizePt) {
            $this->setFont($a['family'], $a['style'], $a['size']);
        }
        $this->underline = $a['ul'];

        // Restore colors
        if ($this->DrawColor != $a['dc']) {
            $this->DrawColor = $a['dc'];
            $this->out($a['dc']);
        }
        if ($this->FillColor != $a['fc']) {
            $this->FillColor = $a['fc'];
            $this->out($a['fc']);
        }
        $this->TextColor = $a['tc'];
        $this->ColorFlag = $a['cf'];
    }

    /**
     * Checks if object contains property with given name.
     * If object doesn't have requested property, default value will be returned
     * @param \stdClass $obj    from JSON-Data
     * @param string $strName
     * @param mixed $default
     * @return mixed
     */
    protected function property(\stdClass $obj, string $strName, $default = '')
    {
        $value = $default;
        if (property_exists($obj, $strName)) {
            $value = $obj->$strName;
        }
        return $value;
    }

    /**
     * Checks if object contains font property with given name.
     * If object doesn't have requested property, default font will be returned
     * @param \stdClass $obj
     * @param string $strName
     * @param XPDFFont $fontDefault
     * @return XPDFFont
     */
    protected function propertyFont(\stdClass $obj, string $strName, XPDFFont $fontDefault) : XPDFFont
    {
        $font = $fontDefault;
        if (property_exists($obj, $strName)) {
            $oFont = $obj->$strName;
            $font = new XPDFFont($oFont->name, $oFont->style, $oFont->size);
        }
        return $font;
    }

    /**
     * @param string $strText
     * @return string
     */
    public function convText(string $strText) : string
    {
        /*
         // $strCharset = mb_detect_encoding($strText);
         if ($this->strCharset != 'ISO-8859-15') {
         $strText = iconv($this->strCharset, 'ISO-8859-15//TRANSLIT', $strText);
         }
         return html_entity_decode($strText, ENT_QUOTES, 'ISO-8859-15');
         */
        trigger_error('remove obsolete call from XPDF::convText()', E_USER_DEPRECATED);
        return $strText;
    }

    /**
     * Formatting of the cell data.
     * @param mixed $value
     * @param int $iFormat
     * @return string
     */
    protected function formatValue($value, int $iFormat) : string
    {
        $strValue = strval($value);
        if (($iFormat & self::FLAG_NO_ZERO) && floatval($value) == 0.0) {
            // suppress zero values...
            $strValue = '';
        } else {
            switch ($iFormat & self::FLAG_FORMAT) {
                case self::FLAG_CUR_SYMBOL:
                    $strValue = $this->formatCurrency(floatval($value), true);
                    break;
                case self::FLAG_CUR_PLAIN:
                    $strValue = $this->formatCurrency(floatval($value), false);
                    break;
                case self::FLAG_DATE:
                    $strValue = $this->formatDate($value);
                    break;
                case self::FLAG_TIME:
                    $strValue = $this->formatTime($value);
                    break;
                case self::FLAG_DATE_TIME:
                    $strValue = $this->formatDateTime($value);
                    break;
                case self::FLAG_NUMBER:
                    $strValue = $this->formatNumber(floatval($value));
                    break;
                case self::FLAG_IBAN:
                    $strValue = $this->formatIBAN($value);
                    break;
            }
        }
        return $strValue;
    }

    /**
     * Formats value as number according to locale settings on system.
     * @param float $fltValue
     * @param int $iDecimals
     * @param string $strPrefix
     * @param string $strSuffix
     * @return string
     */
    protected function formatNumber(float $fltValue, ?int $iDecimals = null, ?string $strPrefix = null, ?string $strSuffix = null) : string
    {
        if (!$this->bInvalidLocale) {
            $li = localeconv();
        } else {
            $li = array('decimal_point' => '.', 'thousands_sep' => ',');
        }
        $iDecimals ??= $this->iNumberDecimals;
        $strPrefix ??= $this->strNumberPrefix;
        $strSuffix ??= $this->strNumberSuffix;
        $strValue = number_format($fltValue, $iDecimals, $li['decimal_point'], $li['thousands_sep']);
        if (strlen($strPrefix) > 0) {
            $strValue .= $strPrefix . $strValue;
        }
        if (strlen($strSuffix) > 0) {
            $strValue = $strValue . $strSuffix;
        }
        return $strValue;
    }

    /**
     * Formats value as localized currency.
     * money_format($format, $number) has been DEPRECATED as of PHP 7.4.0.
     * @param float $fltValue
     * @param bool $bSymbol
     * @return string
     */
    protected function formatCurrency(float $fltValue, bool $bSymbol = true) : string
    {
        if (!$this->bInvalidLocale) {
            $li = localeconv();
        } else {
            $li = [
                'mon_decimal_point' => '.',
                'mon_thousands_sep' => ',',
                'p_cs_precedes' => 1,
                'p_ns_precedes' => 1,
                'p_sep_by_space' => 1,
                'n_sep_by_space' => 1,
                'currency_symbol' => '$',
            ];
            $bSymbol = false;
        }
        $strValue = number_format($fltValue, 2, $li['mon_decimal_point'], $li['mon_thousands_sep']);
        if ($bSymbol) {
            $bPrecedes = ($fltValue >= 0 ? $li['p_cs_precedes'] : $li['n_cs_precedes']);
            $bSpace = ($fltValue >= 0 ? $li['p_sep_by_space'] : $li['n_sep_by_space']);
            $strSep = $bSpace ? ' ' : '';
            if ( $li['currency_symbol'] == '€') {
                $li['currency_symbol'] = '{eur}';
            }
            if ($bPrecedes) {
                $strValue = $li['currency_symbol'] . $strSep . $strValue;
            } else {
                $strValue = $strValue . $strSep . $li['currency_symbol'];
            }
        }
        return $strValue;
    }

    /**
     * Format a date cell.
     * @param int|string $Date unix timestamp or valid Date string
     * @return string
     */
    protected function formatDate($Date) : string
    {
        $strDate = '';
        $oDate = null;
        if (is_numeric($Date)) {
            $oDate = new \DateTime();
            $oDate->setTimestamp($Date);
        } else {
            $oDate = new \DateTime($Date);
        }
        if ($oDate !== null) {
            $strDate = $this->oDateFormatter->format($oDate);
        }
        return $strDate;
    }

    /**
     * Format a time cell.
     * @param int|string  $Time unix timestamp or valid Time string
     * @return string
     */
    protected function formatTime($Time) : string
    {
        $strTime = '';
        $oTime = null;
        if (is_numeric($Time)) {
            $oTime = new \DateTime();
            $oTime->setTimestamp($Time);
        } else {
            $oTime = new \DateTime($Time);
        }
        if ($oTime !== null) {
            $strTime = $this->oTimeFormatter->format($oTime);
        }
        return $strTime;
    }

    /**
     * Format a date-time cell.
     * @param int|string $DT    unix timestamp or valid DateTime string
     * @return string
     */
    protected function formatDateTime($DT) : string
    {
        $strDT = '';
        $oDT = null;
        if (is_numeric($DT)) {
            $oDT = new \DateTime();
            $oDT->setTimestamp($DT);
        } else {
            $oDT = new \DateTime($DT);
        }
        if ($oDT !== null) {
            $strDT = $this->oDTFormatter->format($oDT);
        }
        return $strDT;
    }

    /**
     * Format IBAN.
     * @param string $strIBAN
     * @return string
     */
    protected function formatIBAN(string $strIBAN) : string
    {
        $strIBAN = str_replace(' ', '', $strIBAN);
        $strFormatedIBAN = '';
        $iLen = strlen($strIBAN);
        for ($i = 0; $i < $iLen; $i++) {
            if ($i > 0 && $i % 4 == 0) {
                $strFormatedIBAN .= ' ';
            }
            $strFormatedIBAN .= $strIBAN[$i];
        }
        return $strFormatedIBAN;
    }

    /**
     * Replace the placeholders that can be used in header and footer.
     * @param string $strText
     * @return string
     */
    protected function replacePlaceholder(string $strText) : string
    {
        $oDateFormatter = new IntlDateFormatter($this->strLocale, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
        $strText = str_replace("{D}", $oDateFormatter->format(new \DateTime()), $strText);
        $oTimeFormatter = new IntlDateFormatter($this->strLocale, IntlDateFormatter::NONE, IntlDateFormatter::MEDIUM);
        $strText = str_replace("{T}", $oTimeFormatter->format(new \DateTime()), $strText);
        $strText = str_replace("{PN}", strval($this->pageNo()), $strText);

        return $strText;
    }
}
