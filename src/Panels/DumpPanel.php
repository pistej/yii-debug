<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiisoft\Yii\Debug\Panels;

use Psr\Log\LogLevel;
use Yii;
use yii\base\Request;
use yii\web\View;
use Yiisoft\Yii\Debug\Models\Search\Log;
use Yiisoft\Yii\Debug\Panel;

/**
 * Dump panel that collects and displays debug messages (LogLevel::DEBUG).
 *
 * @author Pistej <pistej2@gmail.com>
 * @author Simon Karlen <simi.albi@outlook.com>
 * @since 2.1.0
 */
class DumpPanel extends Panel
{
    /**
     * @var array the message categories to filter by. If empty array, it means
     * all categories are allowed
     */
    public $categories = ['application'];
    /**
     * @var bool whether the result should be syntax-highlighted
     */
    public $highlight = true;
    /**
     * @var int maximum depth that the dumper should go into the variable
     */
    public $depth = 10;

    /**
     * @var array log messages extracted to array as models, to use with data provider.
     */
    private $_models;
    /** @var Request */
    private $request;

    public function __construct(Request $request, View $view)
    {
        $this->request = $request;
        parent::__construct($view);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Dump';
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary(): string
    {
        return $this->render('panels/dump/summary', ['panel' => $this]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail(): string
    {
        $searchModel = new Log();
        $dataProvider = $searchModel->search($this->request->getQueryParams(), $this->getModels());

        return $this->render('panels/dump/detail', [
            'dataProvider' => $dataProvider,
            'panel' => $this,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $target = $this->module->logTarget;
        $except = [];
        if (isset($this->module->panels['router'])) {
            $except = $this->module->panels['router']->getCategories();
        }

        $messages = $target->filterMessages($target->getMessages(), [LogLevel::DEBUG], $this->categories, $except);

        return $messages;
    }

    /**
     * Returns an array of models that represents logs of the current request.
     * Can be used with data providers, such as \yii\data\ArrayDataProvider.
     *
     * @param bool $refresh if need to build models from log messages and refresh them.
     * @return array models
     */
    protected function getModels($refresh = false)
    {
        if ($this->_models === null || $refresh) {
            $this->_models = [];

            foreach ($this->data as $message) {
                $this->_models[] = [
                    'message' => $message[0],
                    'level' => $message[1],
                    'category' => $message[2],
                    'time' => $message[3] * 1000, // time in milliseconds
                    'trace' => $message[4]
                ];
            }
        }

        return $this->_models;
    }
}
