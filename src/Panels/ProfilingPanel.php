<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiisoft\Yii\Debug\Panels;

use yii\base\Application;
use yii\web\Request;
use yii\web\View;
use Yiisoft\Yii\Debug\Models\Search\Profile;
use Yiisoft\Yii\Debug\Panel;

/**
 * Debugger panel that collects and displays performance profiling info.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ProfilingPanel extends Panel
{
    /**
     * @var array current request profile timings
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
        return 'Profiling';
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary(): string
    {
        return $this->render('panels/profile/summary', [
            'memory' => sprintf('%.3f MB', $this->data['memory'] / 1048576),
            'time' => number_format($this->data['time'] * 1000) . ' ms',
            'panel' => $this
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail(): string
    {
        $searchModel = new Profile();
        $dataProvider = $searchModel->search($this->request->getQueryParams(), $this->getModels());

        return $this->render('panels/profile/detail', [
            'panel' => $this,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'memory' => sprintf('%.3f MB', $this->data['memory'] / 1048576),
            'time' => number_format($this->data['time'] * 1000) . ' ms',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $target = $this->module->profileTarget;
        $messages = $target->messages;
        return [
            'memory' => memory_get_peak_usage(),
            'time' => microtime(true) - YII_BEGIN_TIME,
            'messages' => $messages,
        ];
    }

    /**
     * Returns array of profiling models that can be used in a data provider.
     * @return array models
     */
    protected function getModels()
    {
        if ($this->_models === null) {
            $this->_models = [];

            if (isset($this->data['messages'])) {
                foreach ($this->data['messages'] as $seq => $message) {
                    $this->_models[] = [
                        'duration' => $message['endTime'] * 1000 - $message['beginTime'] * 1000, // in milliseconds
                        'category' => $message['category'],
                        'info' => $message['token'],
                        'level' => $message['nestedLevel'],
                        'timestamp' => $message['beginTime'] * 1000, //in milliseconds
                        'seq' => $seq,
                    ];
                }
            }
        }

        return $this->_models;
    }
}
