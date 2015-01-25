<?php
namespace yii\easyii;

use Yii;
use yii\web\View;
use yii\base\Application;
use yii\base\BootstrapInterface;

use yii\easyii\models\Module;
use yii\easyii\models\Setting;
use yii\easyii\assets\LiveAsset;

class AdminModule extends \yii\base\Module implements BootstrapInterface
{
    public $settings;
    public $activeModules;

    public function init()
    {
        parent::init();

        if(Yii::$app->cache === null){
            throw new \yii\web\ServerErrorHttpException('Please configure Cache component.');
        }

        $this->activeModules = Module::findAllActive();

        $modules = [];
        foreach($this->activeModules as $name => $module){
            $modules[$name]['class'] = '\\yii\\easyii\\modules\\'.$name.'\\'.ucfirst($name).'Module';
            if(is_array($module->settings)){
                $modules[$name]['settings'] = $module->settings;
            }
        }
        $this->setModules($modules);

        define('IS_ROOT',  !Yii::$app->user->isGuest && Yii::$app->user->identity->isRoot());
        define('LIVE_EDIT', !Yii::$app->user->isGuest && Yii::$app->session->get('easyii_live_edit'));
    }


    public function bootstrap($app)
    {
        Yii::setAlias('easyii', '@vendor/noumo/easyiicms');

        if(!$app->user->isGuest && strpos($app->request->pathInfo, 'admin') === false) {
            $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
                $app->getView()->on(View::EVENT_BEGIN_BODY, [$this, 'renderToolbar']);
            });
        }
    }

    public function renderToolbar()
    {
        $view = Yii::$app->getView();
        echo $view->render('@easyii/views/layouts/frontend-toolbar.php');
    }
}