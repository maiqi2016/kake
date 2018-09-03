<?php

namespace backend\controllers;

use Oil\src\Helper;
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
            ],
            'build' => [
                'title_icon' => 'plus',
                'title_info' => '生成',
                'button_info' => '生成',
                'action' => 'build-form'
            ],
            'import' => [
                'title_icon' => 'import',
                'title_info' => '导入',
                'button_info' => '导入',
                'action' => 'import-form'
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

    // ---

    /**
     * @inheritdoc
     */
    public static function buildAssist()
    {
        return [
            'total' => [
                'title' => '生成条数',
            ],
            'product_supplier_id' => [
                'elem' => 'select',
                'list_table' => 'product_supplier',
                'list_value' => 'name',
            ],
            'package_id' => [
                'title' => '产品套餐',
                'readonly' => true,
                'same_row' => true
            ],
            'select_package_id' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择套餐',
                'script' => '$.showPage("product-package.list", {modal_size: "md", field_name: "package_id", only: "yes"})'
            ],
            'remark' => 'textarea',
        ];
    }

    /**
     * 生成核销码
     */
    public function actionBuild()
    {
        return $this->showForm();
    }

    /**
     * @auth-same {ctrl}/build
     */
    public function actionBuildForm()
    {
        $p = Yii::$app->request->post();

        if (empty($p['total']) || empty($p['product_supplier_id']) || empty($p['package_id']) || empty($p['remark'])) {
            $this->goReference($this->getControllerName('build'), [
                'danger' => '所有字段都不能为空',
                'list' => $p
            ]);
        }

        $result = $this->service('order.build', $p);
        if (is_string($result)) {
            $this->goReference($this->getControllerName('build'), [
                'danger' => $result,
                'list' => $p
            ]);
        }

        $this->goReference('order-sub/sold', ['success' => '生成成功']);
    }

    // ---

    /**
     * @inheritdoc
     */
    public static function importAssist()
    {
        return [
            'product_supplier_id' => [
                'elem' => 'select',
                'list_table' => 'product_supplier',
                'list_value' => 'name',
            ],
            'package_id' => [
                'title' => '产品套餐',
                'readonly' => true,
                'same_row' => true
            ],
            'select_package_id' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择套餐',
                'script' => '$.showPage("product-package.list", {modal_size: "md", field_name: "package_id", only: "yes"})'
            ],

            'excel_id' => [
                'title' => 'Excel',
                'readonly' => true
            ],
            'upload' => [
                'type' => 'file',
                'tag' => 1,
                'rules' => [
                    'suffix' => 'xls,xlsx',
                    'max_size' => 1024 * 5
                ],
                'field_name' => 'excel_id'
            ],

            'remark' => 'textarea',
        ];
    }

    /**
     * 导入核销码
     */
    public function actionImport()
    {
        $this->sourceJs = ['jquery.ajaxupload'];

        return $this->showForm();
    }

    /**
     * @auth-same {ctrl}/import
     */
    public function actionImportForm()
    {
        $p = Yii::$app->request->post();

        if (empty($p['excel_id']) || empty($p['product_supplier_id']) || empty($p['package_id']) || empty($p['remark'])) {
            $this->goReference($this->getControllerName('import'), [
                'danger' => '所有字段都不能为空',
                'list' => $p
            ]);
        }

        $result = $this->service('order.import', $p);
        if (is_string($result)) {
            $this->goReference($this->getControllerName('import'), [
                'danger' => $result,
                'list' => $p
            ]);
        }

        $this->goReference('order-sub/sold', ['success' => '导入成功']);
    }
}