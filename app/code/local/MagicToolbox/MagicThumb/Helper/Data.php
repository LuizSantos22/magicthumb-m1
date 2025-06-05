<?php
declare(strict_types=1);

class MagicToolbox_MagicThumb_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected bool $isConfigurableSwatchesEnabled = false;

    public function __construct()
    {
        $helperClass = Mage::getConfig()->getHelperClassName('configurableswatches/data');
        if ($helperClass && class_exists($helperClass, false)) {
            if (Mage::helper('configurableswatches')->isEnabled()) {
                $this->isConfigurableSwatchesEnabled = true;
            }
        }
    }

    /**
     * Retrieve list of option labels
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string[]
     */
    public function getProductOptionLabels(Mage_Catalog_Model_Product $product): array
    {
        static $labels = [];
        $id = (int)$product->getId();

        if (!isset($labels[$id])) {
            $labels[$id] = [];

            if ($product->hasData('child_attribute_label_mapping')) {
                $childAttributeLabelMapping = $product->getChildAttributeLabelMapping();

                if (empty($childAttributeLabelMapping) || !is_array($childAttributeLabelMapping)) {
                    return [];
                }

                // Defensive check: ensure array_merge_recursive input is valid
                $mapping = call_user_func_array('array_merge_recursive', array_values($childAttributeLabelMapping));

                if (isset($mapping['labels']) && is_array($mapping['labels'])) {
                    $labels[$id] = array_unique($mapping['labels']);
                }
            }
        }

        return $labels[$id];
    }

    /**
     * Determine whether to show an image in the product media gallery
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Object $image
     * @return bool
     */
    public function isGalleryImageVisible(Mage_Catalog_Model_Product $product, Varien_Object $image): bool
    {
        static $productImageFilters = [];

        if (!$this->isConfigurableSwatchesEnabled) {
            return true;
        }

        $id = (int)$product->getId();

        if (!isset($productImageFilters[$id])) {
            $filters = $this->getProductOptionLabels($product);

            $filters = array_map(
                fn($label) => $label . Mage_ConfigurableSwatches_Helper_Productimg::SWATCH_LABEL_SUFFIX,
                $filters
            );

            $productImageFilters[$id] = $filters;
        }

        $normalizedLabel = Mage_ConfigurableSwatches_Helper_Data::normalizeKey($image->getLabel());

        return !in_array($normalizedLabel, $productImageFilters[$id], true);
    }

    /**
     * Retrieve list of all gallery images
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Data_Collection
     */
    public function getAllGalleryImages(Mage_Catalog_Model_Product $product): Varien_Data_Collection
    {
        static $allImages = [];
        $id = (int)$product->getId();

        if (!isset($allImages[$id])) {
            $allImages[$id] = new Varien_Data_Collection();

            $images = $product->getMediaGallery('images');

            if (is_array($images)) {
                foreach ($images as $image) {
                    if (!isset($image['file'])) {
                        continue;
                    }

                    $image['url'] = $product->getMediaConfig()->getMediaUrl($image['file']);
                    $image['id'] = $image['value_id'] ?? null;
                    $image['path'] = $product->getMediaConfig()->getMediaPath($image['file']);
                    $allImages[$id]->addItem(new Varien_Object($image));
                }
            }
        }

        return $allImages[$id];
    }

    /**
     * Retrieve list of excluded gallery images
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Data_Collection
     */
    public function getExcludedGalleryImages(Mage_Catalog_Model_Product $product): Varien_Data_Collection
    {
        static $excludedImages = [];
        $id = (int)$product->getId();

        if (!isset($excludedImages[$id])) {
            $excludedImages[$id] = new Varien_Data_Collection();

            $images = $product->getMediaGallery('images');

            if (is_array($images)) {
                foreach ($images as $image) {
                    if (!empty($image['disabled']) && isset($image['file'])) {
                        $image['url'] = $product->getMediaConfig()->getMediaUrl($image['file']);
                        $image['id'] = $image['value_id'] ?? null;
                        $image['path'] = $product->getMediaConfig()->getMediaPath($image['file']);
                        $excludedImages[$id]->addItem(new Varien_Object($image));
                    }
                }
            }
        }

        return $excludedImages[$id];
    }

    /**
     * Retrieve list of used labeled images
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $excludedOnly
     * @return Varien_Object[]
     */
    public function getUsedLabeledImages(Mage_Catalog_Model_Product $product, bool $excludedOnly = false): array
    {
        static $labeledImages = [];
        $id = (int)$product->getId();

        if (!isset($labeledImages[$id])) {
            $labeledImages[$id] = [];

            $labels = array_map('strtolower', array_map('trim', $this->getProductOptionLabels($product)));

            $images = $excludedOnly ? $this->getExcludedGalleryImages($product) : $this->getAllGalleryImages($product);

            foreach ($images as $image) {
                $label = strtolower(trim((string)$image->getLabel()));

                if (in_array($label, $labels, true)) {
                    $labeledImages[$id][$label] = $image;
                }
            }
        }

        return $labeledImages[$id];
    }

    /**
     * Retrieve list of used labeled image's urls
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $excludedOnly
     * @return array<string,array<string,string>>
     */
    public function getUsedLabeledImageUrls(Mage_Catalog_Model_Product $product, bool $excludedOnly = false): array
    {
        static $data = [];
        $id = (int)$product->getId();

        if (!isset($data[$id])) {
            $data[$id] = [];
            $images = $this->getUsedLabeledImages($product, $excludedOnly);

            $magicToolboxHelper = Mage::helper('magicthumb/settings');
            $tool = $magicToolboxHelper->loadTool();
            $imageHelper = Mage::helper('catalog/image');
            $createSquareImages = $tool->params->checkValue('square-images', 'Yes');

            foreach ($images as $key => $image) {
                $imagePath = (string)$image->getPath();

                if (@file_exists($imagePath)) {
                    $imageSize = @getimagesize($imagePath);
                    if ($imageSize === false) {
                        continue;
                    }

                    $bigImage = $imageHelper->init($product, 'image', $image->getFile())->__toString();

                    if ($createSquareImages) {
                        $bigImageSize = max($imageSize[0], $imageSize[1]);
                        $bigImage = $imageHelper->watermark(null, null)->resize($bigImageSize)->__toString();
                    }

                    list($w, $h) = $magicToolboxHelper->magicToolboxGetSizes('thumb', $imageSize);

                    $mediumImage = $imageHelper->watermark(null, null)->resize($w, $h)->__toString();

                    $data[$id][$key] = [
                        'large-image-url' => $bigImage,
                        'small-image-url' => $mediumImage,
                    ];
                }
            }
        }

        return $data[$id];
    }
}
