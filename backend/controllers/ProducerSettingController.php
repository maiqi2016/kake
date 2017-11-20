<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * 分销商设置
 *
 * @auth-inherit-except front sort
 */
class ProducerSettingController extends GeneralController
{
    // 模型
    public static $modelName = 'ProducerSetting';

    // 模型描述
    public static $modelInfo = '分销商';

    public static $ajaxModalListTitle = '选择分销商';

    public static $ajaxModalListRecordFilterValueName = 'producer_id';

    public static $uid;

    /**
     * @inheritDoc
     */
    public function pageDocument()
    {
        return array_merge(parent::pageDocument(), [
            'center' => [
                'title_icon' => 'edit',
                'title_info' => '编辑',
                'button_info' => '编辑',
                'action' => 'setting'
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增分销商',
                'value' => 'producer-setting/add',
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function ajaxModalListOperations()
    {
        return [
            [
                'text' => '选定',
                'type' => 'script',
                'value' => '$.modalRadioValueToInput("radio", "producer_id")',
                'icon' => 'flag'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '分销产品',
                'value' => 'producer-product/index',
                'level' => 'info',
                'icon' => 'link',
                'params' => function($record) {
                    return ['producer_id' => $record['producer_id']];
                }
            ],
            [
                'text' => '用户',
                'value' => 'user/index',
                'level' => 'info',
                'icon' => 'link',
                'params' => function($record) {
                    return ['id' => $record['producer_id']];
                }
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function indexFilter()
    {
        return [
            'username' => [
                'elem' => 'input',
                'table' => 'user'
            ],
            'name' => [
                'elem' => 'input'
            ],
            'theme' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'account_type' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function ajaxModalListFilter()
    {
        return [
            'username' => [
                'elem' => 'input',
                'table' => 'user'
            ],
            'name' => [
                'elem' => 'input'
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'producer_id' => [
                'code',
                'title' => 'UID'
            ],
            'username' => [
                'code',
                'table' => 'user',
                'color' => 'default'
            ],
            'name',
            'theme' => [
                'info',
                'code'
            ],
            'logo_preview_url' => [
                'title' => 'LOGO预览',
                'img' => [
                    'pos' => 'left'
                ],
                'width' => '128px'
            ],
            'account_type' => [
                'info',
                'code',
                'color' => [
                    0 => 'success',
                    1 => 'info'
                ]
            ],
            'account_number' => [
                'code',
                'empty',
                'color' => 'default'
            ],
            'add_time' => 'tip',
            'update_time' => 'tip',
            'state' => [
                'code',
                'info',
                'color' => [
                    0 => 'danger',
                    1 => 'info',
                    2 => 'default'
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function ajaxModalListAssist()
    {
        return [
            'username' => [
                'code',
                'table' => 'user'
            ],
            'name',
            'state' => [
                'code',
                'info',
                'color' => [
                    0 => 'danger',
                    1 => 'info',
                    2 => 'default'
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function editAssist($action = null)
    {
        return [
            'producer_id' => [
                'readonly' => true,
                'same_row' => true
            ],
            'select_producer' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择用户',
                'script' => '$.showPage("user.list", {role: 10, state: 1})'
            ],
            'name' => [
                'placeholder' => '32个字以内'
            ],
            'theme' => [
                'elem' => 'select',
                'value' => 1
            ],

            'logo_attachment_id' => [
                'hidden' => true
            ],
            'old_logo_attachment_id' => [
                'value_key' => 'logo_attachment_id',
                'hidden' => true
            ],
            'logo_preview_url' => [
                'img_label' => 2,
                'title' => 'LOGO预览',
                'elem' => 'img',
                'upload_name' => 'upload_logo'
            ],
            'upload_log_tip' => [
                'title' => '',
                'label' => 4,
                'elem' => 'text',
                'value' => '不上传头像默认使用微信头像'
            ],
            'upload_logo' => [
                'title' => '',
                'type' => 'file',
                'tag' => 1,
                'rules' => [
                    'suffix' => 'jpg,jpeg,png',
                    'pic_sizes' => '256*256',
                    'max_size' => 512
                ],
                'preview_name' => 'logo_preview_url',
                'field_name' => 'logo_attachment_id'
            ],

            'account_type' => [
                'elem' => 'select',
                'value' => 0
            ],
            'account_number' => [
                'placeholder' => '佣金提现收款账号',
                'show' => ['account_type' => 'value != 0']
            ],
            'spread_url' => [
                'title' => '推广链接',
                'label' => 6,
                'elem' => 'text'
            ],
            'spread_img' => [
                'title' => '推广二维码',
                'img_label' => 4,
                'elem' => 'img',
                'readonly' => true
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1,
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function addAssist()
    {
        $assist = self::editAssist();
        unset($assist['spread_url'], $assist['spread_img']);

        return $assist;
    }

    /**
     * 分销商设置辅助编辑
     */
    public static function centerAssist()
    {
        $assist = self::editAssist();
        $assist['producer_id'] = [
            'value' => self::$uid,
            'hidden' => true
        ];
        $assist['id'] = [
            'hidden' => true
        ];
        unset($assist['select_producer'], $assist['spread_url'], $assist['spread_img']);

        return $assist;
    }

    /**
     * 分销商列表 - 弹出
     *
     * @auth-same producer-setting/index
     */
    public function actionAjaxModalList()
    {
        return $this->showList();
    }

    /**
     * 我的分销商设置
     *
     * @auth-pass-role 1,10
     */
    public function actionCenter()
    {
        self::$uid = $this->user->id;
        $this->logReference($this->getControllerName('center'));

        return $this->showFormWithRecord([
            ['producer_id' => self::$uid]
        ]);
    }

    /**
     * 分销商设置
     *
     * @auth-pass-role 1,10
     */
    public function actionSetting()
    {
        $reference = $this->getControllerName('center');
        $post = Yii::$app->request->post();
        $post['producer_id'] = $this->user->id;

        if (!empty($post['id'])) {
            $this->actionEditForm($reference, 'edit', $post);
        } else {
            $this->actionAddForm($reference, 'add', $post);
        }
    }

    /**
     * @inheritDoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                [
                    'table' => 'attachment',
                    'as' => 'logo',
                    'left_on_field' => 'logo_attachment_id'
                ],
                [
                    'table' => 'user',
                    'left_on_field' => 'producer_id'
                ]
            ],
            'select' => [
                'logo.deep_path AS logo_deep_path',
                'logo.filename AS logo_filename',
                'producer_setting.*',
                'user.username',
                'user.head_img_url',
                'user.phone'
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function ajaxModalListCondition()
    {
        return $this->indexCondition();
    }

    /**
     * @inheritDoc
     */
    public function editCondition()
    {
        $condition = $this->indexCondition();
        unset($condition['order']);

        return $condition;
    }

    /**
     * 分销商设置查询辅助
     */
    public function centerCondition()
    {
        return $this->editCondition();
    }

    /**
     * 获取推广信息
     *
     * @access public
     *
     * @param integer $userId
     * @param boolean $saveTmp
     *
     * @return array
     */
    public function spreadInfo($userId, $saveTmp = false)
    {
        $producer = $this->getProducer($userId);
        if (empty($producer)) {
            return [];
        }

        $channel = Helper::integerEncode($userId);
        $link = Yii::$app->params['frontend_url'] . '/?r=distribution/items&channel=' . $channel;

        $logoPath = parent::getPathByUrl(current($producer['logo_preview_url']));
        if (!$logoPath) {
            return [
                $link,
                null,
                $producer
            ];
        }

        $qr = $this->createQrCode($link, 400, $logoPath);
        $file = $qr->writeDataUri();

        if ($saveTmp) {
            $file = Yii::$app->params['tmp_path'] . '/' . uniqid('qr_code_', true) . '.jpg';
            $qr->writeFile($file);
        }

        return [
            $link,
            $file,
            $producer
        ];
    }

    /**
     * @inheritdoc
     */
    public function preHandleField($record, $action = null)
    {
        if (isset($record['account_type']) && $record['account_type'] == 0 && empty($record['account_number'])) {
            $record['account_number'] = 'AUTO:openid';
        }

        return parent::preHandleField($record, $action);
    }

    /**
     * @inheritDoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        // 生成封面图附件地址
        if (!empty($record['logo_attachment_id'])) {
            $record = $this->createAttachmentUrl($record, ['logo_attachment_id' => 'logo']);
        } else if (!empty($record['head_img_url'])) {
            $record['logo_preview_url'][1] = $record['head_img_url'];
        }

        if (isset($record['account_type']) && !isset($record['account_type_info'])) {
            $type = parent::model(self::$modelName)->_account_type;
            $record['account_type_info'] = $type[$record['account_type']];
        }

        if ($action == 'edit') {
            $spread = $this->spreadInfo($record['producer_id']);
            if (!empty($spread)) {
                list($record['spread_url'], $record['spread_img']) = $spread;
                $record['spread_img'] = ['qr' => $record['spread_img']];
            }
        }

        return parent::sufHandleField($record, $action);
    }

    /**
     * 生成推广链接
     *
     * @auth-pass-role 1,10
     */
    public function actionSpread()
    {
        $spread = $this->spreadInfo($this->user->id);

        if (!isset($spread[1])) {
            Yii::$app->session->setFlash('warning', '请先完善个人设置');

            return $this->redirect(['producer-setting/center']);
        }

        list($link, $img) = $spread;

        return $this->display('spread', compact('link', 'img'));
    }

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        $this->sourceJs = [
            'jquery.ajaxupload',
            'ckeditor/ckeditor',
            '/node_modules/cropper/dist/cropper.min'
        ];
        $this->sourceCss = ['/node_modules/cropper/dist/cropper.min'];

        return parent::beforeAction($action);
    }
}
