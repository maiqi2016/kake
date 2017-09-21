<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;
use yii\helpers\Url;

/**
 * 分销商申请管理
 *
 * @auth-inherit-except front sort
 */
class ProducerApplyController extends GeneralController
{
    // 模型
    public static $modelName = 'ProducerApply';

    // 模型描述
    public static $modelInfo = '分销商申请';

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增分销商申请',
                'value' => 'producer-apply/add',
                'icon' => 'plus'
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
                'text' => '用户',
                'value' => 'user/index',
                'level' => 'info',
                'icon' => 'link',
                'params' => function ($record) {
                    return ['id' => $record['user_id']];
                }
            ],
            [
                'text' => '通过',
                'value' => 'agree-apply',
                'level' => 'success confirm-button',
                'icon' => 'thumbs-up',
                'show_condition' => function ($record) {
                    return $record['state'] == 1;
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
            'phone' => 'input',
            'name' => 'input',
            'state' => [
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'user_id' => 'code',
            'phone',
            'name',
            'add_time',
            'preview_url' => [
                'img' => [
                    'pos' => 'left'
                ],
                'width' => '128px',
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexSorter()
    {
        return ['add_time'];
    }

    /**
     * @inheritDoc
     */
    public static function editAssist($action = null)
    {
        return [
            'user_id' => [
                'readonly' => true,
                'same_row' => true
            ],
            'select_user' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择用户',
                'script' => '$.showPage("user.list", {role: 0, state: 1, field_name: "user_id"})'
            ],
            'name' => [
                'placeholder' => '32个字以内'
            ],
            'phone',

            'attachment_id' => [
                'hidden' => true
            ],
            'old_attachment_id' => [
                'value_key' => 'attachment_id',
                'hidden' => true
            ],
            'preview_url' => [
                'elem' => 'img',
                'img_label' => 3,
                'upload_name' => 'upload'
            ],
            'upload' => [
                'type' => 'file',
                'tag' => 1,
                'rules' => [
                    'suffix' => 'jpg,jpeg,png',
                    'pic_sizes' => '128*128',
                    'max_size' => 512
                ],
                'preview_name' => 'preview_url',
                'field_name' => 'attachment_id'
            ],

            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                ['table' => 'attachment']
            ],
            'select' => [
                'attachment.deep_path',
                'attachment.filename',
                'producer_apply.*'
            ],
            'order' => [
                'producer_apply.state DESC',
                'producer_apply.update_time DESC'
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function editCondition()
    {
        return $this->indexCondition();
    }

    /**
     * @inheritDoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        $record = $this->createAttachmentUrl($record, 'attachment_id');

        return parent::sufHandleField($record, $action);
    }

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        $this->sourceJs = [
            'jquery.ajaxupload',
            '/node_modules/cropper/dist/cropper.min'
        ];
        $this->sourceCss = ['/node_modules/cropper/dist/cropper.min'];

        return parent::beforeAction($action);
    }

    /**
     * 通过申请
     *
     * @access public
     *
     * @param integer $id
     *
     * @return void
     */
    public function actionAgreeApply($id)
    {
        $result = $this->service('producer.agree-apply', ['id' => $id]);

        if (is_string($result)) {
            Yii::$app->session->setFlash('danger', Yii::t('common', $result));
        } else {

            if (!empty($result['avatar'])) {
                $url = Yii::$app->params['upload_path'];

                $avatar = $result['avatar'];
                $img = Helper::joinString('/', $url, $avatar['deep_path'], $avatar['filename']);
                $this->thumbCrop($img, 256, 256, true);
            }

            Yii::$app->wx->notice->send([
                'touser' => $result['openid'],
                'template_id' => '',
                'url' => Yii::$app->params['frontend_url'] . Url::toRoute(['producer/index']),
                'data' => [
                    'first' => "您的分销商申请已被通过\n",
                    'keyword1' => [
                        date('Y-m-d H:i:s'),
                        '#999'
                    ],
                    'keyword2' => [
                        Helper::integerEncode($result['user_id']),
                        '#999'
                    ],
                    'keyword3' => [
                        $result['name'],
                        '#999'
                    ],
                    'remark' => [
                        "\n如有疑问请联系客服 " . Yii::$app->params['company_tel'],
                        '#fda443'
                    ]
                ]
            ]);
            Yii::$app->session->setFlash('success', '通过申请操作完成');
        }

        $this->goReference($this->getControllerName('index'));
    }
}
