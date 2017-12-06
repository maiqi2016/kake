<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;

/**
 * 公众号菜单管理
 *
 * @auth-inherit-except add front sort
 */
class WxMenuController extends GeneralController
{
    /**
     * @inheritDoc
     */
    public static function indexOperation()
    {
        return [
            [
                'text' => 'JSON工具',
                'value' => 'http://www.bejson.com/',
                'level' => 'default',
                'attrs' => [
                    'target' => '_blank'
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'menu' => [
                'title' => 'JSON代码',
                'placeholder' => '以数组符合包围，不可留空',
                'elem' => 'textarea',
                'label' => 8,
                'row' => 15,
                'value' => Helper::formatPrintJson(Yii::$app->wx->getMenu())
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function pageDocument()
    {
        return [
            'index' => [
                'title_icon' => 'cog',
                'title_info' => '编辑菜单接口',
                'button_info' => '提交修改',
                'action' => 'update'
            ]
        ];
    }

    /**
     * 公众号菜单预览
     *
     * @auth-same {ctrl}/edit
     */
    public function actionIndex()
    {
        return $this->showForm();
    }

    /**
     * 公众号菜单编辑
     */
    public function actionUpdate()
    {
        $wx = Yii::$app->wx;
        $post = Yii::$app->request->post();
        $menu = json_decode(Yii::$app->request->post('menu'), true);
        if (empty($menu)) {
            $this->goReference($this->getControllerName('index'), [
                'danger' => '请规范编写菜单JSON代码',
                'list' => $post
            ]);
        }

        $wx->menu->destroy();
        $result = $wx->menu->add($menu);

        if ($result->errmsg == 'ok') {
            $flash['success'] = '菜单编辑成功，5分钟后或重新关注后生效';
        } else {
            $flash['danger'] = $result->errmsg;
            $flash['list'] = $post;
        }

        $this->goReference($this->getControllerName('index'), $flash);
    }
}
