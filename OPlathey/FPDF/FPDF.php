<?php
namespace OPlathey\FPDF;

/**
 * Modified version of O.Platheys FPDF.php
 *
 * Based on version 1.82 of FPDF.php, extended by 
 * - namespace to include through autoloader
 * - PHP 7.4 typehints
 * - phpDoc comments  
 *
 * @package OPlathey/FPDF
 * @version 1.82
 * @author O.Plathey
 * @copyright MIT License - see the LICENSE file for details
 */

/*******************************************************************************
* FPDF                                                                         *
*                                                                              *
* Version: 1.82                                                                *
* Date:    2019-12-07                                                          *
* Author:  Olivier PLATHEY                                                     *
* http://www.fpdf.org/en/doc/index.php
*******************************************************************************/

define('FPDF_VERSION','1.82');

class FPDF
{
    /** @var int current page number     */
    protected int $page;
    /** @var int current object number     */
    protected int $n;
    /** @var array array of object offsets     */
    protected array $offsets; 
    /** @var string buffer holding in-memory PDF     */
    protected string $buffer; 
    /** @var array array containing pages     */
    protected array $pages; 
    /** @var int current document state     */
    protected int $state; 
    /** @var bool compression flag     */
    protected bool $compress;
    /** @var float scale factor (number of points in user unit)     */
    protected float $k;
    /** @var string default orientation     */
    protected string $DefOrientation;
    /** @var string current orientation     */
    protected string $CurOrientation;
    /** @var array standard page sizes     */
    protected array $StdPageSizes;
    /** @var array default page size     */
    protected array $DefPageSize;
    /** @var array current page size     */
    protected array $CurPageSize;
    /** @var int current page rotation     */
    protected int $CurRotation;
    /** @var array page-related data     */
    protected array $PageInfo;
    /** @var float width of current page in points     */
    protected float $wPt;
    /** @var float height of current page in points     */
    protected float $hPt;
    /** @var float width of current page in user unit     */
    protected float $w;
    /** @var float height of current page in user unit     */
    protected float $h;
    /** @var float left margin     */
    protected float $lMargin;
    /** @var float top margin     */
    protected float $tMargin;
    /** @var float right margin     */
    protected float $rMargin;
    /** @var float page break margin     */
    protected float $bMargin;
    /** @var float cell margin     */
    protected float $cMargin;
    /** @var float current X-position in user unit     */
    protected float $x; 
    /** @var float current Y-position in user unit     */
    protected float $y;
    /** @var float height of last printed cell     */
    protected float $lasth;
    /** @var float line width in user unit     */
    protected float $LineWidth;
    /** @var string path containing fonts     */
    protected string $fontpath;
    /** @var array array of core font names     */
    protected array $CoreFonts;
    /** @var array array of used fonts     */
    protected array $fonts;
    /** @var array array of font files     */
    protected array $FontFiles;
    /** @var array array of encodings     */
    protected array $encodings;
    /** @var array array of ToUnicode CMaps     */
    protected array $cmaps;
    /** @var string current font family     */
    protected string $FontFamily;
    /** @var string current font style     */
    protected string $FontStyle;
    /** @var bool underlining flag     */
    protected bool $underline;
    /** @var array current font info     */
    protected array $CurrentFont;
    /** @var float current font size in points     */
    protected float $FontSizePt;
    /** @var float current font size in user unit     */
    protected float $FontSize;
    /** @var string commands for drawing color     */
    protected string $DrawColor;
    /** @var string commands for filling color     */
    protected string $FillColor;
    /** @var string commands for text color     */
    protected string $TextColor;
    /** @var bool indicates whether fill and text colors are different     */
    protected bool $ColorFlag;
    /** @var bool indicates whether alpha channel is used     */
    protected bool $WithAlpha;
    /** @var float word spacing     */
    protected float $ws;
    /** @var array array of used images     */
    protected array $images;
    /** @var array array of links in pages     */
    protected array $PageLinks;
    /** @var array array of internal links     */
    protected array $links;
    /** @var bool automatic page breaking     */
    protected bool $AutoPageBreak;
    /** @var float threshold used to trigger page breaks     */
    protected float $PageBreakTrigger;
    /** @var bool flag set when processing header     */
    protected bool $InHeader;
    /** @var bool flag set when processing footer     */
    protected bool $InFooter;
    /** @var string alias for total number of pages     */
    protected string $AliasNbPages;
    /** @var string|float zoom display mode     */
    protected $ZoomMode;
    /** @var string layout display mode     */
    protected string $LayoutMode;
    /** @var array document properties     */
    protected array $metadata;
    /** @var string PDF version number     */
    protected string $PDFVersion;

    /**
     * This is the class constructor. 
     * It allows to set up the page size, the orientation and the unit of measure used 
     * in all methods (except for font sizes).
     * @param string $orientation   Default page orientation. <br/>
     *                              Possible values are (case insensitive): <ul> 
     *                              <li>   'P' or 'Portrait' </li>
     *                              <li>   'L' or 'Landscape' </li></ul> 
     *                              Default value is 'P'. <br/>
     * @param string $unit          User unit.  <br/>
     *                              Possible values are: <ul>
     *                              <li>   'pt': point,  </li> 
     *                              <li>   'mm': millimeter,  </li>
     *                              <li>   'cm': centimeter,  </li>
     *                              <li>   'in': inch </li></ul>
     *                              A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). <br/>
     *                              This is a very common unit in typography; font sizes are expressed in that unit. <br/>
     *                              Default value is 'mm'. <br/>
     * @param string|array $size    The size used for pages. <br/>
     *                              It can be either one of the following values (case insensitive): <ul>
     *                              <li> 'A3' </li>
     *                              <li> 'A4' </li>
     *                              <li> 'A5' </li>
     *                              <li> 'Letter' </li>
     *                              <li> 'Legal' </li></ul>
     *                              or an array containing the width and the height (expressed in the unit given by unit). <br/>
     *                              Default value is 'A4'.
     */
    public function __construct(string $orientation='P', string $unit='mm', $size='A4')
    {
    	// Some checks
    	$this->_dochecks();
    	// Initialization of properties
    	$this->state = 0;
    	$this->page = 0;
    	$this->n = 2;
    	$this->buffer = '';
    	$this->pages = array();
    	$this->PageInfo = array();
    	$this->fonts = array();
    	$this->FontFiles = array();
    	$this->encodings = array();
    	$this->cmaps = array();
    	$this->images = array();
    	$this->links = array();
    	$this->InHeader = false;
    	$this->InFooter = false;
    	$this->lasth = 0;
    	$this->FontFamily = '';
    	$this->FontStyle = '';
    	$this->FontSizePt = 12;
    	$this->underline = false;
    	$this->DrawColor = '0 G';
    	$this->FillColor = '0 g';
    	$this->TextColor = '0 g';
    	$this->ColorFlag = false;
    	$this->WithAlpha = false;
    	$this->ws = 0;
    	// Font path
    	if (defined('FPDF_FONTPATH')) {
    		$this->fontpath = FPDF_FONTPATH;
    		if (substr($this->fontpath, -1) != '/' && substr($this->fontpath, -1) != '\\') {
    			$this->fontpath .= '/';
    		}
    	} elseif (is_dir(dirname(__FILE__) . '/font')) {
    		$this->fontpath = dirname(__FILE__) . '/font/';
    	} else {
    		$this->fontpath = '';
    	}
    	// Core fonts
    	$this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
    	// Scale factor
    	if($unit == 'pt') {
    		$this->k = 1;
    	} elseif($unit == 'mm') {
    		$this->k = 72/25.4;
    	} elseif($unit == 'cm') {
    		$this->k = 72/2.54;
    	} elseif($unit == 'in') {
    		$this->k = 72;
    	} else {
    		$this->Error('Incorrect unit: ' . $unit);
    	}
    	// Page sizes
    	$this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
    		'letter'=>array(612,792), 'legal'=>array(612,1008));
    	$size = $this->_getpagesize($size);
    	$this->DefPageSize = $size;
    	$this->CurPageSize = $size;
    	// Page orientation
    	$orientation = strtolower($orientation);
    	if($orientation=='p' || $orientation=='portrait')
    	{
    		$this->DefOrientation = 'P';
    		$this->w = $size[0];
    		$this->h = $size[1];
    	}
    	elseif($orientation=='l' || $orientation=='landscape')
    	{
    		$this->DefOrientation = 'L';
    		$this->w = $size[1];
    		$this->h = $size[0];
    	}
    	else
    		$this->Error('Incorrect orientation: '.$orientation);
    	$this->CurOrientation = $this->DefOrientation;
    	$this->wPt = $this->w*$this->k;
    	$this->hPt = $this->h*$this->k;
    	// Page rotation
    	$this->CurRotation = 0;
    	// Page margins (1 cm)
    	$margin = 28.35/$this->k;
    	$this->setMargins($margin,$margin);
    	// Interior cell margin (1 mm)
    	$this->cMargin = $margin/10;
    	// Line width (0.2 mm)
    	$this->LineWidth = .567/$this->k;
    	// Automatic page break
    	$this->setAutoPageBreak(true,2*$margin);
    	// Default display mode
    	$this->setDisplayMode('default');
    	// Enable compression
    	$this->setCompression(true);
    	// Set default PDF version number
    	$this->PDFVersion = '1.3';
    }

    /**
     * Defines the left, top and right margins. 
     * By default, they equal 1 cm. Call this method to change them. 
     * @param float $left   Left margin.
     * @param float $top    Top margin.
     * @param float $right  Right margin. Default value is the left one.
     */
    public function SetMargins(float $left, float $top, ?float $right=null) : void
    {
    	// Set left, top and right margins
    	$this->lMargin = $left;
    	$this->tMargin = $top;
    	if($right===null)
    		$right = $left;
    	$this->rMargin = $right;
    }

    /**
     * Defines the left margin. 
     * The method can be called before creating the first page.
     * If the current X-position gets out of page, it is brought back to the margin. 
     * @param float $margin Left margin.
     */
    public function SetLeftMargin(float $margin) : void
    {
    	// Set left margin
    	$this->lMargin = $margin;
    	if($this->page>0 && $this->x<$margin)
    		$this->x = $margin;
    }

    /**
     * Defines the top margin. 
     * The method can be called before creating the first page.  
     * @param float $margin
     */
    public function SetTopMargin(float $margin) : void
    {
    	// Set top margin
    	$this->tMargin = $margin;
    }

    /**
     * Defines the right margin. 
     * The method can be called before creating the first page. 
     * @param float $margin
     */
    public function SetRightMargin(float $margin) : void
    {
    	// Set right margin
    	$this->rMargin = $margin;
    }

    /**
     * Enables or disables the automatic page breaking mode. 
     * When enabling, the second parameter is the distance from the bottom of the page 
     * that defines the triggering limit.
     * By default, the mode is on and the margin is 2 cm. 
     * @param bool $auto    indicating if mode should be on or off. 
     * @param float $margin Distance from the bottom of the page. 
     */
    public function SetAutoPageBreak(bool $auto, float $margin=0) : void
    {
    	// Set auto page break mode and triggering margin
    	$this->AutoPageBreak = $auto;
    	$this->bMargin = $margin;
    	$this->PageBreakTrigger = $this->h-$margin;
    }

    /**
     * Defines the way the document is to be displayed by the viewer. 
     * The zoom level can be set: <br/> 
     * pages can be displayed <ul>
     * <li> entirely on screen </li>
     * <li> occupy the full width of the window </li>
     * <li> use real size </li>
     * <li> be scaled by a specific zooming factor </li>
     * <li> or use viewer default (configured in the Preferences menu of Adobe Reader). </li></ul> 
     * The page layout can be specified too: <ul> 
     * <li> single at once </li>
     * <li> continuous display </li>
     * <li> two columns </li>
     * <li> or viewer default. </li></ul> 
     * @param string|float $zoom    The zoom to use. <br/>
     *                              It can be one of the following string values: <ul>
     *                              <li> 'fullpage': displays the entire page on screen </li>
     *                              <li> 'fullwidth': uses maximum width of window </li>
     *                              <li> 'real': uses real size (equivalent to 100% zoom) </li>
     *                              <li> 'default': uses viewer default mode </li>
     *                              <li> or a number indicating the zooming factor to use. </li></ul> 
     * @param string $layout        The page layout. Possible values are: <ul>
     *                              <li> 'single': displays one page at once </li>
     *                              <li> 'continuous': displays pages continuously </li>
     *                              <li> 'two': displays two pages on two columns </li>
     *                              <li> 'defaul't: uses viewer default mode </li></ul>
     *                              Default value is default. 
     */
    public function SetDisplayMode($zoom, string $layout='default') : void
    {
    	// Set display mode in viewer
    	if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
    		$this->ZoomMode = $zoom;
    	else
    		$this->Error('Incorrect zoom display mode: '.$zoom);
    	if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
    		$this->LayoutMode = $layout;
    	else
    		$this->Error('Incorrect layout display mode: '.$layout);
    }

    /**
     * Activates or deactivates page compression. 
     * When activated, the internal representation of each page is compressed, which leads to 
     * a compression ratio of about 2 for the resulting document.
     * Compression is on by default. <br/>
     * <br/>
     * <b>Note: the Zlib extension is required for this feature. If not present, compression will be turned off.</b> 
     * @param bool $compress
     */
    public function SetCompression(bool $compress) : void
    {
    	// Set page compression
    	if(function_exists('gzcompress'))
    		$this->compress = $compress;
    	else
    		$this->compress = false;
    }

    /**
     * Defines the title of the document. 
     * @param string $title The title.
     * @param bool $isUTF8  Indicates if the string is encoded in ISO-8859-1 (false) or UTF-8 (true). Default value: false. 
     */
    public function SetTitle(string $title, bool $isUTF8=false) : void
    {
    	// Title of document
    	$this->metadata['Title'] = $isUTF8 ? $title : utf8_encode($title);
    }

    /**
     * Defines the author of the document. 
     * @param string $author
     * @param bool $isUTF8  Indicates if the string is encoded in ISO-8859-1 (false) or UTF-8 (true). Default value: false.
     */
    public function SetAuthor(string $author, bool $isUTF8=false) : void
    {
    	// Author of document
    	$this->metadata['Author'] = $isUTF8 ? $author : utf8_encode($author);
    }

    /**
     * Defines the subject of the document. 
     * @param string $subject
     * @param bool $isUTF8  Indicates if the string is encoded in ISO-8859-1 (false) or UTF-8 (true). Default value: false.
     */
    public function SetSubject(string $subject, bool $isUTF8=false) : void
    {
    	// Subject of document
    	$this->metadata['Subject'] = $isUTF8 ? $subject : utf8_encode($subject);
    }
    
    /**
     * Associates keywords with the document, generally in the form 'keyword1 keyword2 ...'. 
     * @param string $keywords
     * @param bool $isUTF8  Indicates if the string is encoded in ISO-8859-1 (false) or UTF-8 (true). Default value: false.
     */
    public function SetKeywords(string $keywords, bool $isUTF8=false) : void
    {
    	// Keywords of document
    	$this->metadata['Keywords'] = $isUTF8 ? $keywords : utf8_encode($keywords);
    }
    
    /**
     * Defines the creator of the document. This is typically the name of the application that generates the PDF. 
     * @param string $creator
     * @param bool $isUTF8  Indicates if the string is encoded in ISO-8859-1 (false) or UTF-8 (true). Default value: false.
     */
    public function SetCreator(string $creator, bool $isUTF8=false) : void
    {
    	// Creator of document
    	$this->metadata['Creator'] = $isUTF8 ? $creator : utf8_encode($creator);
    }

    /**
     * Defines an alias for the total number of pages. It will be substituted as the document is closed. 
     * @param string $alias The alias. Default value: {nb}. 
     */
    public function AliasNbPages(string $alias='{nb}') : void
    {
    	// Define an alias for total number of pages
    	$this->AliasNbPages = $alias;
    }
    
    /**
     * This method is automatically called in case of a fatal error.
     * It simply throws an exception with the provided message.
     * An inherited class may override it to customize the error handling but 
     * the method should never return, otherwise the resulting document would probably be invalid. 
     * @param string $msg The error message.
     * @throws \Exception
     */
    public function Error(string $msg) : void
    {
    	// Fatal error
    	throw new \Exception('FPDF error: '.$msg);
    }
    
    /**
     * Terminates the PDF document. 
     * It is not necessary to call this method explicitly because Output() does it 
     * automatically. If the document contains no page, AddPage() is called to prevent 
     * from getting an invalid document.
     */
    public function Close() : void
    {
    	// Terminate document
    	if($this->state==3)
    		return;
    	if($this->page==0)
    		$this->addPage();
    	// Page footer
    	$this->InFooter = true;
    	$this->Footer();
    	$this->InFooter = false;
    	// Close page
    	$this->_endpage();
    	// Close document
    	$this->_enddoc();
    }
    
    /**
     * Adds a new page to the document. 
     * If a page is already present, the Footer() method is called first to output the 
     * footer. Then the page is added, the current position set to the top-left corner 
     * according to the left and top margins, and Header() is called to display the header.
     * The font which was set before calling is automatically restored. There is no need 
     * to call SetFont() again if you want to continue with the same font. The same is 
     * true for colors and line width.
     * The origin of the Y-position system is at the top-left corner and increasing 
     * Y-positions go downwards.
     * @param string $orientation   Default page orientation. <br/>
     *                              Possible values are (case insensitive): <ul> 
     *                              <li> 'P' or 'Portrait' </li>
     *                              <li> 'L' or 'Landscape' </li></ul>
     *                              Default value is 'P'. <br/> 
     * @param string|array $size    The size used for pages. <br/>
     *                              It can be either one of the following values (case insensitive): <ul>
     *                              <li> 'A3' </li>
     *                              <li> 'A4' </li>
     *                              <li> 'A5' </li>
     *                              <li> 'Letter' </li>
     *                              <li> 'Legal' </li></ul>
     *                              or an array containing the width and the height (expressed in the unit given by unit). <br/>
     *                              Default value is 'A4'.  <br/>
     * @param int $rotation         Angle by which to rotate the page. <br/>
     *                              It must be a multiple of 90; positive values mean clockwise rotation. </br>
     *                              The default value is 0.
     */
    public function AddPage(string $orientation='', $size='', int $rotation=0) : void
    {
    	// Start a new page
    	if($this->state==3)
    		$this->Error('The document is closed');
    	$family = $this->FontFamily;
    	$style = $this->FontStyle.($this->underline ? 'U' : '');
    	$fontsize = $this->FontSizePt;
    	$lw = $this->LineWidth;
    	$dc = $this->DrawColor;
    	$fc = $this->FillColor;
    	$tc = $this->TextColor;
    	$cf = $this->ColorFlag;
    	if($this->page>0)
    	{
    		// Page footer
    		$this->InFooter = true;
    		$this->Footer();
    		$this->InFooter = false;
    		// Close page
    		$this->_endpage();
    	}
    	// Start new page
    	$this->_beginpage($orientation,$size,$rotation);
    	// Set line cap style to square
    	$this->_out('2 J');
    	// Set line width
    	$this->LineWidth = $lw;
    	$this->_out(sprintf('%.2F w',$lw*$this->k));
    	// Set font
    	if($family)
    		$this->setFont($family,$style,$fontsize);
    	// Set colors
    	$this->DrawColor = $dc;
    	if($dc!='0 G')
    		$this->_out($dc);
    	$this->FillColor = $fc;
    	if($fc!='0 g')
    		$this->_out($fc);
    	$this->TextColor = $tc;
    	$this->ColorFlag = $cf;
    	// Page header
    	$this->InHeader = true;
    	$this->Header();
    	$this->InHeader = false;
    	// Restore line width
    	if($this->LineWidth!=$lw)
    	{
    		$this->LineWidth = $lw;
    		$this->_out(sprintf('%.2F w',$lw*$this->k));
    	}
    	// Restore font
    	if($family)
    		$this->setFont($family,$style,$fontsize);
    	// Restore colors
    	if($this->DrawColor!=$dc)
    	{
    		$this->DrawColor = $dc;
    		$this->_out($dc);
    	}
    	if($this->FillColor!=$fc)
    	{
    		$this->FillColor = $fc;
    		$this->_out($fc);
    	}
    	$this->TextColor = $tc;
    	$this->ColorFlag = $cf;
    }
    
    /**
     * This method is used to render the page header. 
     * It is automatically called by AddPage() and should not be called directly by the 
     * application. The implementation in FPDF is empty, so you have to subclass it and 
     * override the method if you want a specific processing.
     */
    public function Header() : void
    {
    	// To be implemented in your own inherited class
    }
    
    /**
     * This method is used to render the page footer. 
     * It is automatically called by AddPage() and Close() and should not be called 
     * directly by the application. The implementation in FPDF is empty, so you have to 
     * subclass it and override the method if you want a specific processing.
     */
    public function Footer() : void
    {
    	// To be implemented in your own inherited class
    }
    
    /**
     * Returns the current page number.
     * @return int
     */
    public function PageNo() : int
    {
    	// Get current page number
    	return $this->page;
    }
    
    /**
     * Defines the color used for all drawing operations (lines, rectangles and cell borders). 
     * It can be expressed in RGB components or gray scale. The method can be called before 
     * the first page is created and the value is retained from page to page.
     * @param int $r    If g and b are given, red component; if not, indicates the gray level. Value between 0 and 255.
     * @param int $g    Green component (between 0 and 255).
     * @param int $b    Blue component (between 0 and 255).
     */
    public function SetDrawColor(int $r, ?int $g=null, ?int $b=null) : void
    {
    	// Set color for all stroking operations
        if (($r === 0 && $g === 0 && $b === 0) || $g === null) {
    		$this->DrawColor = sprintf('%.3F G', $r / 255);
        } else {
    		$this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r / 255, $g / 255, $b / 255);
        }
        if ($this->page > 0) {
    		$this->_out($this->DrawColor);
        }
    }
    
    /**
     * Defines the color used for all filling operations (filled rectangles and cell backgrounds). 
     * It can be expressed in RGB components or gray scale. The method can be called before the 
     * first page is created and the value is retained from page to page.
     * @param int $r    If g and b are given, red component; if not, indicates the gray level. Value between 0 and 255.
     * @param int $g    Green component (between 0 and 255).
     * @param int $b    Blue component (between 0 and 255).
     */
    public function SetFillColor(int $r, ?int $g=null, ?int $b=null) : void
    {
    	// Set color for all filling operations
        if (($r === 0 && $g === 0 && $b === 0) || $g === null) {
    		$this->FillColor = sprintf('%.3F g', $r / 255);
        } else {
    		$this->FillColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
        }
    	$this->ColorFlag = ($this->FillColor != $this->TextColor);
    	if ($this->page > 0) {
    		$this->_out($this->FillColor);
    	}
    }
    
    /**
     * Defines the color used for text. 
     * It can be expressed in RGB components or gray scale. The method can be called before the 
     * first page is created and the value is retained from page to page.
     * @param int $r    If g and b are given, red component; if not, indicates the gray level. Value between 0 and 255.
     * @param int $g    Green component (between 0 and 255).
     * @param int $b    Blue component (between 0 and 255).
     */
    public function SetTextColor(int $r, ?int $g=null, ?int $b=null) : void
    {
    	// Set color for text
        if (($r === 0 && $g === 0 && $b === 0) || $g === null) {
    		$this->TextColor = sprintf('%.3F g', $r / 255);
        } else {
    		$this->TextColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
        }
    	$this->ColorFlag = ($this->FillColor != $this->TextColor);
    }
    
    /**
     * Returns the length of a string in user unit for current Font. 
     * A font must be selected.
     * @param string $s The string whose length is to be computed.
     * @return float
     */
    public function GetStringWidth(string $s) : float
    {
    	// Get width of a string in the current font
    	$s = (string)$s;
    	$cw = &$this->CurrentFont['cw'];
    	$w = 0;
    	$l = strlen($s);
    	for($i=0;$i<$l;$i++)
    		$w += $cw[$s[$i]];
    	return $w*$this->FontSize/1000;
    }
    
    /**
     * Defines the line width. 
     * By default, the value equals 0.2 mm. The method can be called before the first 
     * page is created and the value is retained from page to page.
     * @param float $width
     */
    public function SetLineWidth(float $width) : void
    {
    	// Set line width
    	$this->LineWidth = $width;
    	if($this->page>0)
    		$this->_out(sprintf('%.2F w',$width*$this->k));
    }
    
    /**
     * Draws a line between two points.
     * The X/Y-positions refer to the top left corner of the page. 
     * Set margins are NOT taken into account.
     * @param float $x1     X-position upper left corner
     * @param float $y1     Y-position upper left corner
     * @param float $x2     X-position lower right corner
     * @param float $y2     Y-position lower right corner
     */
    public function Line(float $x1, float $y1, float $x2, float $y2) : void
    {
    	// Draw a line
    	$this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
    }

    /**
     * Outputs a rectangle. 
     * It can be drawn (border only), filled (with no border) or both.
     * The X/Y-position refer to the top left corner of the page. 
     * Set margins are NOT taken into account.
     * @param float $x      X-position upper left corner
     * @param float $y      Y-position upper left corner
     * @param float $w      Width
     * @param float $h      Height
     * @param string $style Style of rendering. <br/>
     *                      Possible values are: <ul>
     *                      <li>   'D' or empty string: draw the shape. This is the default value. </li>
     *                      <li>   'F': fill. </li>
     *                      <li>   'DF' or 'FD': draw the shape and fill. </li></ul>
     */
    public function Rect(float $x, float $y, float $w, float $h, string $style='') : void
    {
    	// Draw a rectangle
    	if($style=='F')
    		$op = 'f';
    	elseif($style=='FD' || $style=='DF')
    		$op = 'B';
    	else
    		$op = 'S';
    	$this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
    }
    
    /**
     * Imports a TrueType, OpenType or Type1 font and makes it available. 
     * It is necessary to generate a font definition file first with the MakeFont utility.
     * The definition file (and the font file itself when embedding) must be present in 
     * the font directory. If it is not found, the error "Could not include font definition file" 
     * is raised.
     * @param string $family    Font family. The name can be chosen arbitrarily. If it is a standard family name, it will override the corresponding font.
     * @param string $style     Font style. <br/>
     *                          Possible values are (case insensitive): <ul>
     *                          <li> empty string: regular </li>
     *                          <li> 'B': bold </li>
     *                          <li> 'I': italic </li>
     *                          <li> 'BI' or 'IB': bold italic </li></ul>
     *                          The default value is regular. <br/>
     * @param string $file      The font definition file. <br/>
     *                          By default, the name is built from the family and style, in lower case with no space.
     */
    public function AddFont(string $family, string $style='', string $file='') : void
    {
    	// Add a TrueType, OpenType or Type1 font
    	$family = strtolower($family);
    	if($file=='')
    		$file = str_replace(' ','',$family).strtolower($style).'.php';
    	$style = strtoupper($style);
    	if($style=='IB')
    		$style = 'BI';
    	$fontkey = $family.$style;
    	if(isset($this->fonts[$fontkey]))
    		return;
    	$info = $this->_loadfont($file);
    	$info['i'] = count($this->fonts)+1;
    	if(!empty($info['file']))
    	{
    		// Embedded font
    		if($info['type']=='TrueType')
    			$this->FontFiles[$info['file']] = array('length1'=>$info['originalsize']);
    		else
    			$this->FontFiles[$info['file']] = array('length1'=>$info['size1'], 'length2'=>$info['size2']);
    	}
    	$this->fonts[$fontkey] = $info;
    }
    
    /**
     * Sets the font used to print character strings. 
     * It is mandatory to call this method at least once before printing text or the 
     * resulting document would not be valid.
     * The font can be either a standard one or a font added via the AddFont() method. 
     * Standard fonts use the Windows encoding cp1252 (Western Europe).
     * The method can be called before the first page is created and the font is kept from page to page.
     * If you just wish to change the current font size, it is simpler to call SetFontSize().<br/>
     * 
     * <b>Note:</b><br/>
     * the font definition files must be accessible. 
     * They are searched successively in: <ul>
     * <li> The directory defined by the FPDF_FONTPATH constant (if this constant is defined) </li>
     * <li> The 'font' directory located in the same directory as fpdf.php (if it exists) </li>
     * <li> The directories accessible through include() </li></ul>
     * @param string $family    Family font. <br/>
     *                          It can be either a name defined by AddFont() or one of the standard families (case insensitive): <ul>
     *                          <li> 'Courier' (fixed-width) </li>
     *                          <li> 'Helvetica' or 'Arial' (synonymous; sans serif) </li>
     *                          <li> 'Times' (serif) </li>
     *                          <li> 'Symbol' (symbolic) </li>
     *                          <li> 'ZapfDingbats' (symbolic)</li></ul>
     *                          It is also possible to pass an empty string. In that case, the current family is kept.<br/>
     * @param string $style     Font style. <br>
     *                          ossible values are (case insensitive): <ul>
     *                          <li> empty string: regular </li>
     *                          <li> 'B': bold </li>
     *                          <li> 'I': italic </li>
     *                          <li> 'U': underline </li> 
     *                          <li> or any combination. </li></ul>
     *                          The default value is regular. Bold and italic styles do not apply to Symbol and ZapfDingbats.<br/>
     * @param float $size       Font size in points. <br/>
     *                          The default value is the current size. <br/>
     *                          If no size has been specified since the beginning of the document, the value taken is 12.
     */
    public function SetFont(string $family, string $style='', float $size=0) : void
    {
    	// Select a font; size given in points
    	if($family=='')
    		$family = $this->FontFamily;
    	else
    		$family = strtolower($family);
    	$style = strtoupper($style);
    	if(strpos($style,'U')!==false)
    	{
    		$this->underline = true;
    		$style = str_replace('U','',$style);
    	}
    	else
    		$this->underline = false;
    	if($style=='IB')
    		$style = 'BI';
    	if($size==0)
    		$size = $this->FontSizePt;
    	// Test if font is already selected
    	if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
    		return;
    	// Test if font is already loaded
    	$fontkey = $family.$style;
    	if(!isset($this->fonts[$fontkey]))
    	{
    		// Test if one of the core fonts
    		if($family=='arial')
    			$family = 'helvetica';
    		if(in_array($family,$this->CoreFonts))
    		{
    			if($family=='symbol' || $family=='zapfdingbats')
    				$style = '';
    			$fontkey = $family.$style;
    			if(!isset($this->fonts[$fontkey]))
    				$this->addFont($family,$style);
    		}
    		else
    			$this->Error('Undefined font: '.$family.' '.$style);
    	}
    	// Select it
    	$this->FontFamily = $family;
    	$this->FontStyle = $style;
    	$this->FontSizePt = $size;
    	$this->FontSize = $size/$this->k;
    	$this->CurrentFont = &$this->fonts[$fontkey];
    	if($this->page>0)
    		$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
    }
    
    /**
     * Defines the size of the current font.
     * @param float $size   The size (in points).
     */
    public function SetFontSize(float $size) : void
    {
    	// Set font size in points
    	if($this->FontSizePt==$size)
    		return;
    	$this->FontSizePt = $size;
    	$this->FontSize = $size/$this->k;
    	if($this->page>0)
    		$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
    }
    
    /**
     * Creates a new internal link and returns its identifier. 
     * An internal link is a clickable area which directs to another place within the document.
     * The identifier can then be passed to Cell(), Write(), Image() or Link(). 
     * The destination is defined with SetLink().
     * @return int
     */
    public function AddLink() : int
    {
    	// Create a new internal link
    	$n = count($this->links)+1;
    	$this->links[$n] = array(0, 0);
    	return $n;
    }
    
    /**
     * Defines the page and position a link points to.
     * @param int $link The link identifier created by AddLink().
     * @param float $y  Y-position of target position; -1 indicates the current position. The default value is 0 (top of page).
     * @param int $page Number of target page; -1 indicates the current page. This is the default value.
     */
    public function SetLink(int $link, float $y=0, int $page=-1) : void
    {
    	// Set destination of internal link
    	if($y==-1)
    		$y = $this->y;
    	if($page==-1)
    		$page = $this->page;
    	$this->links[$link] = array($page, $y);
    }
    
    /**
     * Puts a link on a rectangular area of the page. 
     * Text or image links are generally put via Cell(), Write() or Image(), but this 
     * method can be useful for instance to define a clickable area inside an image.
     * Target can be an external URL or an internal link ID created and specified by AddLink()/SetLink() 
     * @param float $x          X-position
     * @param float $y          Y-position
     * @param float $w          Width
     * @param float $h          Height
     * @param string|int $link  URL or link-ID
     */
    public function Link(float $x, float $y, float $w, float $h, $link) : void
    {
    	// Put a link on the page
    	$this->PageLinks[$this->page][] = array($x*$this->k, $this->hPt-$y*$this->k, $w*$this->k, $h*$this->k, $link);
    }
    
    /**
     * Prints a character string. 
     * The origin is on the left of the first character, on the baseline. 
     * This method allows to place a string precisely on the page, but it is usually 
     * easier to use Cell(), MultiCell() or Write() which are the standard methods 
     * to print text.
     * @param float $x      X-position
     * @param float $y      Y-position
     * @param string $txt   String to print.
     */
    public function Text(float $x, float $y, string $txt) : void
    {
    	// Output a string
    	if(!isset($this->CurrentFont))
    		$this->Error('No font has been set');
    	$s = sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    	if($this->underline && $txt!='')
    		$s .= ' '.$this->_dounderline($x,$y,$txt);
    	if($this->ColorFlag)
    		$s = 'q '.$this->TextColor.' '.$s.' Q';
    	$this->_out($s);
    }
    
    /**
     * Whenever a page break condition is met, the method is called, and the break is 
     * issued or not depending on the returned value. 
     * The default implementation returns a value according to the mode selected by 
     * SetAutoPageBreak().
     * This method is called automatically and should not be called directly by the application.<br/>
     * <br/>
     * For usage in derived classes see example at http://www.fpdf.org/en/doc/acceptpagebreak.htm.
     * @link http://www.fpdf.org/en/doc/acceptpagebreak.htm
     * @return bool
     */
    public function AcceptPageBreak() : bool
    {
    	// Accept automatic page break or not
    	return $this->AutoPageBreak;
    }
    
    /**
     * Prints a cell (rectangular area) with optional borders, background color and character string. 
     * The upper-left corner of the cell corresponds to the current position. The text can be 
     * aligned or centered. After the call, the current position moves to the right or to the next line. 
     * It is possible to put a link on the text.
     * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done 
     * before outputting.
     * @param float $w          Cell width. If 0, the cell extends up to the right margin.
     * @param float $h          Cell height. Default value: 0.
     * @param string $txt       String to print. Default value: empty string.
     * @param int|string $border    Indicates if borders must be drawn around the cell. <br/>
     *                          The value can be either a number: <ul>
     *                          <li>0: no border </li>
     *                          <li>1: frame </li></ul>
     *                          or a string containing some or all of the following characters (in any order): <ul>
     *                          <li> 'L': left </li>
     *                          <li> 'T': top </li>
     *                          <li> 'R': right </li>
     *                          <li> 'B': bottom </li></ul>
     *                          Default value: 0. <br/>
     * @param float $ln         Indicates where the current position should go after the call. <br/>
     *                          Possible values are: <ul>
     *                          <li> 0: to the right </li>
     *                          <li> 1: to the beginning of the next line </li>
     *                          <li> 2: below </li></ul>
     *                          Putting 1 is equivalent to putting 0 and calling Ln() just after. <br/>
     *                          Default value: 0. <br/>
     * @param string $align     Allows to center or align the text. <br/>
     *                          Possible values are: <ul>
     *                          <li> 'L' or empty string: left align (default value) </li> 
     *                          <li> 'C': center </li> 
     *                          <li> 'R': right align </li></ul>
     * @param boolean $fill     Indicates if the cell background must be painted (true) or transparent (false). <br/>
     *                          If set to true, current FillColor is used for the background. <br/>
     *                          Default value: false. <br/>
     * @param string|int $link  URL or identifier for internal link created by AddLink().
     */
    public function Cell(float $w, float $h=0, string $txt='', $border=0, float $ln=0, $align='', $fill=false, $link='') : void
    {
    	// Output a cell
    	$k = $this->k;
    	if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
    	{
    		// Automatic page break
    		$x = $this->x;
    		$ws = $this->ws;
    		if($ws>0)
    		{
    			$this->ws = 0;
    			$this->_out('0 Tw');
    		}
    		$this->addPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
    		$this->x = $x;
    		if($ws>0)
    		{
    			$this->ws = $ws;
    			$this->_out(sprintf('%.3F Tw',$ws*$k));
    		}
    	}
    	if($w==0)
    		$w = $this->w-$this->rMargin-$this->x;
    	$s = '';
    	if($fill || $border==1)
    	{
    		if($fill)
    			$op = ($border==1) ? 'B' : 'f';
    		else
    			$op = 'S';
    		$s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
    	}
    	if(is_string($border))
    	{
    		$x = $this->x;
    		$y = $this->y;
    		if(strpos($border,'L')!==false)
    			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
    		if(strpos($border,'T')!==false)
    			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
    		if(strpos($border,'R')!==false)
    			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
    		if(strpos($border,'B')!==false)
    			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
    	}
    	if($txt!=='')
    	{
    		if(!isset($this->CurrentFont))
    			$this->Error('No font has been set');
    		if($align=='R')
    			$dx = $w-$this->cMargin-$this->getStringWidth($txt);
    		elseif($align=='C')
    			$dx = ($w-$this->getStringWidth($txt))/2;
    		else
    			$dx = $this->cMargin;
    		if($this->ColorFlag)
    			$s .= 'q '.$this->TextColor.' ';
    		$s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$this->_escape($txt));
    		if($this->underline)
    			$s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
    		if($this->ColorFlag)
    			$s .= ' Q';
    		if($link)
    			$this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->getStringWidth($txt),$this->FontSize,$link);
    	}
    	if($s)
    		$this->_out($s);
    	$this->lasth = $h;
    	if($ln>0)
    	{
    		// Go to next line
    		$this->y += $h;
    		if($ln==1)
    			$this->x = $this->lMargin;
    	}
    	else
    		$this->x += $w;
    }
    
    /**
     * This method allows printing text with line breaks. 
     * They can be automatic (as soon as the text reaches the right border of the cell) or 
     * explicit (via the \n character). 
     * As many cells as necessary are output, one below the other.
     * Text can be aligned, centered or justified. The cell block can be framed and the background painted.
     * @param float $w          Cell width. If 0, the cell extends up to the right margin.
     * @param float $h          Cell height. Default value: 0.
     * @param string $txt       String to print. Default value: empty string.
     * @param int|string $border    Indicates if borders must be drawn around the cell. <br/>
     *                          The value can be either a number: <ul>
     *                          <li>0: no border </li>
     *                          <li>1: frame </li></ul>
     *                          or a string containing some or all of the following characters (in any order): <ul>
     *                          <li> 'L': left </li>
     *                          <li> 'T': top </li>
     *                          <li> 'R': right </li>
     *                          <li> 'B': bottom </li></ul>
     *                          Default value: 0. <br/>
     * @param string $align     Allows to center or align the text. <br/>
     *                          Possible values are: <ul>
     *                          <li> 'L' or empty string: left align (default value) </li> 
     *                          <li> 'C': center </li> 
     *                          <li> 'R': right align </li></ul>
     * @param boolean $fill     Indicates if the cell background must be painted (true) or transparent (false). <br/>
     *                          If set to true, current FillColor is used for the background. <br/>
     *                          Default value: false.
     */
    public function MultiCell(float $w, float $h, string $txt, $border=0, string $align='J', bool $fill=false) : void
    {
    	// Output text with automatic or explicit line breaks
    	if(!isset($this->CurrentFont))
    		$this->Error('No font has been set');
    	$cw = &$this->CurrentFont['cw'];
    	if($w==0)
    		$w = $this->w-$this->rMargin-$this->x;
    	$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
    	$s = str_replace("\r",'',$txt);
    	$nb = strlen($s);
    	if($nb>0 && $s[$nb-1]=="\n")
    		$nb--;
    	$b = 0;
    	$b2 = '';
    	if($border)
    	{
    		if($border==1)
    		{
    			$border = 'LTRB';
    			$b = 'LRT';
    			$b2 = 'LR';
    		}
    		else
    		{
    			$b2 = '';
    			if(strpos($border,'L')!==false)
    				$b2 .= 'L';
    			if(strpos($border,'R')!==false)
    				$b2 .= 'R';
    			$b = (strpos($border,'T')!==false) ? $b2.'T' : $b2;
    		}
    	}
    	$sep = -1;
    	$i = 0;
    	$j = 0;
    	$l = 0;
    	$ns = 0;
    	$nl = 1;
    	$ls = 0;
    	while($i<$nb)
    	{
    		// Get next character
    		$c = $s[$i];
    		if($c=="\n")
    		{
    			// Explicit line break
    			if($this->ws>0)
    			{
    				$this->ws = 0;
    				$this->_out('0 Tw');
    			}
    			$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    			$i++;
    			$sep = -1;
    			$j = $i;
    			$l = 0;
    			$ns = 0;
    			$nl++;
    			if($border && $nl==2)
    				$b = $b2;
    			continue;
    		}
    		if($c==' ')
    		{
    			$sep = $i;
    			$ls = $l;
    			$ns++;
    		}
    		$l += $cw[$c];
    		if($l>$wmax)
    		{
    			// Automatic line break
    			if($sep==-1)
    			{
    				if($i==$j)
    					$i++;
    				if($this->ws>0)
    				{
    					$this->ws = 0;
    					$this->_out('0 Tw');
    				}
    				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    			}
    			else
    			{
    				if($align=='J')
    				{
    					$this->ws = ($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
    					$this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
    				}
    				$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
    				$i = $sep+1;
    			}
    			$sep = -1;
    			$j = $i;
    			$l = 0;
    			$ns = 0;
    			$nl++;
    			if($border && $nl==2)
    				$b = $b2;
    		}
    		else
    			$i++;
    	}
    	// Last chunk
    	if($this->ws>0)
    	{
    		$this->ws = 0;
    		$this->_out('0 Tw');
    	}
    	if($border && strpos($border,'B')!==false)
    		$b .= 'B';
    	$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    	$this->x = $this->lMargin;
    }
    
    /**
     * This method prints text from the current position. 
     * When the right margin is reached (or the \n character is met) a line break occurs 
     * and text continues from the left margin. Upon method exit, the current position 
     * is left just at the end of the text.
     * It is possible to put a link on the text.
     * @param float $h          Line height.
     * @param string $txt       String to print.
     * @param string|int $link  URL or identifier for internal link created by AddLink().
     */
    public function Write(float $h, string $txt, $link='') : void
    {
    	// Output text in flowing mode
    	if(!isset($this->CurrentFont))
    		$this->Error('No font has been set');
    	$cw = &$this->CurrentFont['cw'];
    	$w = $this->w-$this->rMargin-$this->x;
    	$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
    	$s = str_replace("\r",'',$txt);
    	$nb = strlen($s);
    	$sep = -1;
    	$i = 0;
    	$j = 0;
    	$l = 0;
    	$nl = 1;
    	while($i<$nb)
    	{
    		// Get next character
    		$c = $s[$i];
    		if($c=="\n")
    		{
    			// Explicit line break
    			$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link);
    			$i++;
    			$sep = -1;
    			$j = $i;
    			$l = 0;
    			if($nl==1)
    			{
    				$this->x = $this->lMargin;
    				$w = $this->w-$this->rMargin-$this->x;
    				$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
    			}
    			$nl++;
    			continue;
    		}
    		if($c==' ')
    			$sep = $i;
    		$l += $cw[$c];
    		if($l>$wmax)
    		{
    			// Automatic line break
    			if($sep==-1)
    			{
    				if($this->x>$this->lMargin)
    				{
    					// Move to next line
    					$this->x = $this->lMargin;
    					$this->y += $h;
    					$w = $this->w-$this->rMargin-$this->x;
    					$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
    					$i++;
    					$nl++;
    					continue;
    				}
    				if($i==$j)
    					$i++;
    				$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link);
    			}
    			else
    			{
    				$this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',false,$link);
    				$i = $sep+1;
    			}
    			$sep = -1;
    			$j = $i;
    			$l = 0;
    			if($nl==1)
    			{
    				$this->x = $this->lMargin;
    				$w = $this->w-$this->rMargin-$this->x;
    				$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
    			}
    			$nl++;
    		}
    		else
    			$i++;
    	}
    	// Last chunk
    	if($i!=$j)
    		$this->Cell($l/1000*$this->FontSize,$h,substr($s,$j),0,0,'',false,$link);
    }
    
    /**
     * Performs a line break. 
     * The current X-position goes back to the left margin and the Y-position increases by 
     * the amount passed in parameter.
     * @param float $h  The height of the break. <br/>
     *                  By default, the value equals the height of the last printed cell.
     */
    public function Ln(float $h=null) : void
    {
    	// Line feed; default value is the last cell height
    	$this->x = $this->lMargin;
    	if($h===null)
    		$this->y += $this->lasth;
    	else
    		$this->y += $h;
    }
    
    /**
     * Puts an image. 
     * The size it will take on the page can be specified in different ways: <ul>
     * <li> explicit width and height (expressed in user unit or dpi) </li> 
     * <li> one explicit dimension, the other being calculated automatically in order to keep the original proportions </li> 
     * <li> no explicit dimension, in which case the image is put at 96 dpi </li></ul>
     * Supported formats are JPEG, PNG and GIF. <b>The GD extension is required for GIF.</b><br/>
     * For JPEGs, all flavors are allowed: <ul>
     * <li> gray scales </li> 
     * <li> true colors (24 bits) </li>
     * <li> CMYK (32 bits) </li></ul>
     * For PNGs, are allowed: <ul>
     * <li> gray scales on at most 8 bits (256 levels) </li>
     * <li> indexed colors </li>
     * <li> true colors (24 bits) </li></ul>
     * For GIFs: in case of an animated GIF, only the first frame is displayed. <br/><br/>
     * Transparency is supported. <br/><br/>
     * The format can be specified explicitly or inferred from the file extension. <br/><br/>
     * It is possible to put a link on the image. <br/><br/>
     * <b>Remark:</b> if an image is used several times, only one copy is embedded in the file.
     * @param string $file  Path or URL of the image.
     * @param float $x      X-position of the upper-left corner. <br/> <br/>
     *                      If not specified or equal to null, the current X-position is used. <br/>
     * @param float $y      Y-position of the upper-left corner. <br/>
     *                      If not specified or equal to null, the current Y-position is used; <br/>
     *                      moreover, a page break is triggered first if necessary (in case automatic page breaking is enabled) and, <br/>
     *                      after the call, the current Y-position is moved to the bottom of the image. <br/>
     * @param float $w      Width of the image in the page. <br/>
     *                      There are three cases: <ul>
     *                      <li> If the value is positive, it represents the width in user unit </li> 
     *                      <li> If the value is negative, the absolute value represents the horizontal resolution in dpi </li> 
     *                      <li> If the value is not specified or equal to zero, it is automatically calculated </li><ul>
     * @param float $h      Height of the image in the page. <br/>
     *                      There are three cases: <ul>
     *                      <li> If the value is positive, it represents the height in user unit </li> 
     *                      <li> If the value is negative, the absolute value represents the vertical resolution in dpi </li> 
     *                      <li> If the value is not specified or equal to zero, it is automatically calculated </li><ul>
     * @param string $type  Image format. <br/>
     *                      Possible values are (case insensitive): <ul> 
     *                      <li> JPG </li> 
     *                      <li> JPEG </li> 
     *                      <li> PNG </li>
     *                      <li> GIF </li></ul>
     *                      If not specified, the type is inferred from the file extension. <br/>
     * @param string|int $link  URL or identifier for internal link created by AddLink().
     */
    public function Image(string $file, ?float $x=null, ?float $y=null, float $w=0, float $h=0, string $type='', $link='') : void
    {
    	// Put an image on the page
    	if($file=='')
    		$this->Error('Image file name is empty');
    	if(!isset($this->images[$file]))
    	{
    		// First use of this image, get info
    		if($type=='')
    		{
    			$pos = strrpos($file,'.');
    			if(!$pos)
    				$this->Error('Image file has no extension and no type was specified: '.$file);
    			$type = substr($file,$pos+1);
    		}
    		$type = strtolower($type);
    		if($type=='jpeg')
    			$type = 'jpg';
    		$mtd = '_parse'.$type;
    		if(!method_exists($this,$mtd))
    			$this->Error('Unsupported image type: '.$type);
    		$info = $this->$mtd($file);
    		$info['i'] = count($this->images)+1;
    		$this->images[$file] = $info;
    	}
    	else
    		$info = $this->images[$file];
    
    	// Automatic width and height calculation if needed
    	if($w==0 && $h==0)
    	{
    		// Put image at 96 dpi
    		$w = -96;
    		$h = -96;
    	}
    	if($w<0)
    		$w = -$info['w']*72/$w/$this->k;
    	if($h<0)
    		$h = -$info['h']*72/$h/$this->k;
    	if($w==0)
    		$w = $h*$info['w']/$info['h'];
    	if($h==0)
    		$h = $w*$info['h']/$info['w'];
    
    	// Flowing mode
    	if($y===null)
    	{
    		if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
    		{
    			// Automatic page break
    			$x2 = $this->x;
    			$this->addPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
    			$this->x = $x2;
    		}
    		$y = $this->y;
    		$this->y += $h;
    	}
    
    	if($x===null)
    		$x = $this->x;
    	$this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
    	if($link)
    		$this->Link($x,$y,$w,$h,$link);
    }
    
    /**
     * Get current page width.
     * @return float
     */
    public function GetPageWidth() : float
    {
    	// Get current page width
    	return $this->w;
    }
    
    /**
     * Get current page height.
     * @return float
     */
    public function GetPageHeight() : float
    {
    	// Get current page height
    	return $this->h;
    }
    
    /**
     * Get current x position.
     * @return float
     */
    public function GetX() : float
    {
    	// GetX position
    	return $this->x;
    }
    
    /**
     * Set new X position.
     * If the passed value is negative, it is relative to the right of the page.
     * @param float $x
     */
    public function SetX(float $x) : void
    {
    	// Set x position
    	if($x>=0)
    		$this->x = $x;
    	else
    		$this->x = $this->w+$x;
    }

    /**
     * Get current Y position.
     * @return float
     */
    public function GetY() : float
    {
    	// Get y position
    	return $this->y;
    }

    /**
     * Set new Y position and optionally moves the current X-position back to the left margin.
     * If the passed value is negative, it is relative to the bottom of the page.
     * @param float $y
     * @param bool $resetX
     */
    public function SetY(float $y, bool $resetX=true) : void
    {
    	// Set y position and optionally reset x
    	if($y>=0)
    		$this->y = $y;
    	else
    		$this->y = $this->h+$y;
    	if($resetX)
    		$this->x = $this->lMargin;
    }
    
    /**
     * Set new X and Y position.
     * If the passed values are negative, they are relative respectively to the right and bottom of the page.
     * @param float $x
     * @param float $y
     */
    public function SetXY(float $x, float $y) : void
    {
    	// Set x and y positions
    	$this->setX($x);
    	$this->setY($y,false);
    }
    
    /**
     * Send the document to a given destination: browser, file or string. 
     * In the case of a browser, the PDF viewer may be used or a download may be forced.
     * The method first calls Close() if necessary to terminate the document.
     * @param string $dest  Destination where to send the document. <br/>
     *                      It can be one of the following: <ul> 
     *                      <li> 'I': send the file inline to the browser. The PDF viewer is used if available. </li> 
     *                      <li> 'D': send to the browser and force a file download with the name given by name. </li> 
     *                      <li> 'F': save to a local file with the name given by name (may include a path). </li> 
     *                      <li> 'S': return the document as a string. </li></ul>
     *                      The default value is I. <br/>
     * @param string $name  The name of the file. It is ignored in case of destination 'S'. <br/>
     *                      The default value is doc.pdf. <br/>
     * @param bool $isUTF8  Indicates if name is encoded in ISO-8859-1 (false) or UTF-8 (true). <br/>
     *                      Only used for destinations I and D. <br/>
     *                      The default value is false. <br/>
     * @return string
     */
    public function Output(string $dest='', string $name='', bool $isUTF8=false) : string
    {
    	// Output PDF to some destination
    	$this->Close();
    	if(strlen($name)==1 && strlen($dest)!=1)
    	{
    		// Fix parameter order
    		$tmp = $dest;
    		$dest = $name;
    		$name = $tmp;
    	}
    	if($dest=='')
    		$dest = 'I';
    	if($name=='')
    		$name = 'doc.pdf';
    	switch(strtoupper($dest))
    	{
    		case 'I':
    			// Send to standard output
    			$this->_checkoutput();
    			if(PHP_SAPI!='cli')
    			{
    				// We send to a browser
    				header('Content-Type: application/pdf; charset=UTF-8');
    				header('Content-Disposition: inline; '.$this->_httpencode('filename',$name,$isUTF8));
    				header('Cache-Control: private, max-age=0, must-revalidate');
    				header('Pragma: public');
    			}
    			echo $this->buffer;
    			break;
    		case 'D':
    			// Download file
    			$this->_checkoutput();
    			header('Content-Type: application/x-download');
    			header('Content-Disposition: attachment; '.$this->_httpencode('filename',$name,$isUTF8));
    			header('Cache-Control: private, max-age=0, must-revalidate');
    			header('Pragma: public');
    			echo $this->buffer;
    			break;
    		case 'F':
    			// Save to local file
    			if(!file_put_contents($name,$this->buffer))
    				$this->Error('Unable to create output file: '.$name);
    			break;
    		case 'S':
    			// Return as a string
    			return $this->buffer;
    		default:
    			$this->Error('Incorrect output destination: '.$dest);
    	}
    	return '';
    }

    /**
     * Some internal checks before starting.
     */
    protected function _dochecks() : void
    {
    	// Check mbstring overloading
    	if(ini_get('mbstring.func_overload') & 2)
    		$this->Error('mbstring overloading must be disabled');
    }

    /**
     * Some internal checks before output.
     */
    protected function _checkoutput() : void
    {
    	if(PHP_SAPI!='cli')
    	{
    	    $file = '';
    	    $line = 0;
    		if(headers_sent($file,$line))
    			$this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
    	}
    	if(ob_get_length())
    	{
    		// The output buffer is not empty
    		if(preg_match('/^(\xEF\xBB\xBF)?\s*$/',ob_get_contents()))
    		{
    			// It contains only a UTF-8 BOM and/or whitespace, let's clean it
    			ob_clean();
    		}
    		else
    			$this->Error("Some data has already been output, can't send PDF file");
    	}
    }
    
    /**
     * Get dimensions of selected pagesize.
     * @param string|array $size
     * @return array
     */
    protected function _getpagesize($size) : array
    {
    	if(is_string($size))
    	{
    		$size = strtolower($size);
    		if(!isset($this->StdPageSizes[$size]))
    			$this->Error('Unknown page size: '.$size);
    		$a = $this->StdPageSizes[$size];
    		return array($a[0]/$this->k, $a[1]/$this->k);
    	}
    	else
    	{
    		if($size[0]>$size[1])
    			return array($size[1], $size[0]);
    		else
    			return $size;
    	}
    }
    
    /**
     * Start new page.
     * @param string $orientation
     * @param string|array $size
     * @param int $rotation
     */
    protected function _beginpage(string $orientation, $size, int $rotation) : void
    {
    	$this->page++;
    	$this->pages[$this->page] = '';
    	$this->state = 2;
    	$this->x = $this->lMargin;
    	$this->y = $this->tMargin;
    	$this->FontFamily = '';
    	// Check page size and orientation
    	if($orientation=='')
    		$orientation = $this->DefOrientation;
    	else
    		$orientation = strtoupper($orientation[0]);
    	if($size=='')
    		$size = $this->DefPageSize;
    	else
    		$size = $this->_getpagesize($size);
    	if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
    	{
    		// New size or orientation
    		if($orientation=='P')
    		{
    			$this->w = $size[0];
    			$this->h = $size[1];
    		}
    		else
    		{
    			$this->w = $size[1];
    			$this->h = $size[0];
    		}
    		$this->wPt = $this->w*$this->k;
    		$this->hPt = $this->h*$this->k;
    		$this->PageBreakTrigger = $this->h-$this->bMargin;
    		$this->CurOrientation = $orientation;
    		$this->CurPageSize = $size;
    	}
    	if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
    		$this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt);
    	if($rotation!=0)
    	{
    		if($rotation%90!=0)
    			$this->Error('Incorrect rotation value: '.$rotation);
    		$this->CurRotation = $rotation;
    		$this->PageInfo[$this->page]['rotation'] = $rotation;
    	}
    }
    
    /**
     * End of current page.
     */
    protected function _endpage() : void
    {
    	$this->state = 1;
    }
    
    /**
     * Load a font definition file from the font directory.
     * @param string $font
     * @return array
     */
    protected function _loadfont(string $font) : array
    {
    	// Load a font definition file from the font directory
        if(strpos($font,'/')!==false || strpos($font,"\\")!==false) {
    		$this->Error('Incorrect font definition file name: '.$font);
        }
        // following vars must be initialized in the font definition file beeing included
    	$name = null; 
    	$enc = null;
    	$subsetted = null;
    	include($this->fontpath.$font);
    	
    	// phpstan can't see the code dynamicly included before so assuming $name, $enc, $subsetted always set to null!
    	if(!isset($name)) {            /* @phpstan-ignore-line */
    		$this->Error('Could not include font definition file');
    	}
    	if(isset($enc)) {              /* @phpstan-ignore-line */
    		$enc = strtolower($enc);
    	}
    	if(!isset($subsetted)) {       /* @phpstan-ignore-line */
    		$subsetted = false;
    	}
    	return get_defined_vars();
    }
    
    /**
     * Check if string only contains ascii chars (0...127).
     * @param string $s
     * @return bool
     */
    protected function _isascii(string $s) : bool
    {
    	// Test if string is ASCII
    	$nb = strlen($s);
    	for($i=0;$i<$nb;$i++)
    	{
    		if(ord($s[$i])>127)
    			return false;
    	}
    	return true;
    }
    
    /**
     * @param string $param
     * @param string $value
     * @param bool $isUTF8
     * @return string
     */
    protected function _httpencode(string $param, string $value, bool $isUTF8) : string
    {
    	// Encode HTTP header field parameter
    	if($this->_isascii($value))
    		return $param.'="'.$value.'"';
    	if(!$isUTF8)
    		$value = utf8_encode($value);
    	if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false)
    		return $param.'="'.rawurlencode($value).'"';
    	else
    		return $param."*=UTF-8''".rawurlencode($value);
    }
    
    /**
     * Convert UTF8 to UTF16.
     * @param string $s
     * @return string
     */
    protected function _UTF8toUTF16(string $s) : string
    {
    	// Convert UTF-8 to UTF-16BE with BOM
    	$res = "\xFE\xFF";
    	$nb = strlen($s);
    	$i = 0;
    	while($i<$nb)
    	{
    		$c1 = ord($s[$i++]);
    		if($c1>=224)
    		{
    			// 3-byte character
    			$c2 = ord($s[$i++]);
    			$c3 = ord($s[$i++]);
    			$res .= chr((($c1 & 0x0F)<<4) + (($c2 & 0x3C)>>2));
    			$res .= chr((($c2 & 0x03)<<6) + ($c3 & 0x3F));
    		}
    		elseif($c1>=192)
    		{
    			// 2-byte character
    			$c2 = ord($s[$i++]);
    			$res .= chr(($c1 & 0x1C)>>2);
    			$res .= chr((($c1 & 0x03)<<6) + ($c2 & 0x3F));
    		}
    		else
    		{
    			// Single-byte character
    			$res .= "\0".chr($c1);
    		}
    	}
    	return $res;
    }
    
    /**
     * Escape special characters.
     * @param string $s
     * @return string
     */
    protected function _escape(string $s) : string
    {
    	// Escape special characters
    	if(strpos($s,'(')!==false || strpos($s,')')!==false || strpos($s,'\\')!==false || strpos($s,"\r")!==false)
    		return str_replace(array('\\','(',')',"\r"), array('\\\\','\\(','\\)','\\r'), $s);
    	else
    		return $s;
    }
    
    /**
     * Format a text string.
     * @param string $s
     * @return string
     */
    protected function _textstring(string $s) : string
    {
    	// Format a text string
    	if(!$this->_isascii($s))
    		$s = $this->_UTF8toUTF16($s);
    	return '('.$this->_escape($s).')';
    }
    
    /**
     * Underline text with 'simple' line.
     * @param float $x
     * @param float $y
     * @param string $txt
     * @return string
     */
    protected function _dounderline(float $x, float $y, string $txt) : string
    {
    	// Underline text
    	$up = $this->CurrentFont['up'];
    	$ut = $this->CurrentFont['ut'];
    	$w = $this->getStringWidth($txt)+$this->ws*substr_count($txt,' ');
    	return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y-$up/1000*$this->FontSize))*$this->k,$w*$this->k,-$ut/1000*$this->FontSizePt);
    }
    
    /**
     * Extract info from a JPEG file.
     * @param string $file
     * @return array
     */
    protected function _parsejpg(string $file) : array
    {
    	// Extract info from a JPEG file
    	$a = getimagesize($file);
    	if(!$a)
    		$this->Error('Missing or incorrect image file: '.$file);
    	if($a[2]!=2)
    		$this->Error('Not a JPEG file: '.$file);
    	if(!isset($a['channels']) || $a['channels']==3)
    		$colspace = 'DeviceRGB';
    	elseif($a['channels']==4)
    		$colspace = 'DeviceCMYK';
    	else
    		$colspace = 'DeviceGray';
    	$bpc = isset($a['bits']) ? $a['bits'] : 8;
    	$data = file_get_contents($file);
    	return array('w'=>$a[0], 'h'=>$a[1], 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'DCTDecode', 'data'=>$data);
    }
    
    /**
     * Extract info from a PNG file.
     * @param string $file
     * @return array
     */
    protected function _parsepng(string $file) : array
    {
    	// Extract info from a PNG file
    	$f = fopen($file, 'rb');
    	if ($f === false) {
    		$this->Error('Can\'t open image file: ' . $file);
    	}
    	$info = $this->_parsepngstream($f, $file);
    	fclose($f);
    	return $info;
    }
    
    /**
     * Extract info from a PNG stream
     * @param resource $f
     * @param string $file
     * @return array
     */
    protected function _parsepngstream($f, string $file) : array
    {
    	// Check signature
    	if($this->_readstream($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
    		$this->Error('Not a PNG file: '.$file);
    
    	// Read header chunk
    	$this->_readstream($f,4);
    	if($this->_readstream($f,4)!='IHDR')
    		$this->Error('Incorrect PNG file: '.$file);
    	$w = $this->_readint($f);
    	$h = $this->_readint($f);
    	$bpc = ord($this->_readstream($f,1));
    	if($bpc>8)
    		$this->Error('16-bit depth not supported: '.$file);
    	$ct = ord($this->_readstream($f,1));
    	$colspace = '';
    	if($ct==0 || $ct==4)
    		$colspace = 'DeviceGray';
    	elseif($ct==2 || $ct==6)
    		$colspace = 'DeviceRGB';
    	elseif($ct==3)
    		$colspace = 'Indexed';
    	else
    		$this->Error('Unknown color type: '.$file);
    	if(ord($this->_readstream($f,1))!=0)
    		$this->Error('Unknown compression method: '.$file);
    	if(ord($this->_readstream($f,1))!=0)
    		$this->Error('Unknown filter method: '.$file);
    	if(ord($this->_readstream($f,1))!=0)
    		$this->Error('Interlacing not supported: '.$file);
    	$this->_readstream($f,4);
    	$dp = '/Predictor 15 /Colors '.($colspace=='DeviceRGB' ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w;
    
    	// Scan chunks looking for palette, transparency and image data
    	$pal = '';
    	$trns = '';
    	$data = '';
    	do
    	{
    		$n = $this->_readint($f);
    		$type = $this->_readstream($f,4);
    		if($type=='PLTE')
    		{
    			// Read palette
    			$pal = $this->_readstream($f,$n);
    			$this->_readstream($f,4);
    		}
    		elseif($type=='tRNS')
    		{
    			// Read transparency info
    			$t = $this->_readstream($f,$n);
    			if($ct==0)
    				$trns = array(ord(substr($t,1,1)));
    			elseif($ct==2)
    				$trns = array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1)));
    			else
    			{
    				$pos = strpos($t,chr(0));
    				if($pos!==false)
    					$trns = array($pos);
    			}
    			$this->_readstream($f,4);
    		}
    		elseif($type=='IDAT')
    		{
    			// Read image data block
    			$data .= $this->_readstream($f,$n);
    			$this->_readstream($f,4);
    		}
    		elseif($type=='IEND')
    			break;
    		else
    			$this->_readstream($f,$n+4);
    	}
    	while($n);
    
    	if($colspace=='Indexed' && empty($pal))
    		$this->Error('Missing palette in '.$file);
    	$info = array('w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'dp'=>$dp, 'pal'=>$pal, 'trns'=>$trns);
    	if($ct>=4)
    	{
    		// Extract alpha channel
    		if(!function_exists('gzuncompress'))
    			$this->Error('Zlib not available, can\'t handle alpha channel: '.$file);
    		$data = gzuncompress($data);
    		$color = '';
    		$alpha = '';
    		if($ct==4)
    		{
    			// Gray image
    			$len = 2*$w;
    			for($i=0;$i<$h;$i++)
    			{
    				$pos = (1+$len)*$i;
    				$color .= $data[$pos];
    				$alpha .= $data[$pos];
    				$line = substr($data,$pos+1,$len);
    				$color .= preg_replace('/(.)./s','$1',$line);
    				$alpha .= preg_replace('/.(.)/s','$1',$line);
    			}
    		}
    		else
    		{
    			// RGB image
    			$len = 4*$w;
    			for($i=0;$i<$h;$i++)
    			{
    				$pos = (1+$len)*$i;
    				$color .= $data[$pos];
    				$alpha .= $data[$pos];
    				$line = substr($data,$pos+1,$len);
    				$color .= preg_replace('/(.{3})./s','$1',$line);
    				$alpha .= preg_replace('/.{3}(.)/s','$1',$line);
    			}
    		}
    		unset($data);
    		$data = gzcompress($color);
    		$info['smask'] = gzcompress($alpha);
    		$this->WithAlpha = true;
    		if($this->PDFVersion<'1.4')
    			$this->PDFVersion = '1.4';
    	}
    	$info['data'] = $data;
    	return $info;
    }
    
    /**
     * Read n bytes from stream.
     * @param resource $f
     * @param int $n
     * @return string
     */
    protected function _readstream($f, int $n) : string
    {
    	// Read n bytes from stream
    	$res = '';
    	while($n>0 && !feof($f))
    	{
    		$s = fread($f,$n);
    		if($s===false)
    			$this->Error('Error while reading stream');
    		$n -= strlen($s);
    		$res .= $s;
    	}
    	if($n>0)
    		$this->Error('Unexpected end of stream');
    	return $res;
    }
    
    /**
     * Read a 4-byte integer from stream.
     * @param resource $f
     * @return int
     */
    protected function _readint($f) : int
    {
    	// Read a 4-byte integer from stream
    	$a = unpack('Ni',$this->_readstream($f,4));
    	return $a['i'];
    }
    
    /**
     * Extract info from a GIF file (via PNG conversion).
     * @param string $file
     * @return array
     */
    protected function _parsegif(string $file) : array
    {
    	// Extract info from a GIF file (via PNG conversion)
        if (!function_exists('imagepng')) {
    		$this->Error('GD extension is required for GIF support');
        }
        if (!function_exists('imagecreatefromgif')) {
    		$this->Error('GD has no GIF read support');
        }
    	$im = imagecreatefromgif($file);
    	if ($im === false) {
    		$this->Error('Missing or incorrect image file: ' . $file);
    	}
    	imageinterlace($im, 0);
    	ob_start();
    	imagepng($im);
    	$data = ob_get_clean();
    	imagedestroy($im);
    	$f = fopen('php://temp', 'rb+');
    	if ($f === false) {
    		$this->Error('Unable to create memory stream');
    	}
    	fwrite($f, $data);
    	rewind($f);
    	$info = $this->_parsepngstream($f, $file);
    	fclose($f);
    	return $info;
    }
    
    /**
     * Add a line to the document.
     * @param string $s
     */
    protected function _out(string $s) : void
    {
    	// Add a line to the document
    	if($this->state==2)
    		$this->pages[$this->page] .= $s."\n";
    	elseif($this->state==1)
    		$this->_put($s);
    	elseif($this->state==0)
    		$this->Error('No page has been added yet');
    	elseif($this->state==3)
    		$this->Error('The document is closed');
    }
    
    /**
     * Add a command to the document.
     * @param string $s
     */
    protected function _put(string $s) : void
    {
    	$this->buffer .= $s."\n";
    }
    
    /**
     * Get current length of the output buffer.
     * @return int
     */
    protected function _getoffset() : int
    {
    	return strlen($this->buffer);
    }
    
    /**
     * Begin a new object.
     * @param int $n
     */
    protected function _newobj(?int $n=null) : void
    {
    	// Begin a new object
    	if($n===null)
    		$n = ++$this->n;
    	$this->offsets[$n] = $this->_getoffset();
    	$this->_put($n.' 0 obj');
    }
    
    /**
     * Add data from stream to the document.
     * @param string $data
     */
    protected function _putstream(string $data) : void
    {
    	$this->_put('stream');
    	$this->_put($data);
    	$this->_put('endstream');
    }
    
    /**
     * Add Stream-object to the document. 
     * @param string $data
     */
    protected function _putstreamobject(string $data) : void
    {
    	if($this->compress)
    	{
    		$entries = '/Filter /FlateDecode ';
    		$data = gzcompress($data);
    	}
    	else
    		$entries = '';
    	$entries .= '/Length '.strlen($data);
    	$this->_newobj();
    	$this->_put('<<'.$entries.'>>');
    	$this->_putstream($data);
    	$this->_put('endobj');
    }
    
    /**
     * Add Pageinfos to the document.
     * @param int $n
     */
    protected function _putpage(int $n) : void
    {
    	$this->_newobj();
    	$this->_put('<</Type /Page');
    	$this->_put('/Parent 1 0 R');
    	if(isset($this->PageInfo[$n]['size']))
    		$this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageInfo[$n]['size'][0],$this->PageInfo[$n]['size'][1]));
    	if(isset($this->PageInfo[$n]['rotation']))
    		$this->_put('/Rotate '.$this->PageInfo[$n]['rotation']);
    	$this->_put('/Resources 2 0 R');
    	if(isset($this->PageLinks[$n]))
    	{
    		// Links
    		$annots = '/Annots [';
    		foreach($this->PageLinks[$n] as $pl)
    		{
    			$rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
    			$annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
    			if(is_string($pl[4]))
    				$annots .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
    			else
    			{
    				$l = $this->links[$pl[4]];
    				if(isset($this->PageInfo[$l[0]]['size']))
    					$h = $this->PageInfo[$l[0]]['size'][1];
    				else
    					$h = ($this->DefOrientation=='P') ? $this->DefPageSize[1]*$this->k : $this->DefPageSize[0]*$this->k;
    				$annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',$this->PageInfo[$l[0]]['n'],$h-$l[1]*$this->k);
    			}
    		}
    		$this->_put($annots.']');
    	}
    	if($this->WithAlpha)
    		$this->_put('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
    	$this->_put('/Contents '.($this->n+1).' 0 R>>');
    	$this->_put('endobj');
    	// Page content
    	if(!empty($this->AliasNbPages))
    		$this->pages[$n] = str_replace($this->AliasNbPages, strval($this->page), $this->pages[$n]);
    	$this->_putstreamobject($this->pages[$n]);
    }
    
    /**
     * Add all pages to the document.
     */
    protected function _putpages() : void
    {
    	$nb = $this->page;
    	for($n=1;$n<=$nb;$n++)
    		$this->PageInfo[$n]['n'] = $this->n+1+2*($n-1);
    	for($n=1;$n<=$nb;$n++)
    		$this->_putpage($n);
    	// Pages root
    	$this->_newobj(1);
    	$this->_put('<</Type /Pages');
    	$kids = '/Kids [';
    	for($n=1;$n<=$nb;$n++)
    		$kids .= $this->PageInfo[$n]['n'].' 0 R ';
    	$this->_put($kids.']');
    	$this->_put('/Count '.$nb);
    	if($this->DefOrientation=='P')
    	{
    		$w = $this->DefPageSize[0];
    		$h = $this->DefPageSize[1];
    	}
    	else
    	{
    		$w = $this->DefPageSize[1];
    		$h = $this->DefPageSize[0];
    	}
    	$this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$w*$this->k,$h*$this->k));
    	$this->_put('>>');
    	$this->_put('endobj');
    }
    
    /**
     * Add fonts to the document.
     */
    protected function _putfonts() : void
    {
    	foreach ($this->FontFiles as $file => $info) {
    		// Font file embedding
    		$this->_newobj();
    		$this->FontFiles[$file]['n'] = $this->n;
    		$font = file_get_contents($this->fontpath . $file, true);
    		if (!$font) {
    			$this->Error('Font file not found: ' . $file);
    		}
    		$compressed = (substr($file, -2) == '.z');
    		if (!$compressed && isset($info['length2'])) {
    			$font = substr($font, 6, $info['length1']) . substr($font, 6 + $info['length1'] + 6, $info['length2']);
    		}
    		$this->_put('<</Length ' . strlen($font));
    		if ($compressed) {
    			$this->_put('/Filter /FlateDecode');
    		}
    		$this->_put('/Length1 ' . $info['length1']);
    		if (isset($info['length2'])) {
    			$this->_put('/Length2 '.$info['length2'].' /Length3 0');
    		}
    		$this->_put('>>');
    		$this->_putstream($font);
    		$this->_put('endobj');
    	}
    	foreach ($this->fonts as $k => $font) {
    		// Encoding
    		if (isset($font['diff'])) {
    			if (!isset($this->encodings[$font['enc']])) {
    				$this->_newobj();
    				$this->_put('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences [' . $font['diff'] . ']>>');
    				$this->_put('endobj');
    				$this->encodings[$font['enc']] = $this->n;
    			}
    		}
    		// ToUnicode CMap
    		$cmapkey = '';
    		if (isset($font['uv'])) {
    		    if (isset($font['enc'])) {
    				$cmapkey = $font['enc'];
    		    } else {
    				$cmapkey = $font['name'];
    		    }
    			if (!isset($this->cmaps[$cmapkey])) {
    				$cmap = $this->_tounicodecmap($font['uv']);
    				$this->_putstreamobject($cmap);
    				$this->cmaps[$cmapkey] = $this->n;
    			}
    		}
    		// Font object
    		$this->fonts[$k]['n'] = $this->n + 1;
    		$type = $font['type'];
    		$name = $font['name'];
    		if ($font['subsetted']) {
    			$name = 'AAAAAA+' . $name;
    		}
    		if ($type=='Core') {
    			// Core font
    			$this->_newobj();
    			$this->_put('<</Type /Font');
    			$this->_put('/BaseFont /' . $name);
    			$this->_put('/Subtype /Type1');
    			if ($name != 'Symbol' && $name != 'ZapfDingbats') {
    				$this->_put('/Encoding /WinAnsiEncoding');
    			}
    			if (isset($font['uv'])) {
    				$this->_put('/ToUnicode ' . $this->cmaps[$cmapkey] . ' 0 R');
    			}
    			$this->_put('>>');
    			$this->_put('endobj');
    		} elseif ($type == 'Type1' || $type == 'TrueType') {
    			// Additional Type1 or TrueType/OpenType font
    			$this->_newobj();
    			$this->_put('<</Type /Font');
    			$this->_put('/BaseFont /' . $name);
    			$this->_put('/Subtype /' . $type);
    			$this->_put('/FirstChar 32 /LastChar 255');
    			$this->_put('/Widths ' . ($this->n + 1) . ' 0 R');
    			$this->_put('/FontDescriptor ' . ($this->n + 2) . ' 0 R');
    			if (isset($font['diff'])) {
    				$this->_put('/Encoding ' . $this->encodings[$font['enc']] . ' 0 R');
    			} else {
    				$this->_put('/Encoding /WinAnsiEncoding');
    			}
    			if (isset($font['uv'])) {
    				$this->_put('/ToUnicode ' . $this->cmaps[$cmapkey] . ' 0 R');
    			}
    			$this->_put('>>');
    			$this->_put('endobj');
    			// Widths
    			$this->_newobj();
    			$cw = &$font['cw'];
    			$s = '[';
    			for ($i = 32; $i <= 255; $i++) {
    				$s .= $cw[chr($i)] . ' ';
    			}
    			$this->_put($s . ']');
    			$this->_put('endobj');
    			// Descriptor
    			$this->_newobj();
    			$s = '<</Type /FontDescriptor /FontName /' . $name;
    			foreach ($font['desc'] as $k2 => $v) {
    				$s .= ' /' . $k2 . ' ' . $v;
    			}
    			if (!empty($font['file'])) {
    				$s .= ' /FontFile' . ($type == 'Type1' ? '' : '2') . ' ' . $this->FontFiles[$font['file']]['n'] . ' 0 R';
    			}
    			$this->_put($s . '>>');
    			$this->_put('endobj');
    		} else {
    			// Allow for additional types
    			$mtd = '_put' . strtolower($type);
    			if (!method_exists($this, $mtd)) {
    				$this->Error('Unsupported font type: ' . $type);
    			}
    			$this->$mtd($font);
    		}
    	}
    }
    
    /**
     * @param array $uv
     * @return string
     */
    protected function _tounicodecmap(array $uv) : string
    {
    	$ranges = '';
    	$nbr = 0;
    	$chars = '';
    	$nbc = 0;
    	foreach($uv as $c=>$v)
    	{
    		if(is_array($v))
    		{
    			$ranges .= sprintf("<%02X> <%02X> <%04X>\n",$c,$c+$v[1]-1,$v[0]);
    			$nbr++;
    		}
    		else
    		{
    			$chars .= sprintf("<%02X> <%04X>\n",$c,$v);
    			$nbc++;
    		}
    	}
    	$s = "/CIDInit /ProcSet findresource begin\n";
    	$s .= "12 dict begin\n";
    	$s .= "begincmap\n";
    	$s .= "/CIDSystemInfo\n";
    	$s .= "<</Registry (Adobe)\n";
    	$s .= "/Ordering (UCS)\n";
    	$s .= "/Supplement 0\n";
    	$s .= ">> def\n";
    	$s .= "/CMapName /Adobe-Identity-UCS def\n";
    	$s .= "/CMapType 2 def\n";
    	$s .= "1 begincodespacerange\n";
    	$s .= "<00> <FF>\n";
    	$s .= "endcodespacerange\n";
    	if($nbr>0)
    	{
    		$s .= "$nbr beginbfrange\n";
    		$s .= $ranges;
    		$s .= "endbfrange\n";
    	}
    	if($nbc>0)
    	{
    		$s .= "$nbc beginbfchar\n";
    		$s .= $chars;
    		$s .= "endbfchar\n";
    	}
    	$s .= "endcmap\n";
    	$s .= "CMapName currentdict /CMap defineresource pop\n";
    	$s .= "end\n";
    	$s .= "end";
    	return $s;
    }
    
    /**
     * Add all containing images to the document.
     */
    protected function _putimages() : void
    {
    	foreach(array_keys($this->images) as $file)
    	{
    		$this->_putimage($this->images[$file]);
    		unset($this->images[$file]['data']);
    		unset($this->images[$file]['smask']);
    	}
    }
    
    /**
     * Add image to the document.
     * @param array $info
     */
    protected function _putimage(array &$info) : void
    {
    	$this->_newobj();
    	$info['n'] = $this->n;
    	$this->_put('<</Type /XObject');
    	$this->_put('/Subtype /Image');
    	$this->_put('/Width ' . $info['w']);
    	$this->_put('/Height ' . $info['h']);
    	if ($info['cs'] == 'Indexed') {
    		$this->_put('/ColorSpace [/Indexed /DeviceRGB ' . (strlen($info['pal']) / 3 - 1) . ' ' . ($this->n + 1) . ' 0 R]');
    	} else {
    		$this->_put('/ColorSpace /' . $info['cs']);
    		if($info['cs']=='DeviceCMYK') {
    			$this->_put('/Decode [1 0 1 0 1 0 1 0]');
    		}
    	}
    	$this->_put('/BitsPerComponent ' . $info['bpc']);
    	if (isset($info['f'])) {
    		$this->_put('/Filter /' . $info['f']);
    	}
    	if (isset($info['dp'])) {
    		$this->_put('/DecodeParms <<' . $info['dp'] . '>>');
    	}
    	if (isset($info['trns']) && is_array($info['trns']))	{
    		$trns = '';
    		$cnt = count($info['trns']);
    		for ($i = 0; $i < $cnt; $i++) {
    			$trns .= $info['trns'][$i] . ' ' . $info['trns'][$i] . ' ';
    		}
    		$this->_put('/Mask [' . $trns . ']');
    	}
    	if (isset($info['smask'])) {
    		$this->_put('/SMask ' . ($this->n+1) . ' 0 R');
    	}
    	$this->_put('/Length ' . strlen($info['data']) . '>>');
    	$this->_putstream($info['data']);
    	$this->_put('endobj');
    	// Soft mask
    	if (isset($info['smask'])) {
    		$dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns ' . $info['w'];
    		$smask = array(
    		    'w'=>$info['w'], 
    		    'h'=>$info['h'], 
    		    'cs'=>'DeviceGray', 
    		    'bpc'=>8, 
    		    'f'=>$info['f'], 
    		    'dp'=>$dp, 
    		    'data'=>$info['smask']
    		);
    		$this->_putimage($smask);
    	}
    	// Palette
    	if ($info['cs'] == 'Indexed') {
    		$this->_putstreamobject($info['pal']);
    	}
    }
    
    /**
     * 
     */
    protected function _putxobjectdict() : void
    {
        foreach ($this->images as $image) {
    		$this->_put('/I' . $image['i'] . ' ' . $image['n'] . ' 0 R');
        }
    }
    
    /**
     * 
     */
    protected function _putresourcedict() : void
    {
    	$this->_put('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
    	$this->_put('/Font <<');
    	foreach ($this->fonts as $font) {
    		$this->_put('/F' . $font['i'] . ' ' . $font['n'] . ' 0 R');
    	}
    	$this->_put('>>');
    	$this->_put('/XObject <<');
    	$this->_putxobjectdict();
    	$this->_put('>>');
    }
    
    /**
     * 
     */
    protected function _putresources() : void
    {
    	$this->_putfonts();
    	$this->_putimages();
    	// Resource dictionary
    	$this->_newobj(2);
    	$this->_put('<<');
    	$this->_putresourcedict();
    	$this->_put('>>');
    	$this->_put('endobj');
    }
    
    /**
     * 
     */
    protected function _putinfo() : void
    {
    	$this->metadata['Producer'] = 'FPDF '.FPDF_VERSION;
    	$this->metadata['CreationDate'] = 'D:' . @date('YmdHis');
    	foreach ($this->metadata as $key=>$value) {
    		$this->_put('/' . $key . ' ' . $this->_textstring($value));
    	}
    }
    
    /**
     * 
     */
    protected function _putcatalog() : void
    {
    	$n = $this->PageInfo[1]['n'];
    	$this->_put('/Type /Catalog');
    	$this->_put('/Pages 1 0 R');
    	if($this->ZoomMode=='fullpage') {
    		$this->_put('/OpenAction [' . $n . ' 0 R /Fit]');
    	} elseif ($this->ZoomMode=='fullwidth') {
    		$this->_put('/OpenAction [' . $n . ' 0 R /FitH null]');
    	} elseif($this->ZoomMode=='real') {
    		$this->_put('/OpenAction [' . $n . ' 0 R /XYZ null null 1]');
    	} elseif(!is_string($this->ZoomMode)) {
    		$this->_put('/OpenAction [' . $n . ' 0 R /XYZ null null ' . sprintf('%.2F', $this->ZoomMode / 100) . ']');
    	}
    	if($this->LayoutMode=='single') {
    		$this->_put('/PageLayout /SinglePage');
    	} elseif($this->LayoutMode=='continuous') {
    		$this->_put('/PageLayout /OneColumn');
    	} elseif($this->LayoutMode=='two') {
    		$this->_put('/PageLayout /TwoColumnLeft');
    	}
    }
    
    /**
     * 
     */
    protected function _putheader() : void
    {
    	$this->_put('%PDF-' . $this->PDFVersion);
    }
    
    /**
     * 
     */
    protected function _puttrailer() : void
    {
    	$this->_put('/Size ' . ($this->n + 1));
    	$this->_put('/Root ' . $this->n . ' 0 R');
    	$this->_put('/Info ' . ($this->n - 1) . ' 0 R');
    }
    
    /**
     * 
     */
    protected function _enddoc() : void
    {
    	$this->_putheader();
    	$this->_putpages();
    	$this->_putresources();
    	// Info
    	$this->_newobj();
    	$this->_put('<<');
    	$this->_putinfo();
    	$this->_put('>>');
    	$this->_put('endobj');
    	// Catalog
    	$this->_newobj();
    	$this->_put('<<');
    	$this->_putcatalog();
    	$this->_put('>>');
    	$this->_put('endobj');
    	// Cross-ref
    	$offset = $this->_getoffset();
    	$this->_put('xref');
    	$this->_put('0 ' . ($this->n + 1));
    	$this->_put('0000000000 65535 f ');
    	for ($i = 1; $i <= $this->n; $i++) {
    		$this->_put(sprintf('%010d 00000 n ', $this->offsets[$i]));
    	}
    	// Trailer
    	$this->_put('trailer');
    	$this->_put('<<');
    	$this->_puttrailer();
    	$this->_put('>>');
    	$this->_put('startxref');
    	$this->_put(strval($offset));
    	$this->_put('%%EOF');
    	$this->state = 3;
    }
}
