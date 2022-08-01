<?php

namespace MagePal\CartQtyIncrementsMsi\Plugin\Msi;

use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsCorrectQtyCondition;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use MagePal\CartQtyIncrements\Helper\Data;

class IsCorrectQtyConditionPlugin
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * StockStateProviderPlugin constructor.
     * @param Data $helperData
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helperData,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->helperData = $helperData;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param IsCorrectQtyCondition $subject
     * @param callable $proceed
     * @param string $sku
     * @param int $stockId
     * @param float $requestedQty
     * @return ProductSalableResultInterface
     */
    public function aroundExecute(
        IsCorrectQtyCondition $subject,
        callable $proceed,
        string $sku,
        int $stockId,
        float $requestedQty
    ) {
        $result = $proceed($sku, $stockId, $requestedQty);

        if (!$result->isSalable()
            && $this->helperData->hasIgnoreCoreRestriction($this->storeManager->getStore()->getId())
        ) {
            foreach ($result->getErrors() as $error) {
                if ($error->getCode() === 'is_correct_qty-qty_increment') {
                    return $this->productSalableResultFactory->create(['errors' => []]);
                }
            }
        }

        return $result;
    }
}
