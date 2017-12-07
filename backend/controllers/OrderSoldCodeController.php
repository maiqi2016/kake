<?php

namespace backend\controllers;

use Yii;

/**
 * 订单核销码管理
 *
 * @auth-inherit-except index add front sort
 */
class OrderSoldCodeController extends GeneralController
{
    // 模型
    public static $modelName = 'OrderSoldCode';

    // 模型描述
    public static $modelInfo = '订单核销码';

    /**
     * @inheritdoc
     */
    public function pageDocument()
    {
        return array_merge(parent::pageDocument(), [
            'my-edit' => [
                'title_icon' => 'edit',
                'title_info' => '编辑',
                'button_info' => '编辑',
                'action' => 'my-edit-form'
            ],
            'sold-code' => [
                'title_icon' => 'check',
                'title_info' => '套餐核销',
                'button_info' => '确认核销',
                'action' => 'verify-sold-code',
                'info_perfect' => true
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function editAssist($action = null)
    {
        return [
            'code',
            'state' => 'select'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function myEditAssist()
    {
        return self::editAssist();
    }

    /**
     * @inheritdoc
     */
    public static function soldCodeAssist()
    {
        return [
            'sold' => [
                'title' => '核销码',
                'class' => 'input-group-lg'
            ]
        ];
    }

    /**
     * @inheritdoc
     * @auth-same {ctrl}/edit
     */
    public function actionEditForm($reference = null, $action = 'edit', $post = null, $caller = null)
    {
        $reference = $reference ?: [
            'success' => 'order-sub/sold',
            'fail' => 'order-sold-code/edit'
        ];

        return parent::actionEditForm($reference, $action, $post, $caller);
    }

    /**
     * @auth-same {ctrl}/my-edit
     */
    public function actionMyEditForm()
    {
        $this->user->supplier = $this->listSupplier($this->user->id);
        $post = Yii::$app->request->post();
        $post['where'] = [
            ['id' => $post['id']],
            ['product_supplier_id' => $this->user->supplier]
        ];

        return self::actionEditForm([
            'success' => 'order-sub/my-sold',
            'fail' => 'order-sold-code/my-edit'
        ], 'edit', $post);
    }

    /**
     * 编辑我的
     *
     * @auth-pass-role 1,9
     */
    public function actionMyEdit()
    {
        return $this->showFormWithRecord();
    }

    /**
     * 套餐核销
     *
     * @auth-pass-role 1,9
     */
    public function actionSoldCode()
    {
        $this->logReference('order-sold-code/sold-code');
        return $this->showForm();
    }

    /**
     * @auth-same {ctrl}/sold-code
     */
    public function actionVerifySoldCode()
    {
        $supplier = $this->listSupplier($this->user->id);
        $result = $this->service('order.verify-sold-code', [
            'sold' => Yii::$app->request->post('sold'),
            'supplier' => $supplier
        ]);

        if (is_string($result)) {
            $flash['danger'] = Yii::t('common', $result);
        } else {
            $flash['success'] = '套餐核销成功';
        }

        $this->goReference($this->getControllerName('sold-code'), $flash);
    }
}