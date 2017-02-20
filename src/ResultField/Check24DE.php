<?php

namespace ElasticExportCheck24DE\ResultField;

use Plenty\Modules\DataExchange\Contracts\ResultFields;
use Plenty\Modules\DataExchange\Models\FormatSetting;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Mutators\SkuMutator;
use Plenty\Modules\Item\Search\Mutators\DefaultCategoryMutator;


/**
 * Class Check24DE
 * @package ElasticExport\ResultFields
 */
class Check24DE extends ResultFields
{

    const CHECK24_DE = 150.00;
    /*
	 * @var ArrayHelper
	 */
    private $arrayHelper;

    /**
     * Billiger constructor.
     * @param ArrayHelper $arrayHelper
     */
    public function __construct(ArrayHelper $arrayHelper)
    {
        $this->arrayHelper = $arrayHelper;
    }

    /**
     * Generate result fields.
     * @param  array $formatSettings = []
     * @return array
     */
    public function generateResultFields(array $formatSettings = []):array
    {
        $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');

        $this->setOrderByList(['variation.itemId', 'ASC']);

        $reference = $settings->get('referrerId') ? $settings->get('referrerId') : self::CHECK24_DE;

        $itemDescriptionFields = ['texts.urlPath'];

        switch($settings->get('nameId'))
        {
            case 1:
                $itemDescriptionFields[] = 'texts.name1';
                break;
            case 2:
                $itemDescriptionFields[] = 'texts.name2';
                break;
            case 3:
                $itemDescriptionFields[] = 'texts.name3';
                break;
            default:
                $itemDescriptionFields[] = 'texts.name1';
                break;
        }

        if($settings->get('descriptionType') == 'itemShortDescription'
            || $settings->get('previewTextType') == 'itemShortDescription')
        {
            $itemDescriptionFields[] = 'texts.shortDescription';
        }

        if($settings->get('descriptionType') == 'itemDescription'
            || $settings->get('descriptionType') == 'itemDescriptionAndTechnicalData'
            || $settings->get('previewTextType') == 'itemDescription'
            || $settings->get('previewTextType') == 'itemDescriptionAndTechnicalData')
        {
            $itemDescriptionFields[] = 'texts.description';
        }
        $itemDescriptionFields[] = 'texts.technicalData';

        //Mutator
        /**
         * @var ImageMutator $imageMutator
         */
        $imageMutator = pluginApp(ImageMutator::class);
        $imageMutator->addMarket($reference);
        /**
         * @var LanguageMutator $languageMutator
         */
        $languageMutator = pluginApp(LanguageMutator::class, [[$settings->get('lang')]]);
        /**
         * @var SkuMutator $skuMutator
         */
        $skuMutator = pluginApp(SkuMutator::class);
        $skuMutator->setMarket($reference);
        /**
         * @var DefaultCategoryMutator $defaultCategoryMutator
         */
        $defaultCategoryMutator = pluginApp(DefaultCategoryMutator::class);
        $defaultCategoryMutator->setPlentyId($settings->get('plentyId'));


        $fields = [
            [
                //item
                'item.id',
                'item.manufacturer.id',

                //variation
                'id',
                'variation.availability.id',
                'variation.stockLimitation',
                'variation.vatId',
                'variation.model',
                'variation.isMain',
                'variation.weightG',

                //images
                'images.all.type',
                'images.all.path',
                'images.all.position',
                'images.all.fileType',
                'images.item.type',
                'images.item.path',
                'images.item.position',
                'images.item.fileType',
                'images.variation.type',
                'images.variation.path',
                'images.variation.position',
                'images.variation.fileType',

                //unit
                'unit.content',
                'unit.id',

                //sku
                'skus.sku',

                //defaultCategories
                'defaultCategories.id',

                //barcodes
                'barcodes.code',
                'barcodes.type',

                //attributes
                'attributes.attributeValueSetId',
                'attributes.attributeId',
                'attributes.valueId',

            ],

            [
                $imageMutator,
                $languageMutator,
                $skuMutator,
                $defaultCategoryMutator
            ],
        ];
        foreach($itemDescriptionFields as $itemDescriptionField)
        {
            $fields[0][] = $itemDescriptionField;
        }

        return $fields;
    }

//    /**
//     * Generate result fields.
//     * @param  array $formatSettings = []
//     * @return array
//     */
//    public function generateResultFields(array $formatSettings = []):array
//    {
//        $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');
//
//        $itemDescriptionFields = ['urlContent']; done
//        $itemDescriptionFields[] = ($settings->get('nameId')) ? 'name' . $settings->get('nameId') : 'name1';
//
//        if($settings->get('descriptionType') == 'itemShortDescription'
//            || $settings->get('previewTextType') == 'itemShortDescription')
//        {
//            $itemDescriptionFields[] = 'shortDescription';
//        }
//
//        if($settings->get('descriptionType') == 'itemDescription'
//            || $settings->get('descriptionType') == 'itemDescriptionAndTechnicalData'
//            || $settings->get('previewTextType') == 'itemDescription'
//            || $settings->get('previewTextType') == 'itemDescriptionAndTechnicalData')
//        {
//            $itemDescriptionFields[] = 'description';
//        }
//
//        if($settings->get('descriptionType') == 'technicalData'
//            || $settings->get('descriptionType') == 'itemDescriptionAndTechnicalData'
//            || $settings->get('previewTextType') == 'technicalData'
//            || $settings->get('previewTextType') == 'itemDescriptionAndTechnicalData')
//        {
//            $itemDescriptionFields[] = 'technicalData';
//        } done
//
//        return [
//            'itemBase'=> [
//                'id',     done
//                'producerId',     done
//            ],
//
//            'itemDescription' => [
//                'params' => [
//                    'language' => $settings->get('lang') ? $settings->get('lang') : 'de',
//                ],
//                'fields' => $itemDescriptionFields,       done
//            ],
//
//            'variationImageList' => [
//                'params' => [
//                    'type' => 'item_variation',
//                    'referenceMarketplace' => $settings->get('referrerId') ? $settings->get('referrerId') : 150,
//                ],
//                'fields' => [
//                    'type',       done
//                    'path',       done
//                    'position',   done
//                ]
//
//            ],
//
//            'variationBase' => [
//                'id',                     done
//                'availability',           done
//                'attributeValueSetId',    done
//                'model',                  done
//                'limitOrderByStockSelect',done
//                'unitId',                 done
//                'content',                done
//                'weightG',                done
//            ],
//
//            'variationRetailPrice' => [
//                'params' => [
//                    'referrerId' => $settings->get('referrerId') ? $settings->get('referrerId') : 150,
//                ],
//                'fields' => [
//                    'price',              done
//                ],
//            ],
//
//            'variationStandardCategory' => [
//                'params' => [
//                    'plentyId' => $settings->get('plentyId'),
//                ],
//                'fields' => [
//                    'categoryId',         done
//                ],
//            ],
//
//            'variationBarcodeList' => [
//                'params' => [
//                    'barcodeType' => $settings->get('barcode') ? $settings->get('barcode') : 'EAN',
//                ],
//                'fields' => [
//                    'variationId',
//                    'code',               done
//                    'barcodeId',          todo
//                    'barcodeType',        done
//                    'barcodeName'         todo
//                ]
//            ],
//
//            'variationMarketStatus' => [
//                'params' => [
//                    'marketId' => 150
//                ],
//                'fields' => [
//                    'sku'                 done
//                ]
//            ],
//
//            'variationStock' => [
//                'params' => [
//                    'type' => 'virtual'
//                ],
//                'fields' => [
//                    'stockNet'            done
//                ]
//            ],
//
//            'variationAttributeValueList' => [
//                'attributeId',            done
//                'attributeValueId',       done
//            ],
//        ];
//    }
}