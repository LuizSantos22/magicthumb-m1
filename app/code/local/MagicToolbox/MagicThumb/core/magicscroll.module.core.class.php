<?php

if (!defined('MagicScrollModuleCoreClassLoaded')) {

define('MagicScrollModuleCoreClassLoaded', true);

require_once(dirname(__FILE__) . '/magictoolbox.params.class.php');

/**
 * MagicScrollModuleCoreClass
 *
 */
class MagicScrollModuleCoreClass
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
    public $type = 'category';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($reloadDefaults = true)
    {
        static $params = null;
        if ($params === null) {
            $params = new MagicToolboxParamsClass();
            $params->setScope('magicscroll');
            $params->setMapping(array(
                'width' => array('0' => 'auto'),
                'height' => array('0' => 'auto'),
                'step' => array('0' => 'auto'),
                'pagination' => array('Yes' => 'true', 'No' => 'false'),
                'scrollOnWheel' => array('turn on' => 'true', 'turn off' => 'false'),
                'lazy-load' => array('Yes' => 'true', 'No' => 'false'),
            ));
            //NOTE: if the constructor is called for the first time, we load the defaults anyway
            $reloadDefaults = true;
        }
        $this->params = $params;

        //NOTE: do not load defaults, if they have already been loaded by MagicScroll module
        if ($reloadDefaults) {
            $this->loadDefaults();
        }
    }

    /**
     * Method to get headers string
     *
     * @param string $jsPath  Path to JS file
     * @param string $cssPath Path to CSS file
     *
     * @return string
     */
    public function getHeadersTemplate($jsPath = '', $cssPath = null, $linkModuleCss = true)
    {
        //to prevent multiple displaying of headers
        if (!defined('MAGICSCROLL_MODULE_HEADERS')) {
            define('MAGICSCROLL_MODULE_HEADERS', true);
        } else {
            return '';
        }
        if ($cssPath == null) {
            $cssPath = $jsPath;
        }
        $headers = array();
        // add module version
        $headers[] = '<!-- Magic Thumb Magento module version v4.16.0 [v1.6.97:v3.0.20] -->';
        $headers[] = '<script type="text/javascript">window["mgctlbx$Pltm"] = "Magento";</script>';
        // add tool style link
        $headers[] = '<link type="text/css" href="' . $cssPath . '/magicscroll.css" rel="stylesheet" media="screen" />';
        if ($linkModuleCss) {
            // add module style link
            $headers[] = '<link type="text/css" href="' . $cssPath . '/magicscroll.module.css" rel="stylesheet" media="screen" />';
        }
        // add script link
        $headers[] = '<script type="text/javascript" src="' . $jsPath . '/magicscroll.js"></script>';
        // add options
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
        return "<script type=\"text/javascript\">\n\tMagicScrollOptions = {\n\t\t" . $this->params->serialize(true, ",\n\t\t") . "\n\t}\n</script>";
    }

    /**
     * Method to get MagicScroll HTML
     *
     * @param array $itemsData MagicScroll data
     * @param array $params Additional params
     *
     * @return string
     */
    public function getMainTemplate($itemsData, $params = array())
    {
        $id = '';
        $width = '';
        $height = '';

        $html = array();

        extract($params);

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

        if (empty($id)) {
            $id = '';
        } else {
            $id = ' id="' . addslashes($id) . '"';
        }

        // add div with tool className
        $additionalClasses = $this->params->getValue('scroll-extra-styles');
        if (empty($additionalClasses)) {
            $additionalClasses = '';
        } else {
            $additionalClasses = ' ' . $additionalClasses;
        }

        //NOTE: get personal options
        $options = $this->params->serialize();
        if (empty($options)) {
            $options = '';
        } else {
            $options = ' data-options="' . $options . '"';
        }

        $html[] = '<div' . $id . ' class="MagicScroll' . $additionalClasses . '"' . $width . $height . $options . '>';

        // add items
        foreach ($itemsData as $item) {

            $img = '';
            $img2x = '';
            $thumb = '';
            $thumb2x = '';
            $link = '';
            $target = '';
            $alt = '';
            $title = '';
            $description = '';
            $width = '';
            $height = '';
            $medium = '';
            $content = '';

            extract($item);

            // check big image
            if (empty($img)) {
                $img = '';
            }

            //NOTE: remove this?
            if (!empty($medium)) {
                $thumb = $medium;
            }

            // check thumbnail
            if (!empty($img) || empty($thumb)) {
                $thumb = $img;
            }
            if (!empty($img2x) || empty($thumb2x)) {
                $thumb2x = $img2x;
            }

            // check item link
            if (empty($link)) {
                $link = '';
            } else {
                // check target
                if (empty($target)) {
                    $target = '';
                } else {
                    $target = ' target="' . $target . '"';
                }
                $link = $target . ' href="' . addslashes($link) . '"';
            }

            // check item alt tag
            if (empty($alt)) {
                $alt = '';
            } else {
                $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
            }

            // check title
            if (empty($title)) {
                $title = '';
            } else {
                $title = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
                if (empty($alt)) {
                    $alt = $title;
                }
                if ($this->params->checkValue('show-image-title', 'No')) {
                    $title = '';
                }
            }

            // check description
            if (!empty($description) && $this->params->checkValue('show-image-title', 'Yes')) {
                //$description = preg_replace("/<(\/?)a([^>]*)>/is", "[$1a$2]", $description);
                //NOTICE: span or div?
                //NOTICE: scroll takes the first child after image and place it in span.mcs-caption
                if (empty($title)) {
                    $title = "<span class=\"mcs-description\">{$description}</span>";
                } else {
                    //NOTE: to wrap title in span for show with description
                    $title = "<span>{$title}<br /><span class=\"mcs-description\">{$description}</span></span>";
                }
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
                //$thumb2x = ' srcset="' . $thumb2x . ' 2x"';
                //$thumb2x = ' srcset="' . $thumb . ' 1x, ' . $thumb2x . ' 2x"';
                $thumb2x = ' srcset="' . str_replace(' ', '%20', $thumb) . ' 1x, ' . str_replace(' ', '%20', $thumb2x) . ' 2x"';
            }

            // add item
            if (empty($content)) {
                $html[] = "<a{$link}><img{$width}{$height} src=\"{$thumb}\" {$thumb2x} alt=\"{$alt}\" />{$title}</a>";
            } else {
                $html[] = "<div class=\"mcs-content-container\">{$content}</div>";
            }
        }

        // close core div
        $html[] = '</div>';

        // create HTML string
        $html = implode('', $html);

        // return result
        return $html;
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
