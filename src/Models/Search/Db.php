<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiisoft\Yii\Debug\Models\Search;

use yii\data\ArrayDataProvider;
use yii\helpers\Yii;
use Yiisoft\Yii\Debug\Components\Search\Filter;

/**
 * Search model for current request database queries.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class Db extends Base
{
    /**
     * @var string type of the input search value
     */
    public $type;
    /**
     * @var int query attribute input search value
     */
    public $query;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'query'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'type' => 'Type',
            'query' => 'Query',
        ];
    }

    /**
     * Returns data provider with filled models. Filter applied if needed.
     *
     * @param array $models data to return provider for
     * @return \yii\data\ArrayDataProvider
     */
    public function search($models)
    {
        $dataProvider = Yii::createObject([
            '__class' => ArrayDataProvider::class,
            'allModels' => $models,
            'pagination' => false,
            'sort' => [
                'attributes' => ['duration', 'seq', 'type', 'query', 'duplicate'],
            ],
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'type', true);
        $this->addCondition($filter, 'query', true);
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }
}
