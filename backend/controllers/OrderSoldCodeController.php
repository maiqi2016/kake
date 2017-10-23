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
     * @inheritDoc
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
        ]);
    }

    /**
     * @inheritDoc
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
        return $this->display('sold-code');
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
            Yii::$app->session->setFlash('danger', Yii::t('common', $result));
        } else {
            Yii::$app->session->setFlash('success', '套餐核销成功');
        }

        $reference = $this->getControllerName('sold-code');
        $this->goReference($reference);
    }
}