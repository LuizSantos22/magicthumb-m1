<?php

if (!defined('MagicThumbModuleCoreClassLoaded')) {

define('MagicThumbModuleCoreClassLoaded', true);

require_once(dirname(__FILE__) . '/magictoolbox.params.class.php');

/**
 * MagicThumbModuleCoreClass
 *
 */
class MagicThumbModuleCoreClass
{

    /**
     * MagicToolboxParamsClass class
     *
     * @var   MagicToolboxParamsClass
     *
     */
    public $params;

    /**
     * Tool type
     *
     * @var   string
     *
     */
    public $type = 'standard';

    /**
     * Id
     *
     * @var   string
     *
     */
    public $id = '';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->params = new MagicToolboxParamsClass();
        $this->params->setScope('magicthumb');
        $this->params->setMapping(array(
            'loop' => array('Yes' => 'true', 'No' => 'false'),
            'keyboard' => array('Yes' => 'true', 'No' => 'false'),
            'rightClick' => array('Yes' => 'true', 'No' => 'false'),
            'lazyLoad' => array('Yes' => 'true', 'No' => 'false'),
            'autostart' => array('Yes' => 'true', 'No' => 'false')
        ));
        $this->loadDefaults();
    }

    /**
     * Method to get headers string
     *
     * @param string $jsPath  Path to JS file
     * @param string $cssPath Path to CSS file
     *
     * @return string
     */
    public function getHeadersTemplate($jsPath = '', $cssPath = null)
    {
        //to prevent multiple displaying of headers
        if (!defined('MAGICTHUMB_MODULE_HEADERS')) {
            define('MAGICTHUMB_MODULE_HEADERS', true);
        } else {
            return '';
        }
        if ($cssPath == null) {
            $cssPath = $jsPath;
        }
        $headers = array();
        $headers[] = '<!-- Magic Thumb Magento module version v4.16.0 [v1.6.97:v3.0.20] -->';
        $headers[] = '<script type="text/javascript">window["mgctlbx$Pltm"] = "Magento";</script>';
        $headers[] = '<link type="text/css" href="' . $cssPath . '/magicthumb.css" rel="stylesheet" media="screen" />';
        $headers[] = '<link type="text/css" href="' . $cssPath . '/magicthumb.module.css" rel="stylesheet" media="screen" />';
        $headers[] = '<script type="text/javascript" src="' . $jsPath . '/magicthumb.js"></script>';
        $headers[] = $this->getOptionsTemplate();
        return "\r\n" . implode("\r\n", $headers) . "\r\n";
    }

    /**
     * Method to get options string
     *
     * @return string
     */
    public function getOptionsTemplate()
    {
        $addition = "\n\t\t'captionSource':'title',";

        if ($this->params->checkValue('show-caption', 'No')) {
            $this->params->setValue('captionPosition', 'off');
        }

        return  "<script type=\"text/javascript\">\n\tvar mgtOptions = {{$addition}\n\t\t" . $this->params->serialize(true, ",\n\t\t") . "\n\t}\n</script>" .
                "<script type=\"text/javascript\">\n\tvar mgtMobileOptions = {" .
                "\n\t\t'slideMobileEffect':'" . str_replace('\'', '\\\'', $this->params->getValue('slideMobileEffect')) . "'," .
                "\n\t\t'textClickHint':'" . str_replace('\'', '\\\'', $this->params->getValue('textClickHintForMobile')) . "'" .
                "\n\t}\n</script>";
    }

    /**
     * Method to get main image HTML
     *
     * @param array $params Params
     *
     * @return string
     */
    public function getMainTemplate($params)
    {
        $img = '';
        $thumb = '';
        $thumb2x = '';
        $id = '';
        $alt = '';
        $title = '';
        $description = '';
        $width = '';
        $height = '';
        $link = '';
        $group = '';

        extract($params);

        if (empty($img)) {
            return false;
        }
        if (empty($thumb)) {
            $thumb = $img;
        }
        if (empty($id)) {
            $id = hash('md5', $img, false);
        }

        $this->id = $id;

        if (empty($alt)) {
            if (empty($title)) {
                $title = '';
                $alt = '';
            } else {
                $alt = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
            }
        } else {
            if (empty($title)) {
                $title = '';
            }
            $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
        }
        if (empty($description)) {
            $description = '';
        }

        if ($this->params->checkValue('show-caption', 'No')) {
            $title = '';
        }

        if (empty($width)) {
            $width = '';
        } else {
            $width = " width=\"{$width}\"";
        }
        if (empty($height)) {
            $height = '';
        } else {
            $height = " height=\"{$height}\"";
        }

        $dataOptions = $this->params->serialize();

        if (!empty($link)) {
            $dataOptions .= 'link:' . $link . ';';
        }

        if (!empty($group)) {
            $dataOptions .= 'group:' . $group . ';';
        }

        if ('' != trim($dataOptions)) {
            $dataOptions = 'data-options="' . $dataOptions . '"';
        }

        if (!empty($thumb2x)) {
            //$thumb2x = ' srcset="' . $thumb2x . ' 2x"';
            //$thumb2x = ' srcset="' . $thumb . ' 1x, ' . $thumb2x . ' 2x"';
            $thumb2x = ' srcset="' . str_replace(' ', '%20', $thumb) . ' 1x, ' . str_replace(' ', '%20', $thumb2x) . ' 2x"';
        }

        return "<a title=\"{$title}\" class=\"MagicThumb\" id=\"MagicThumbImage{$id}\" href=\"{$img}\" {$dataOptions}><img itemprop=\"image\"{$width}{$height} {$thumb2x} src=\"{$thumb}\" alt=\"{$alt}\" /></a>";
    }

    /**
     * Method to get selectors HTML
     *
     * @param array $params Params
     *
     * @return string
     */
    public function getSelectorTemplate($params)
    {
        $img = '';
        $medium = '';
        $thumb = '';
        $thumb2x = '';
        $id = '';
        $alt = '';
        $title = '';
        $width = '';
        $height = '';

        extract($params);

        if (empty($img)) {
            return false;
        }
        if (empty($medium)) {
            $medium = $img;
        }
        if (empty($thumb)) {
            $thumb = $img;
        }
        if (empty($id)) {
            $id = $this->id;
        }


        if (empty($title)) {
            $title = '';
        } else {
            $title = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
        }
        if (empty($alt)) {
            $alt = $title;
        } else {
            $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
        }

        if ($this->params->checkValue('show-caption', 'Yes') && !empty($title)) {
            $title = trim(preg_replace('#\s+#is', ' ', $title));
            $title = preg_replace('#<(/?)a([^>]*+)>#is', '[$1a$2]', $title);
            $title = " title=\"{$title}\"";
        } else {
            $title = '';
        }

        if (empty($width)) {
            $width = '';
        } else {
            $width = " width=\"{$width}\"";
        }
        if (empty($height)) {
            $height = '';
        } else {
            $height = " height=\"{$height}\"";
        }

        if (!empty($thumb2x)) {
            $thumb2x = ' srcset="' . $thumb2x . ' 2x"';
        }

        $template = "<a{$title} href=\"{$img}\" data-thumb-id=\"MagicThumbImage{$id}\" data-image=\"$medium\"><img{$width}{$height} src=\"{$thumb}\" {$thumb2x} alt=\"{$alt}\" /></a>";

        return $template;
    }

    /**
     * Method to load defaults options
     *
     * @return void
     */
    public function loadDefaults()
    {
        $params = array(
            "enable-effect"=>array("id"=>"enable-effect","group"=>"General","order"=>"10","default"=>"Yes","label"=>"Enable Magic Thumb™","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"module"),
            "template"=>array("id"=>"template","group"=>"General","order"=>"20","default"=>"bottom","label"=>"Thumbnail layout","type"=>"array","subType"=>"select","values"=>array("original","bottom","left","right","top"),"scope"=>"module"),
            "include-headers-on-all-pages"=>array("id"=>"include-headers-on-all-pages","group"=>"General","order"=>"21","default"=>"No","label"=>"Include headers on all pages","description"=>"To be able to apply an effect on any page","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
            "magicscroll"=>array("id"=>"magicscroll","group"=>"General","order"=>"22","default"=>"No","label"=>"Scroll thumbnails","description"=>"Powered by the versatile <a target=\"_blank\" href=\"http://www.magictoolbox.com/magicthumb/magicscroll/\">Magic Scroll</a>™. Normally £29, yours is discounted to £19. <a target=\"_blank\" href=\"http://www.magictoolbox.com/buy/magicscroll/\">Buy a license</a> and upload magicscroll.js to your server. <a target=\"_blank\" href=\"http://www.magictoolbox.com/contact/\">Contact us</a> for help.","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"module"),
            "thumb-max-width"=>array("id"=>"thumb-max-width","group"=>"Positioning and Geometry","order"=>"10","default"=>"450","label"=>"Maximum width of thumbnail (in pixels)","type"=>"num","scope"=>"module"),
            "thumb-max-height"=>array("id"=>"thumb-max-height","group"=>"Positioning and Geometry","order"=>"11","default"=>"450","label"=>"Maximum height of thumbnail (in pixels)","type"=>"num","scope"=>"module"),
            "square-images"=>array("id"=>"square-images","group"=>"Positioning and Geometry","order"=>"40","default"=>"No","label"=>"Always create square images","description"=>"","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
            "expandEffect"=>array("id"=>"expandEffect","group"=>"Expanded view","order"=>"10","default"=>"expand","label"=>"Effect while enlarging image","type"=>"array","subType"=>"select","values"=>array("expand","fade"),"scope"=>"magicthumb"),
            "expandSpeed"=>array("id"=>"expandSpeed","group"=>"Expanded view","order"=>"20","default"=>"350","label"=>"Duration when enlarging image (milliseconds)","description"=>"0-10000, e.g. 2000 = 2 seconds","type"=>"num","scope"=>"magicthumb"),
            "expandImageSize"=>array("id"=>"expandImageSize","group"=>"Expanded view","order"=>"30","default"=>"fit-screen","label"=>"Size of expanded box","type"=>"array","subType"=>"select","values"=>array("fit-screen","original"),"scope"=>"magicthumb"),
            "expandTrigger"=>array("id"=>"expandTrigger","group"=>"Expanded view","order"=>"40","default"=>"click","label"=>"Mouse trigger to expand","type"=>"array","subType"=>"select","values"=>array("click","hover"),"scope"=>"magicthumb"),
            "expandAlign"=>array("id"=>"expandAlign","group"=>"Expanded view","order"=>"50","default"=>"screen","label"=>"Align expanded box relative to screen or thumbnail","type"=>"array","subType"=>"select","values"=>array("screen","image"),"scope"=>"magicthumb"),
            "expandEasing"=>array("id"=>"expandEasing","group"=>"Expanded view","order"=>"60","default"=>"ease-in-out","label"=>"CSS3 Animation Easing","description"=>"CSS3 Animation Easing. See cubic-bezier.com","type"=>"text","scope"=>"magicthumb"),
            "gallerySpeed"=>array("id"=>"gallerySpeed","group"=>"Expanded view","order"=>"70","default"=>"250","label"=>"Duration when switching image (milliseconds)","description"=>"0-10000, e.g. 2000 = 2 seconds","type"=>"num","scope"=>"magicthumb"),
            "selector-max-width"=>array("id"=>"selector-max-width","group"=>"Multiple images","order"=>"10","default"=>"56","label"=>"Thumbnail width (maximum)","type"=>"num","scope"=>"module"),
            "selector-max-height"=>array("id"=>"selector-max-height","group"=>"Multiple images","order"=>"11","default"=>"56","label"=>"Thumbnail height (maximum)","type"=>"num","scope"=>"module"),
            "show-selectors-on-category-page"=>array("id"=>"show-selectors-on-category-page","group"=>"Multiple images","order"=>"20","default"=>"No","label"=>"Show selectors on category page","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"module"),
            "use-individual-titles"=>array("id"=>"use-individual-titles","group"=>"Multiple images","order"=>"40","default"=>"Yes","label"=>"Individual image titles","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
            "selectorTrigger"=>array("id"=>"selectorTrigger","group"=>"Multiple images","order"=>"110","default"=>"click","label"=>"Trigger for selector","type"=>"array","subType"=>"select","values"=>array("click","hover"),"scope"=>"magicthumb"),
            "selectorEffect"=>array("id"=>"selectorEffect","group"=>"Multiple images","order"=>"115","default"=>"switch","label"=>"Effect for selector","type"=>"array","subType"=>"select","values"=>array("switch","expand"),"scope"=>"magicthumb"),
            "show-caption"=>array("id"=>"show-caption","group"=>"Title and Caption","order"=>"20","default"=>"Yes","label"=>"Show caption","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
            "captionPosition"=>array("id"=>"captionPosition","group"=>"Title and Caption","order"=>"150","default"=>"bottom","label"=>"Position of caption in zoom window","type"=>"array","subType"=>"select","values"=>array("bottom","right","off"),"scope"=>"magicthumb"),
            "link-to-product-page"=>array("id"=>"link-to-product-page","group"=>"Miscellaneous","order"=>"30","default"=>"Yes","label"=>"Link enlarged image to the product page","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"module"),
            "option-associated-with-images"=>array("id"=>"option-associated-with-images","group"=>"Miscellaneous","order"=>"40","default"=>"color","label"=>"Product option names associated with images","description"=>"(e.g 'Color,Size'). You should assign labels to all the product images associated with the option's values, e.g., if option's values are 'red', 'blue' and 'white', then you should have 3 images with labels: 'red', 'blue' and 'white'","type"=>"text","scope"=>"module"),
            "show-associated-product-images"=>array("id"=>"show-associated-product-images","group"=>"Miscellaneous","order"=>"41","default"=>"Yes","label"=>"Show associated product's images","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
            "load-associated-product-images"=>array("id"=>"load-associated-product-images","group"=>"Miscellaneous","order"=>"42","default"=>"when option is selected","label"=>"Load associated product's images","type"=>"array","subType"=>"radio","values"=>array("when option is selected","within a gallery"),"scope"=>"module"),
            "ignore-magento-css"=>array("id"=>"ignore-magento-css","group"=>"Miscellaneous","order"=>"50","default"=>"Yes","label"=>"Ignore magento CSS width/height styles for additional images","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
            "keyboard"=>array("id"=>"keyboard","group"=>"Miscellaneous","order"=>"230","default"=>"Yes","label"=>"Allow navigation with right / left keys and close on Esc","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicthumb"),
            "cssClass"=>array("id"=>"cssClass","group"=>"Miscellaneous","order"=>"250","default"=>"","label"=>"Extra CSS class applied to lightbox","type"=>"text","scope"=>"magicthumb"),
            "rightClick"=>array("id"=>"rightClick","group"=>"Miscellaneous","order"=>"260","default"=>"Yes","label"=>"Whether to allow context menu on right click","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicthumb"),
            "lazyLoad"=>array("id"=>"lazyLoad","group"=>"Miscellaneous","order"=>"270","default"=>"No","label"=>"Whether to load large image on demand","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicthumb"),
            "autostart"=>array("id"=>"autostart","group"=>"Miscellaneous","order"=>"280","default"=>"Yes","label"=>"Whether to start Thumb on image automatically on page load or manually","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicthumb"),
            "buttons"=>array("id"=>"buttons","group"=>"Buttons","order"=>"160","default"=>"auto","label"=>"Position of buttons in expand window","type"=>"array","subType"=>"select","values"=>array("auto","top left","top right","bottom left","bottom right","off"),"scope"=>"magicthumb"),
            "textBtnClose"=>array("id"=>"textBtnClose","group"=>"Buttons","order"=>"170","default"=>"Close","label"=>"Title of close button","type"=>"text","scope"=>"magicthumb"),
            "textBtnNext"=>array("id"=>"textBtnNext","group"=>"Buttons","order"=>"180","default"=>"Next","label"=>"Title of next button","type"=>"text","scope"=>"magicthumb"),
            "textBtnPrev"=>array("id"=>"textBtnPrev","group"=>"Buttons","order"=>"190","default"=>"Previous","label"=>"Title of previous button","type"=>"text","scope"=>"magicthumb"),
            "hint"=>array("id"=>"hint","group"=>"Hint","order"=>"120","default"=>"once","label"=>"How to show hint","type"=>"array","subType"=>"select","values"=>array("once","always","off"),"scope"=>"magicthumb"),
            "textClickHint"=>array("id"=>"textClickHint","group"=>"Hint","order"=>"130","default"=>"Click to expand","label"=>"Hint that shows when zoom is active.","type"=>"text","scope"=>"magicthumb"),
            "textHoverHint"=>array("id"=>"textHoverHint","group"=>"Hint","order"=>"130","default"=>"Hover to expand","label"=>"Hint that shows when zoom is active.","type"=>"text","scope"=>"magicthumb"),
            "slideMobileEffect"=>array("id"=>"slideMobileEffect","group"=>"Mobile","order"=>"1","default"=>"rotate","label"=>"Switch image effect in expand window","type"=>"array","subType"=>"select","values"=>array("rotate","straight"),"scope"=>"magicthumb-mobile"),
            "textClickHintForMobile"=>array("id"=>"textClickHintForMobile","group"=>"Mobile","order"=>"2","default"=>"Tap to expand","label"=>"Hint that shows when zoom is active.","type"=>"text","scope"=>"magicthumb-mobile"),
            "width"=>array("id"=>"width","group"=>"Scroll","order"=>"10","default"=>"auto","label"=>"Scroll width","description"=>"auto | pixels | percentage","type"=>"text","scope"=>"magicscroll"),
            "height"=>array("id"=>"height","group"=>"Scroll","order"=>"20","default"=>"auto","label"=>"Scroll height","description"=>"auto | pixels | percentage","type"=>"text","scope"=>"magicscroll"),
            "orientation"=>array("id"=>"orientation","group"=>"Scroll","order"=>"30","default"=>"horizontal","label"=>"Orientation of scroll","type"=>"array","subType"=>"radio","values"=>array("horizontal","vertical"),"scope"=>"magicscroll"),
            "mode"=>array("id"=>"mode","group"=>"Scroll","order"=>"40","default"=>"scroll","label"=>"Scroll mode","type"=>"array","subType"=>"radio","values"=>array("scroll","animation","carousel","cover-flow"),"scope"=>"magicscroll"),
            "items"=>array("id"=>"items","group"=>"Scroll","order"=>"50","default"=>"3","label"=>"Items to show","description"=>"auto | fit | integer | array","type"=>"text","scope"=>"magicscroll"),
            "speed"=>array("id"=>"speed","group"=>"Scroll","order"=>"60","default"=>"600","label"=>"Scroll speed (in milliseconds)","description"=>"e.g. 5000 = 5 seconds","type"=>"num","scope"=>"magicscroll"),
            "autoplay"=>array("id"=>"autoplay","group"=>"Scroll","order"=>"70","default"=>"0","label"=>"Autoplay speed (in milliseconds)","description"=>"e.g. 0 = disable autoplay; 600 = 0.6 seconds","type"=>"num","scope"=>"magicscroll"),
            "loop"=>array("id"=>"loop","group"=>"Scroll","order"=>"80","advanced"=>"1","default"=>"infinite","label"=>"Continue scroll after the last(first) image","description"=>"infinite - scroll in loop; rewind - rewind to the first image; off - stop on the last image","type"=>"array","subType"=>"radio","values"=>array("infinite","rewind","off"),"scope"=>"magicscroll"),
            "step"=>array("id"=>"step","group"=>"Scroll","order"=>"90","default"=>"auto","label"=>"Number of items to scroll","description"=>"auto | integer","type"=>"text","scope"=>"magicscroll"),
            "arrows"=>array("id"=>"arrows","group"=>"Scroll","order"=>"100","default"=>"inside","label"=>"Prev/Next arrows","type"=>"array","subType"=>"radio","values"=>array("inside","outside","off"),"scope"=>"magicscroll"),
            "pagination"=>array("id"=>"pagination","group"=>"Scroll","order"=>"110","advanced"=>"1","default"=>"No","label"=>"Show pagination (bullets)","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicscroll"),
            "easing"=>array("id"=>"easing","group"=>"Scroll","order"=>"120","advanced"=>"1","default"=>"cubic-bezier(.8, 0, .5, 1)","label"=>"CSS3 Animation Easing","description"=>"see cubic-bezier.com","type"=>"text","scope"=>"magicscroll"),
            "scrollOnWheel"=>array("id"=>"scrollOnWheel","group"=>"Scroll","order"=>"130","advanced"=>"1","default"=>"auto","label"=>"Scroll On Wheel mode","description"=>"auto - automatically turn off scrolling on mouse wheel in the 'scroll' and 'animation' modes, and enable it in 'carousel' and 'cover-flow' modes","type"=>"array","subType"=>"radio","values"=>array("auto","turn on","turn off"),"scope"=>"magicscroll"),
            "lazy-load"=>array("id"=>"lazy-load","group"=>"Scroll","order"=>"140","advanced"=>"1","default"=>"No","label"=>"Lazy load","description"=>"Delay image loading. Images outside of view will be loaded on demand.","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicscroll"),
            "scroll-extra-styles"=>array("id"=>"scroll-extra-styles","group"=>"Scroll","order"=>"150","advanced"=>"1","default"=>"","label"=>"Scroll extra styles","description"=>"mcs-rounded | mcs-shadows | bg-arrows | mcs-border","type"=>"text","scope"=>"module"),
            "show-image-title"=>array("id"=>"show-image-title","group"=>"Scroll","order"=>"160","default"=>"No","label"=>"Show image title","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module")
        );
        $this->params->appendParams($params);
    }
}
}
