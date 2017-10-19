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
     * @auth-pass-all
     */
    public static function myEditAssist()
    {
        return self::editAssist();
    }

    /**
     * @inheritdoc
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
     * @auth-pass-all
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
     * @auth-pass-all
     */
    public function actionMyEdit()
    {
        return $this->showFormWithRecord();
    }
}