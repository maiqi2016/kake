<?php

namespace backend\controllers;

use Oil\src\Helper;
use Yii;
use yii\helpers\Markdown;

/**
 * 用户管理
 *
 * @auth-inherit-except add front sort
 */
class UserController extends GeneralController
{
    // 模型
    public static $modelName = 'User';

    // 模型描述
    public static $modelInfo = '用户';

    // 用户列表弹窗标题
    public static $ajaxModalListTitle = '选择用户';

    /**
     * @inheritdoc
     */
    public static function indexOperations()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function indexOperationForm()
    {
        return [
            [
                'text' => '获取UID',
                'type' => 'attr',
                'level' => 'success condition-global-event',
                'params' => [
                    'event' => 'user-ids'
                ],
                'icon' => 'piggy-bank'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '同步',
                'value' => 'sync-user',
                'level' => 'success',
                'icon' => 'retweet',
                'params' => ['openid']
            ],
            [
                'text' => '配置权限',
                'value' => 'edit-auth',
                'level' => 'info',
                'icon' => 'cog',
                'show_condition' => function ($record) {
                    return $record['role'] >= 1;
                }
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function ajaxModalListOperations()
    {
        $field = Yii::$app->request->get('field_name') ?: Yii::$app->request->post('field_name', 'producer_id');

        return [
            [
                'text' => '提交选择',
                'type' => 'script',
                'value' => '$.modalRadioValueToInput("radio", "' . $field . '")',
                'icon' => 'flag'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'username' => 'input',
            'phone' => 'input',
            'manager' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'role' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'sex' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'country' => 'input',
            'province' => 'input',
            'city' => 'input',
            'add_time' => [
                'elem' => 'input',
                'type' => 'date',
                'between' => true
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'id',
            'username',
            'manager',
            'role',
            'update_time',
            'state'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function ajaxModalListFilter()
    {
        return [
            'username' => 'input',
            'phone' => 'input',
            'manager' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'role' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'sex' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'id' => 'code',
            'head_img_url' => [
                'img',
                'width' => '64px'
            ],
            'username',
            'phone' => 'empty',
            'manager' => [
                'code',
                'color' => [
                    0 => 'default',
                    1 => 'success'
                ],
                'info'
            ],
            'role' => [
                'code',
                'color' => [
                    0 => 'default',
                    1 => 'primary'
                ],
                'info'
            ],
            'sex' => [
                'code',
                'color' => [
                    0 => 'default',
                    1 => 'primary',
                    2 => 'danger'
                ],
                'info'
            ],
            'address' => [
                'title' => '地址',
                'tip'
            ],
            'update_time',
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function ajaxModalListAssist()
    {
        return [
            'username',
            'phone' => 'empty',
            'manager' => [
                'code',
                'color' => [
                    0 => 'default',
                    1 => 'success'
                ],
                'info'
            ],
            'role' => [
                'code',
                'color' => [
                    0 => 'default',
                    1 => 'primary'
                ],
                'info'
            ],
            'sex' => [
                'code',
                'color' => [
                    0 => 'default',
                    1 => 'primary',
                    2 => 'danger'
                ],
                'info'
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function editAssist($action = null)
    {
        return [
            'username' => [
                'placeholder' => '建议填写'
            ],
            'phone',
            'manager' => [
                'elem' => 'select',
                'value' => 0
            ],
            'role' => [
                'elem' => 'select',
                'value' => 0,
                'tip' => '该标识仅用于默认权限控制，请谨慎选择'
            ],
            'openid' => [
                'label' => 4,
                'readonly' => true
            ],
            'sex' => [
                'elem' => 'select'
            ],
            'country',
            'province',
            'city',
            'head_img_url' => [
                'label' => 6,
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * 用户列表弹窗 - 分销商编辑、分销商申请、编辑供应商用户
     * @auth-same {ctrl}/index
     */
    public function actionAjaxModalList()
    {
        return $this->showList();
    }

    /**
     * 编辑 (危险)
     *
     * @inheritdoc
     * @auth-info-style <span class="text-danger">{info}</span>
     */
    public function actionEdit()
    {
        return parent::showFormWithRecord();
    }

    /**
     * 权限编辑 (危险)
     *
     * @access          public
     * @auth-info-style <span class="text-danger">{info}</span>
     */
    public function actionEditAuth()
    {
        $userId = Yii::$app->request->get('id');
        if (empty($userId)) {
            $this->error('用户ID参数未指定');
        }

        $authList = $this->getAuthList(true);
        $authRecord = array_keys($this->getAuthRecord($userId));
        $admin = $this->listUser([
            ['manager' => 1],
            ['role' => 1]
        ]);

        return $this->display('auth', [
            'user_id' => $userId,
            'list' => $authList,
            'record' => $authRecord,
            'admin' => $admin
        ]);
    }

    /**
     * 获取管理员的权限列表
     *
     * @access    public
     * @auth-same {ctrl}/edit-auth
     *
     * @param integer $id
     *
     * @return void
     */
    public function actionAjaxGetUserAuth($id)
    {
        $this->success($this->getAuthRecord($id));
    }

    /**
     * 权限编辑动作
     *
     * @access    public
     * @auth-same {ctrl}/edit-auth
     */
    public function actionEditAuthForm()
    {
        $oldAuth = Yii::$app->request->post('old_auth');
        $oldAuth = empty($oldAuth) ? [] : explode(',', $oldAuth);
        $nowAuth = Yii::$app->request->post('new_auth', []);

        $result = Helper::getDiffWithAction($oldAuth, $nowAuth);
        if (!$result) {
            $this->goReference($this->getControllerName('index'), [
                'warning' => '权限配置未曾变化'
            ]);
        }

        list($add, $del) = $result;
        $result = $this->service('user.edit-auth', [
            'user_id' => Yii::$app->request->post('user_id'),
            'add' => $add,
            'del' => $del
        ]);

        if (is_string($result)) {
            $flash['danger'] = $result;
        } else {
            $flash['success'] = '权限配置成功';
        }

        $this->goReference($this->getControllerName('index'), $flash);
    }

    /**
     * 同步用户信息
     *
     * @access public
     *
     * @param string $openid
     */
    public function actionSyncUser($openid)
    {
        $user = Yii::$app->oil->wx->user->get($openid);

        $key = $this->getControllerName('index');
        if (!isset($user->nickname)) {
            $this->goReference($key, [
                'info' => '该用户未关注公众号，无法同步'
            ]);
        }

        $result = $this->service(self::$apiGeneralUpdate, [
            'table' => 'user',
            'where' => ['openid' => $openid],
            'username' => Helper::filterEmjoy($user['nickname']),
            'sex' => $user['sex'],
            'city' => $user['city'],
            'province' => $user['province'],
            'country' => $user['country'],
            'head_img_url' => $user['headimgurl'],
        ]);

        if (is_string($result)) {
            $this->goReference($key, [
                'danger' => Yii::t('common', $result)
            ]);
        }

        $this->goReference($key, [
            'success' => '同步用户信息成功'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        if (!empty($record['country'])) {
            $record['address'] = Helper::joinString('-', $record['country'], $record['province'], $record['city']);
        }

        return parent::sufHandleField($record, $action);
    }

    /**
     * 获取用户ID串
     *
     * @access public
     * @return void
     */
    public function actionIndexUserIds()
    {
        list($list) = $this->showList('index', true, false, [
            'size' => 0,
            'select' => 'id'
        ]);

        $user = [];
        foreach ($list as $item) {
            $user[] = $item['id'];
        }

        $this->goReference($this->getControllerName('index'), [
            'success' => implode(',', $user)
        ]);
    }

    /**
     * Parse markdown text to html
     *
     * @param string $name
     *
     * @return string
     */
    private function markdown($name)
    {
        $file = Yii::$app->getViewPath() . DS . 'markdown/' . $name . '.md';

        return Markdown::process(file_get_contents($file), 'extra');
    }

    /**
     * 密码文档
     *
     * @auth-pass-all
     * @return bool|string
     */
    public function actionSecret()
    {
        if ($this->user->id > 2) {
            $this->error('查看密码文档权限不足');
        }

        return $this->display('markdown', ['markdown' => $this->markdown('secret')]);
    }

    /**
     * 后台及业务文档
     *
     * @auth-pass-all
     * @return bool|string
     */
    public function actionLogicDocument()
    {
        return $this->display('markdown', ['markdown' => $this->markdown('logic-document')]);
    }
}