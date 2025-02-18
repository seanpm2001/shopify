<?php

namespace craft\shopify\jobs;

use craft\queue\BaseJob;
use craft\shopify\elements\Product;
use craft\shopify\helpers\Api as ApiHelper;
use craft\shopify\helpers\Metafields as MetafieldsHelper;
use craft\shopify\Plugin;
use craft\shopify\records\ProductData as ProductDataRecord;

/**
 * Updates the metadata for a Shopify product.
 */
class UpdateProductMetadata extends BaseJob
{
    public int $shopifyProductId;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $api = Plugin::getInstance()->getApi();

        if ($product = Product::find()->shopifyId($this->shopifyProductId)->one()) {
            $metaFieldsObjects = $api->getMetafieldsByProductId($this->shopifyProductId);
            $metaFields = MetafieldsHelper::unpack($metaFieldsObjects);
            $product->setMetafields($metaFields);
            /** @var ProductDataRecord $productData */
            $productData = ProductDataRecord::find()->where(['shopifyId' => $this->shopifyProductId])->one();
            $productData->metaFields = $metaFields;
            $productData->save();
            ApiHelper::rateLimit(); // Avoid rate limiting
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return null;
    }
}
