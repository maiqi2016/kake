<?php

namespace backend\controllers;

use Oil\src\Helper;
use common\controllers\MainController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yii;
use yii\helpers\Url;

/**
 * 通用控制器
 */
class GeneralController extends MainController
{
    /**
     * @cont string user info key
     */
    const USER = 'backend_user_info';

    /**
     * @cont string reference
     */
    const REFERENCE = 'backend_reference';

    /**
     * @cont string key for select all
     */
    const SELECT_KEY_ALL = 'all';

    /**
     * @cont string order by
     */
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    /**
     * @var string 数据库
     */
    public static $modelDb = DB_KAKE;

    /**
     * @var string 模型名称
     */
    public static $modelName;

    /**
     * @var string 模型描述
     */
    public static $modelInfo;

    /**
     * @var string 列表接口名
     */
    public static $apiGeneralList = 'general.list-for-backend';
    public static $listFunctionName;

    /**
     * @var string 获取单条接口名
     */
    public static $apiGeneralGet = 'general.get-for-backend';
    public static $getFunctionName;

    /**
     * @var string 编辑接口名
     */
    public static $apiGeneralUpdate = 'general.update-for-backend';
    public static $updateFunctionName;

    /**
     * @var string 新增接口名
     */
    public static $apiGeneralAdd = 'general.add-for-backend';
    public static $addFunctionName;

    /**
     * @var string 前置记录接口名
     */
    public static $apiGeneralFront = 'general.front-for-backend';
    public static $frontFunctionName;

    /**
     * @var array HTML 与 UBB 转换钩子容器
     */
    public static $hookUbbAndHtml;

    /**
     * @var array 价格钩子容器
     */
    public static $hookPriceNumber;

    /**
     * @var array 日期格式钩子容器
     */
    public static $hookDateSection;

    /**
     * @var array 日期格式钩子容器 (两个都记录)
     */
    public static $hookDateSectionDouble;

    /**
     * @var array 单独的业务逻辑钩子容器
     */
    public static $hookLogic;

    // 权限控制 - 只用于继承的控制器，不纳入权限控制列表
    private static $inheritControllers = [
        'MainController',
        'GeneralController',
        'DemoController'
    ];

    // 权限控制 - 手动排除
    private static $keyInheritExcept = '@auth-inherit-except';

    // 权限控制 - 允许所有人
    private static $keyPassAll = '@auth-pass-all';

    // 权限控制 - 允许指定角色 （含逗号时在该范围内，否则应比指定的权限小）
    private static $keyPassRole = '@auth-pass-role';

    // 权限控制 - 同指定的方法
    private static $keySame = '@auth-same';

    // 权限控制 - 标题样式控制
    private static $keyInfoStyle = '@auth-info-style';

    // 权限描述相关标识
    private static $varCtrl = '{ctrl}';
    private static $varInfo = '{info}';

    /**
     * @inheritdoc
     */
    public function init()
    {
        Helper::executeOnce(function () {
            parent::init();

            $this->enableCsrfValidation = true;

            Yii::info('获取用户信息');
            if (!$this->user && Yii::$app->session->has(self::USER)) {
                $this->user = (object) Yii::$app->session->get(self::USER);
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $router = $action->controller->id . '/' . $action->id;

        if (in_array($router, ['general/error'])) {
            return true;
        }

        if (in_array($router, [
            'login/index',
            'login/ajax-login'
        ])) {
            $this->mustUnLogin();
        } else if (!in_array($router, [
            'general/ajax-sms'
        ])
        ) {
            $this->mustLogin();

            if (!in_array($this->user->id, $this->getRootUsers())) {
                $auth = $this->verifyAuth($router);
                if (is_string($auth)) {
                    if (Yii::$app->request->isAjax) {
                        $this->fail($auth);
                    } else {
                        $this->error($auth, 403);
                    }
                }
            }

            // admin action log
            if (Yii::$app->request->isAjax || Yii::$app->request->isPost) {
                $log = [
                    PHP_EOL,
                    '   Url: ' . $this->currentUrl(),
                    '  User: ' . $this->user->username . ' (ID: ' . $this->user->id . ')',
                    'Params: ' . json_encode(array_merge(Yii::$app->request->get(), Yii::$app->request->post()), JSON_UNESCAPED_UNICODE)
                ];
                Yii::info(implode(PHP_EOL, $log), 'admin-action-log');
            }
        }

        return true;
    }

    /**
     * 操作权限验证
     *
     * @access protected
     *
     * @param string $router
     *
     * @return mixed
     */
    protected function verifyAuth($router)
    {
        $router = str_replace('.', '/', $router);
        $authList = $this->getAuthList(false, $this->user->role);

        $authListDec = $this->getAuthList();
        $_authListDec = [];
        foreach ($authListDec as $ctrlInfo => $item) {
            foreach ($item as $k => $actInfo) {
                $_authListDec[$k] = $ctrlInfo . ' > ' . $actInfo;
            }
        }

        if (!empty($authList[$router])) {
            return true;
        }

        if (empty($_authListDec[$router])) {
            return '[' . $router . '] 未被纳入权限控制系统内';
        }

        Yii::info('操作权限鉴定失败: ' . $router . ' (' . $_authListDec[$router] . ')');
        $info = Helper::deleteHtml('"' . $_authListDec[$router] . '" 操作权限不足');

        return $info;
    }

    /**
     * Get the auth list from php file and record
     *
     * @access protected
     *
     * @param boolean $manager
     * @param integer $userRole
     *
     * @return array
     */
    protected function getAuthList($manager = true, $userRole = null)
    {
        return $this->cache([
            'logic.auth',
            $this->user->id,
            func_get_args()
        ], function () use ($manager, $userRole) {

            $list = $this->reflectionAuthList();
            $_list = [];

            foreach ($list as $ctrl => $item) {
                // 权限列表页 => 排除 => 被继承的控制器
                if ($manager && in_array($ctrl, self::$inheritControllers)) {
                    continue;
                }

                $_items = [];
                foreach ($item['sub'] as $act) {
                    $key = $act['controller'] . '/' . $act['action'];
                    if ($manager) {
                        // 权限列表页 => 排除 => 不含以下标识的方法
                        if (!empty($act[self::$keySame]) || isset($act[self::$keyPassAll])) {
                            continue;
                        }
                        $_items[$key] = $act['info'];
                    } else {
                        unset($act['controller'], $act['action'], $act['info']);
                        $_items[$key] = empty($act) ? false : $act;
                    }
                }

                if (!empty($_items)) {
                    $_list[$item['info']] = $_items;
                }
            }

            // 非权限列表页 => 数据加工
            if (!$manager) {
                $authRecord = $this->getAuthRecord($this->user->id);
                $_list = array_merge(...(array_values($_list)));

                foreach ($_list as $router => &$item) {

                    if (!empty($authRecord[$router])) {
                        $item = true;
                        continue;
                    }

                    if (!is_array($item)) {
                        continue;
                    }

                    if (isset($item[self::$keyPassAll])) {
                        $item = true;
                    } else if (!empty($item[self::$keyPassRole])) {
                        $role = $item[self::$keyPassRole];
                        $item = is_array($role) ? in_array($userRole, $role) : $userRole <= $role;
                    }
                }

                $authSame = function ($list, $v) use ($authRecord, &$authSame) {
                    if (is_array($v) && !empty($v[self::$keySame])) {
                        $prev = $v[self::$keySame];
                        if (isset($list[$prev])) {
                            return $authSame($list, $list[$prev]);
                        } else {
                            return false;
                        }
                    }

                    return $v;
                };

                foreach ($_list as $router => &$item) {
                    $item = $authSame($_list, $item);
                }
            }

            return $_list;
        }, YEAR, null, Yii::$app->params['use_cache']);
    }

    /**
     * Get the auth list from php file
     *
     * @access protected
     *
     * @param integer
     *
     * @return array
     */
    protected function getAuthRecord($userId)
    {
        return $this->cache([
            'list.auth.record',
            func_get_args()
        ], function () use ($userId) {
            $record = $this->service(static::$apiGeneralList, [
                'table' => 'admin_auth',
                'size' => 0,
                'where' => [
                    ['user_id' => $userId],
                    ['state' => 1]
                ]
            ]);

            $_record = [];
            foreach ($record as $item) {
                $key = $item['controller'] . '/' . $item['action'];
                $_record[$key] = $item['state'];
            }

            return $_record;
        }, YEAR, null, Yii::$app->params['use_cache']);
    }

    /**
     * 获取应被纳入权限控制的操作列表
     *
     * @access private
     * @return array
     */
    private function reflectionAuthList()
    {
        $directory = Yii::getAlias('@backend') . DS . 'controllers';
        $controllers = Helper::readDirectory($directory, ['php'], 'IN');

        $list = [];

        foreach ($controllers as $ctrl) {

            // 处理文件路径
            $ctrl = Helper::cutString($ctrl, [
                '/^0^desc',
                '.^0'
            ]);

            // 获取注释
            $class = $this->controller($ctrl);
            $classDoc = Yii::$app->oil->reflection->getClassDocument($class);
            $comment = Yii::$app->oil->reflection->getMethodsDocument($class, null);

            $self = [];

            // 处理注释
            foreach ($comment as $act => $val) {

                // 排除非 http 方法
                if (!preg_match('/^(action)[A-Z]/', $act)) {
                    continue;
                }

                if (!empty($val[self::$keySame])) {
                    $val[self::$keySame] = current($val[self::$keySame]);
                }

                if (isset($val[self::$keyPassAll])) {
                    $val[self::$keyPassAll] = true;
                    $file = Helper::cutString($val['file'], [
                        '/^0^desc',
                        '.^0'
                    ]);
                    if (in_array($file, self::$inheritControllers) && !in_array($ctrl, self::$inheritControllers)) {
                        continue;
                    }
                }

                // 手动排除不需要继承的方法
                $act = preg_replace('/action/', null, $act, 1);
                $act = Helper::camelToUnder($act, '-');

                if (!empty($classDoc[self::$keyInheritExcept])) {

                    $except = implode(' ', $classDoc[self::$keyInheritExcept]);
                    $classDoc[self::$keyInheritExcept] = explode(' ', $except);

                    if (in_array($act, $classDoc[self::$keyInheritExcept])) {
                        continue;
                    }

                    if (!empty($val[self::$keySame])) {
                        $_act = explode('/', $val[self::$keySame])[1];
                        if (in_array($_act, $classDoc[self::$keyInheritExcept])) {
                            continue;
                        }
                    }
                }

                // Ajax 操作标题修饰
                if (strpos($act, 'ajax-') === 0) {
                    $style = self::$varInfo . ' (<b>Ajax</b>)';
                    if (empty($val[self::$keyInfoStyle])) {
                        $val[self::$keyInfoStyle] = [$style];
                    } else {
                        $style = str_replace(self::$varInfo, current($val[self::$keyInfoStyle]), $style);
                        $val[self::$keyInfoStyle] = [$style];
                    }
                }

                // 普通操作标题修饰
                if (!empty($val[self::$keyInfoStyle])) {
                    $val['info'] = str_replace(self::$varInfo, $val['info'], current($val[self::$keyInfoStyle]));
                }

                if (!empty($val[self::$keyPassRole])) {
                    $val[self::$keyPassRole] = current($val[self::$keyPassRole]);
                    if (!is_numeric($val[self::$keyPassRole])) {
                        $val[self::$keyPassRole] = explode(',', (string) $val[self::$keyPassRole]);
                    }
                }

                $_ctrl = str_replace('Controller', null, $ctrl);
                $_ctrl = Helper::camelToUnder($_ctrl, '-');
                if (empty($val['info'])) {
                    $val['info'] = "${_ctrl}/${act} 需规范注释";
                }

                if (!empty($val[self::$keySame])) {
                    $val[self::$keySame] = str_replace(self::$varCtrl, $_ctrl, $val[self::$keySame]);
                }

                $val = Helper::pullSome($val, [
                    self::$keySame,
                    self::$keyPassAll,
                    self::$keyPassRole,
                    'info'
                ]);

                $self[] = array_merge($val, [
                    'controller' => $_ctrl,
                    'action' => $act
                ]);
            }

            if (!empty($self)) {
                $list[$ctrl] = [
                    'info' => $classDoc['info'] ?: 'UnknownController',
                    'sub' => $self
                ];
            }
        }

        return $list;
    }

    /**
     * 设置公用参数
     *
     * @access public
     *
     * @param boolean $hideMenu
     *
     * @return void
     */
    public function setCommonParams($hideMenu = false)
    {
        Yii::$app->view->params['user_info'] = $this->user;
        $menu = $hideMenu ? [] : Yii::$app->params['menu'];

        $authList = $this->getAuthList(false, $this->user->role);
        $rootUser = $this->getRootUsers();

        foreach ($menu as $key => &$item) {

            $routers = [];
            foreach ($item['sub'] as $router => &$page) {

                list($controller, $action) = explode('.', $router);
                $_router = $controller . '/' . $action;

                if (empty($authList[$_router]) && !in_array($this->user->id, $rootUser)) {
                    unset($item['sub'][$router]);
                    if (empty($item['sub'])) {
                        unset($menu[$key]);
                        continue 2;
                    }
                    continue;
                }

                if (!in_array($controller, $routers)) {
                    $routers[] = $_router;
                }

                $page = [
                    'title' => $page,
                    'controller' => $controller,
                    'action' => $action
                ];
            }
            $item['router'] = $routers;
        }

        Yii::$app->view->params['menu'] = $menu;

        $hideMenu = isset($this->user->hide_menu) ? $this->user->hide_menu : false;
        Yii::$app->view->params['hidden_menu'] = $hideMenu;
    }

    /**
     * 获取列表页对记录的操作
     *
     * @access public
     * @return array
     */
    public static function indexOperation()
    {
        return [
            [
                'text' => '编辑',
                'value' => 'edit',
                'icon' => 'pencil'
            ]
        ];
    }

    /**
     * 表单操作页面所需的文档
     *
     * @access public
     * @return array
     */
    public function pageDocument()
    {
        return [
            'add' => [
                'title_icon' => 'plus',
                'title_info' => '新增',
                'button_info' => '新增',
                'action' => 'add-form'
            ],
            'edit' => [
                'title_icon' => 'pencil',
                'title_info' => '编辑',
                'button_info' => '编辑',
                'action' => 'edit-form'
            ]
        ];
    }

    /**
     * 从表中获取 select 的列表数据
     *
     * @access public
     *
     * @param array $item
     *
     * @return array
     */
    public function getListAboutTable($item)
    {
        $list = $this->cache($item, function () use ($item) {

            $table = $item['list_table'];
            $key = empty($item['list_key']) ? 'id' : $item['list_key'];
            $value = empty($item['list_value']) ? 'id' : $item['list_value'];

            $valueCallback = empty($item['list_value_callback']) ? null : $item['list_value_callback'];
            $callback = empty($item['list_callback']) ? null : $item['list_callback'];

            $where = empty($item['list_where']) ? [] : $item['list_where'];

            $list = $this->service(self::$apiGeneralList, [
                'table' => $table,
                'select' => [
                    $key,
                    $value
                ],
                'size' => 0,
                'where' => $where
            ]);

            $list = array_column($list, $value, $key);
            if (is_callable($valueCallback)) {
                $list = array_map($valueCallback, $list, array_keys($list));
            }

            if (is_callable($callback)) {
                $list = call_user_func($callback, $list);
            }

            if (!empty($item['list_except'])) {
                $list = $list + $item['list_except']; // + 运算，同名键左测优先
            }

            return $list;
        }, YEAR, null, Yii::$app->params['use_cache']);

        return empty($list) ? [] : $list;
    }

    /**
     * 获取 model 中的枚举属性
     *
     * @access public
     *
     * @param object $model
     * @param string $enumName
     * @param array  $default
     *
     * @throws \Exception
     * @return mixed
     */
    public function getEnumerate($model, $enumName, $default = null)
    {
        $key = '_' . $enumName;

        $except = function ($data) use ($key) {
            if (isset(static::${$key . '_except'})) {
                $data = static::${$key . '_except'} + $data;
                ksort($data);
            }

            return $data;
        };

        if (isset(static::${$key})) {
            return $except(static::${$key});
        }

        try {
            $enum = $model->$key;
        } catch (\Exception $e) {
            if (isset($default)) {
                return $except($default);
            }
            throw new \Exception('This enumeration property ' . $key . ' don\'t exist in model');
        }

        return $except($enum);
    }

    /**
     * 编辑页面所需的辅助参数
     *
     * @access public
     * @return array
     */
    public static function editAssist()
    {
        return [];
    }

    /**
     * 新增页面所需的辅助参数
     *
     * @access public
     * @return array
     */
    public static function addAssist()
    {
        return static::editAssist();
    }

    /**
     * 首页列表条件参数
     *
     * @access public
     *
     * @param string $as
     *
     * @return array
     */
    public function indexCondition($as = null)
    {
        if (!$as) {
            $model = parent::model(static::$modelName);
            $as = $model->tableName;
        }

        return [
            'order' => [
                "${as}.state DESC",
                "${as}.update_time DESC"
            ],
        ];
    }

    /**
     * 获取列表需要的辅助信息
     *
     * @access public
     *
     * @param array $assist
     *
     * @return array
     */
    public function getListAssist($assist)
    {
        $model = parent::model(static::$modelName);
        $labels = $model->attributeLabels();
        $_assist = [];

        foreach ($assist as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
                $value = null;
            }

            $value = is_array($value) ? $value : (array) $value;
            $_value = [];
            foreach ($value as $k => $v) {
                if (is_numeric($k)) {
                    $_value[$v] = true;
                } else {
                    $_value[$k] = $v;
                }
            }

            empty($_value['color']) && $_value['color'] = 'info';
            if ($_value['color'] == 'auto') {
                $_value['color'] = [
                    0 => 'danger',
                    1 => 'success'
                ];
            }

            if (empty($_value['title'])) {
                $_key = isset($_value['field']) ? $_value['field'] : $key;
                $_labels = $labels;
                if (isset($_value['table'])) {
                    $_labels = Helper::singleton('model.' . $_value['table'], function () use ($_value) {
                        return parent::model($_value['table']);
                    })->attributeLabels();
                }
                $_value['title'] = isset($_labels[$_key]) ? $_labels[$_key] : Yii::t('common', $key);
            }

            if (empty($_value['url_info'])) {
                $_value['url_info'] = 'Link';
            }

            if (empty($_value['not_set_info'])) {
                $_value['not_set_info'] = '<span class="not-set">(Nil)</span>';
            }

            if (strpos($key, 'price') !== false) {
                $_value['price'] = true;
            }

            if (isset($_value['price'])) {
                if (!is_numeric($_value['price'])) {
                    $_value['price'] = 2;
                }

                if (!isset($_value['tpl'])) {
                    $_value['tpl'] = '￥%s';
                }
            }

            if (strpos($key, 'sort') !== false && !isset($_value['tpl'])) {
                $_value['tpl'] = 'No.%s';
            }

            if (!empty($_value['list_table'])) {
                $_value['field_info'] = $this->getListAboutTable($_value);
            }

            if (!empty($_value['img'])) {
                $_value['img'] = array_merge([
                    'tip' => true,
                    'pos' => 'top',
                    'max-width' => '300px'
                ], (array) $_value['img']);
            }

            $title = Helper::popOne($_value, 'title');

            $_assist[$key] = [
                'adorn' => $_value,
                'title' => $title
            ];
        }

        return $_assist;
    }

    /**
     * 获取表单相关需要的辅助信息
     *
     * @access public
     *
     * @param array  $assist
     * @param array  $default
     * @param string $action
     *
     * @return array
     */
    public function getEditAssist($assist, &$default = [], $action = null)
    {
        $model = parent::model(static::$modelName);

        $info = $model->attributeLabels();
        $_assist = [];

        $default = $this->callMethod('sufHandleField', $default, [
            $default,
            $action
        ]);

        foreach ($assist as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
                $value = 'input';
            }

            if (is_string($value)) {
                $value = [
                    'elem' => $value
                ];
            }

            if (empty($value['elem'])) {
                $value['elem'] = 'input';
            }

            if (empty($value['name'])) {
                $value['name'] = $key;
            } else {
                $key = $value['name'];
            }

            !isset($value['title']) && $value['title'] = isset($info[$key]) ? $info[$key] : Yii::t('common', $key);

            if (!empty($value['tip'])) {
                $tip = (array) $value['tip'];
                $tipStr = null;
                foreach ($tip as $k => $v) {
                    if (is_numeric($k)) {
                        $tipStr .= $v;
                    } else {
                        $tipStr .= $k . ': ' . $v;
                    }
                    $tipStr .= '<br>';
                }
                $value['tip'] = $tipStr;
            }

            switch ($value['elem']) {
                case 'select' :
                case 'radio' :
                case 'checkbox' :
                    $this->handleSelectList($value, $model, $key, $default);
                    break;

                case 'input' :
                default:
                    empty($value['type']) && $value['type'] = 'text';

                    if ($value['type'] == 'file') {
                        $value['rules_info'] = $model->_upload_rules;
                    }

                    if (!empty($value['value_key'])) {
                        if (isset($default[$value['value_key']])) {
                            $value['value'] = $default[$value['value_key']];
                        }
                    } else if (isset($default[$key])) {
                        $value['value'] = $default[$key];
                    }

                    break;
            }

            $_assist[$key] = $value;
        }

        return $_assist;
    }

    /**
     * 获取页面的相关描述信息
     *
     * @access public
     *
     * @param string $key
     *
     * @return array
     */
    public function getPageDocument($key = null)
    {
        $document = $this->callMethod('pageDocument', []);
        if (!$key) {
            return $document;
        }

        $key = Helper::camelToUnder($key, '-');

        return isset($document[$key]) ? $document[$key] : [];
    }

    /**
     * 处理下拉框的数据
     *
     * @access private
     *
     * @param array   $value
     * @param object  $model
     * @param string  $key
     * @param array   $default
     * @param boolean $addAll
     *
     * @return void
     */
    private function handleSelectList(&$value, $model, $key, $default, $addAll = false)
    {
        $valued = isset($value['value']) ? $value['value'] : null;

        if (empty($value['list'])) {
            if (empty($value['list_table'])) {
                $list = null;
            } else {
                $list = $this->getListAboutTable($value);
            }
        } else {
            $list = (array) $value['list'];
        }
        $list = $this->getEnumerate($model, $key, $list);

        if ($addAll) {
            $list[self::SELECT_KEY_ALL] = 'All';
        }

        $value['value'] = [
            'list' => $list,
            'name' => $key,
            'selected' => Helper::issetDefault($default, $key, $valued)
        ];
    }

    /**
     * 获取过滤所需数据
     *
     * @access public
     *
     * @param array  $get
     * @param string $caller
     *
     * @return array
     */
    public function getFilter($get, $caller)
    {
        $caller .= 'Filter';
        $filter = $this->callStatic($caller);
        if (!$filter) {
            return [
                null,
                null
            ];
        }

        $model = parent::model(static::$modelName);
        $labels = $model->attributeLabels();
        $get = $this->callMethod('preHandleField', $get, [
            $get,
            $caller
        ]);

        $_filter = [];
        foreach ($filter as $key => $value) {

            if (is_numeric($key)) {
                $key = $value;
                $value = 'select';
            }

            if (is_string($value)) {
                $value = [
                    'elem' => $value
                ];
            }

            empty($value['elem']) && $value['elem'] = 'select';

            $field = isset($value['field']) ? $value['field'] : $key;
            if (empty($value['title'])) {
                $_labels = $labels;
                if (isset($value['table'])) {
                    $_labels = Helper::singleton('model.' . $value['table'], function () use ($value) {
                        return parent::model($value['table']);
                    })->attributeLabels();
                }
                $value['title'] = isset($_labels[$field]) ? $_labels[$field] : Yii::t('common', $key);
            }

            $table = isset($value['table']) ? $value['table'] : $model->tableName;
            $value['name'] = $table . '.' . $field;

            switch ($value['elem']) {
                case 'select' :
                case 'radio' :
                case 'checkbox' :
                    $this->handleSelectList($value, $model, $key, $get, true);
                    break;

                case 'input' :
                default:
                    empty($value['type']) && $value['type'] = 'text';

                    if (!empty($get[$key])) {
                        $value['value'] = htmlspecialchars_decode($get[$key]);
                    }

                    if (!empty($value['between'])) {
                        if (!empty($get[$key . '_from'])) {
                            $value['value_from'] = $get[$key . '_from'];
                        }
                        if (!empty($get[$key . '_to'])) {
                            $value['value_to'] = $get[$key . '_to'];
                        }
                    }

                    if (!empty($value['equal'])) {
                        $value['placeholder'] = '精确搜索';
                    }

                    break;
            }

            $_filter[$key] = $value;
        }

        return [
            $this->getWhereByFilter($_filter),
            $_filter
        ];
    }

    /**
     * 通过过滤条件获取 where 数组
     *
     * @access protected
     *
     * @param array $filter
     *
     * @return array
     */
    protected function getWhereByFilter($filter)
    {
        $where = [];
        foreach ($filter as $name => $item) {

            $field = $item['name'];
            switch ($item['elem']) {

                case 'select' :
                    $v = $item['value'];
                    if ($v['selected'] !== self::SELECT_KEY_ALL && isset($v['list'][$v['selected']])) {
                        $where[] = [
                            $field => $v['selected']
                        ];
                    }
                    break;

                case 'input' :
                default:

                    if (!empty($item['between']) && !empty($item['value_from']) && !empty($item['value_to'])) {
                        $where[] = [
                            'between',
                            $field,
                            $item['value_from'],
                            $item['value_to']
                        ];
                    } elseif (!empty($item['equal']) && !empty($item['value'])) {
                        $where[] = [$field => $item['value']];
                    } elseif (!empty($item['value'])) {
                        $where[] = [
                            'like',
                            $field,
                            $item['value']
                        ];
                    }
            }
        }

        return $where;
    }

    /**
     * 从 url 中获取排序数组
     *
     * @access protected
     *
     * @param array  $get
     * @param string $key
     *
     * @return array
     */
    protected function getSorterFromUrl($get, $key = 'sorter')
    {
        $default = [];
        if (!empty($get[$key])) {
            $get = urldecode($get[$key]);
            foreach (explode(',', $get) as $item) {
                $item = explode(' ', trim($item));
                $default[$item[0]] = empty($item[1]) ? null : $item[1];
            }
        }

        return $default;
    }

    /**
     * 获取排序所需数据
     *
     * @access public
     *
     * @param array  $get
     * @param string $caller
     *
     * @return array
     */
    public function getSorter($get, $caller)
    {
        $caller .= 'Sorter';
        $sorter = $this->callStatic($caller);
        if (!$sorter) {
            return [
                null,
                null
            ];
        }

        $model = parent::model(static::$modelName);

        $_sorter = $default = [];
        foreach ($sorter as $key => $value) {

            if (is_numeric($key)) {
                $key = $value;
                $value = [];
            }

            $field = isset($value['field']) ? $value['field'] : $key;
            $table = isset($value['table']) ? $value['table'] : $model->tableName;
            $value['name'] = $name = $table . '.' . $field;

            if (!empty($get[$name])) {
                $value['value'] = $get[$name];
            }

            if (!empty($value['value'])) {
                $value['value'] = strtoupper($value['value']);
                if ($value['value'] == self::ORDER_ASC || $value['value'] == self::ORDER_DESC) {
                    $default[] = "${name} ${value['value']}";
                } else {
                    unset($value['value']);
                }
            }

            $_sorter[$key] = $value;
        }

        return [
            $default,
            $_sorter
        ];
    }

    /**
     * 预处理字段数据 - 用于数据库交互
     *
     * @access public
     *
     * @param array  $record
     * @param string $action
     *
     * @return array
     */
    public function preHandleField($record, $action = null)
    {
        $record = $this->preHookDateSection($record);
        $record = $this->preHookOrderAttachment($record, $action);
        $record = $this->preHookOrderTag($record, $action);
        $record = $this->preHookDateSectionDouble($record);
        $record = $this->preHookPriceNumber($record);
        $record = $this->preHookUbbAndHtml($record);

        return $record;
    }

    /**
     * 日期转换钩子
     *
     * @access public
     *
     * @param array $record
     *
     * @return array
     */
    public function preHookDateSection($record)
    {
        $container = static::$hookDateSection ?: [];
        $container = array_merge([
            'add_time',
            'update_time'
        ], $container);

        $_container = [];
        foreach ($container as $field => $type) {
            if (is_numeric($field)) {
                $field = $type;
                $type = 'date';
            }

            $_container[$field . '_from'] = $type;
            $_container[$field . '_to'] = $type;
        }

        foreach ($_container as $field => $type) {
            if (empty($record[$field])) {
                continue;
            }
            $hour = (strpos($field, '_from') !== false ? '00:00:00' : '23:59:59');
            $date = $record[$field] . ' ' . $hour;

            switch ($type) {
                case 'stamp' :
                    $date = strtotime($date);
                    break;

                case 'date' :
                default :
                    break;
            }

            $record[$field] = $date;
        }

        return $record;
    }

    /**
     * 秩序化附件钩子
     *
     * @access public
     *
     * @param array  $record
     * @param string $action
     *
     * @return array
     * @throws \Exception
     */
    public function preHookOrderAttachment($record, $action)
    {
        if (empty($action)) {
            return $record;
        }

        // 附件秩序化
        $caller = ucfirst($action) . 'Assist';
        $assist = $this->callStatic($caller, []);

        foreach ($assist as $item) {
            if (!isset($item['type']) || $item['type'] != 'file') {
                continue;
            }

            if (empty($item['field_name'])) {
                throw new \Exception('Key field_name is required.');
            }

            $key = $item['field_name'];
            $oldKey = 'old_' . $key;

            if (isset($record[$key])) {

                $old = array_filter(explode(',', empty($record[$oldKey]) ? null : $record[$oldKey]));
                $now = array_filter(explode(',', $record[$key]));

                Helper::getDiffWithAction($old, $now, function ($add, $del) use (&$record) {
                    $_add = 'attachment_add';
                    $_del = 'attachment_del';
                    $record[$_add] = empty($record[$_add]) ? $add : Helper::joinString(',', $record[$_add], $add);
                    $record[$_del] = empty($record[$_del]) ? $del : Helper::joinString(',', $record[$_del], $del);
                });
            }
            unset($record[$oldKey]);
        }

        return $record;
    }

    /**
     * 秩序化标签钩子
     *
     * @access public
     *
     * @param array  $record
     * @param string $action
     *
     * @return array
     * @throws \Exception
     */
    public function preHookOrderTag($record, $action)
    {
        if (empty($action)) {
            return $record;
        }

        // 附件秩序化
        $assist = $this->callStatic($action . 'Assist', []);

        foreach ($assist as $item) {

            if (!isset($item['elem']) || $item['elem'] != 'tag') {
                continue;
            }

            if (empty($item['field_name'])) {
                throw new \Exception('Key `field_name` is required.');
            }

            if (empty($item['table'])) {
                throw new \Exception('Key `table` is required.');
            }

            if (empty($item['foreign_key'])) {
                throw new \Exception('Key `foreign_key` is required.');
            }

            $key = $item['field_name'];
            $oldKey = 'old_' . $key;
            $newKey = 'new_' . $key;

            if (isset($record[$key])) {

                if (!isset($record['tags_record'])) {
                    $record['tags_record'] = [];
                }

                $old = array_filter(explode(',', empty($record[$oldKey]) ? null : $record[$oldKey]));
                $now = array_filter(explode(',', $record[$key]));

                $tagRecord = [];
                Helper::getDiffWithAction($old, $now, function ($add, $del) use (&$tagRecord) {
                    $tagRecord['del'] = $del;
                });

                $tagRecord['db'] = empty($item['db']) ? DB_KAKE : $item['db'];
                $tagRecord['table'] = $item['table'];
                $tagRecord['foreign_key'] = $item['foreign_key'];

                if (isset($item['handler_controller'])) {
                    $controller = $item['handler_controller'];
                } else {
                    $controller = str_replace('_', '-', $item['table']);
                    $controller = $this->controller($controller, Yii::$app->id, false);
                }

                if (empty($record[$newKey])) {
                    $tagRecord['add'] = [];
                } else {
                    foreach ($record[$newKey] as $query) {
                        parse_str($query, $tagData);
                        $tagData = $this->callMethod('preHandleField', $tagData, [
                            $tagData,
                            $action
                        ], $controller);
                        $tagRecord['add'][] = $tagData;
                    }
                }
                $record['tags_record'][$key] = $tagRecord;
            }
            unset($record[$key], $record[$oldKey], $record[$newKey]);
        }

        return $record;
    }

    /**
     * 日期转换钩子
     *
     * @access public
     *
     * @param array $record
     *
     * @return array
     */
    public function preHookDateSectionDouble($record)
    {
        $container = static::$hookDateSectionDouble;
        if (empty($container)) {
            return $record;
        }

        foreach ($container as $field => $type) {
            if (is_numeric($field)) {
                $field = $type;
                $type = 'date';
            }

            $_from = empty($field) ? 'from' : $field . '_from';
            $_to = empty($field) ? 'to' : $field . '_to';

            if (!empty($record[$_from]) || !empty($record[$_to])) {

                $from = $record[$_from];
                $to = $record[$_to];

                switch ($type) {
                    case 'date' :
                        $from = strtotime($from);
                        $to = strtotime($to);
                        break;

                    case 'stamp' :
                    default :
                        break;
                }

                if ($from === false || $to === false || $from >= $to) {
                    unset($record[$_from], $record[$_to]);
                } else {
                    $record[$_from] = date('Y-m-d H:i:s', $from);
                    $record[$_to] = date('Y-m-d H:i:s', $to);
                }
            }
        }

        return $record;
    }

    /**
     * 金钱数值钩子
     *
     * @access public
     *
     * @param array   $record
     * @param integer $multiple
     *
     * @return array
     */
    public function preHookPriceNumber($record, $multiple = 100)
    {
        $container = static::$hookPriceNumber;
        if (empty($container)) {
            return $record;
        }

        foreach ($container as $hook) {
            !empty($record[$hook]) && $record[$hook] = intval($record[$hook] * $multiple);
        }

        return $record;
    }

    /**
     * UBB&HTML钩子
     *
     * @access public
     *
     * @param array $record
     *
     * @return array
     */
    public function preHookUbbAndHtml($record)
    {
        $container = static::$hookUbbAndHtml;
        if (empty($container)) {
            return $record;
        }

        foreach ($container as $hook) {
            if (empty($record[$hook])) {
                continue;
            }
            $record[$hook] = Yii::$app->oil->ubb->htmlToUbb($record[$hook], true);
        }

        return $record;
    }

    /**
     * 逻辑钩子
     *
     * @access public
     *
     * @param array $where
     *
     * @return array
     */
    public function preHookLogicForWhere($where)
    {
        $container = static::$hookLogic;
        if (empty($container) || empty($where)) {
            return $where;
        }

        $_container = [];
        foreach ($container as $key => $tag) {
            if (is_numeric($key)) {
                $_container[$tag] = 'id';
            } else {
                $_container[$key] = $tag;
            }
        }

        $_where = [];
        foreach ($where as $key => $item) {
            $_key = key($item);
            if (strpos($_key, '.')) {
                $_key = explode('.', $_key)[1];
            }
            if (!isset($_container[$_key])) {
                $_where[$key] = $item;
                continue;
            }
            $logicHandler = Helper::underToCamel($_key) . 'ReverseWhereLogic';
            $_where = array_merge($_where, $this->callStatic($logicHandler, [], [current($item)]));
        }

        return $_where;
    }

    /**
     * 后处理字段数据 - 用于用户交互
     *
     * @access public
     *
     * @param array    $record
     * @param string   $action
     * @param callable $callback
     *
     * @return array
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        $record = $this->sufHookLogic($record, $action);

        if (is_callable($callback)) {
            $record = call_user_func($callback, $record, $action);
        }

        $record = $this->sufHookDateSection($record);
        $record = $this->sufHookDateSectionDouble($record, $action);
        $record = $this->sufHookPriceNumber($record);
        $record = $this->sufHookUbbAndHtml($record);

        return $record;
    }

    /**
     * 日期转换钩子
     *
     * @access public
     *
     * @param array $record
     *
     * @return array
     */
    public function sufHookDateSection($record)
    {
        $container = static::$hookDateSection ?: [];
        $container = array_merge([
            'add_time',
            'update_time'
        ], $container);

        foreach ($container as $field => $type) {
            if (empty($record[$field])) {
                continue;
            }

            switch ($type) {
                case 'stamp' :
                    $date = date('Y-m-d H:i:s', $record[$field]);
                    if (strpos($record[$field], '.') !== false) {
                        $date .= ':' . explode('.', $record[$field])[1];
                    }
                    $record[$field] = $date;
                    break;

                case 'date' :
                default :
                    break;
            }
        }

        return $record;
    }

    /**
     * 日期转换钩子
     *
     * @access public
     *
     * @param array  $record
     * @param string $action
     *
     * @return array
     */
    public function sufHookDateSectionDouble($record, $action)
    {
        if ($action != 'edit') {
            return $record;
        }

        $container = static::$hookDateSectionDouble;
        if (empty($container)) {
            return $record;
        }

        foreach ($container as $field => $type) {
            if (is_numeric($field)) {
                $field = $type;
                $type = 'date';
            }

            $_from = empty($field) ? 'from' : $field . '_from';
            $_to = empty($field) ? 'to' : $field . '_to';

            if (!empty($record[$_from]) && !empty($record[$_to])) {

                $from = $record[$_from];
                $to = $record[$_to];

                switch ($type) {
                    case 'date' :
                        $from = strtotime($from);
                        $to = strtotime($to);
                        break;

                    case 'stamp' :
                    default :
                        break;
                }

                $record[$_from] = date('Y-m-d\TH:i', $from);
                $record[$_to] = date('Y-m-d\TH:i', $to);
            }
        }

        return $record;
    }

    /**
     * 金钱数值钩子
     *
     * @access public
     *
     * @param array   $record
     * @param integer $multiple
     *
     * @return array
     */
    public function sufHookPriceNumber($record, $multiple = 100)
    {
        $container = static::$hookPriceNumber;
        if (empty($container)) {
            return $record;
        }

        foreach ($container as $hook) {
            !empty($record[$hook]) && $record[$hook] = $record[$hook] / $multiple;
        }

        return $record;
    }

    /**
     * UBB&HTML钩子
     *
     * @access public
     *
     * @param array $record
     *
     * @return array
     */
    public function sufHookUbbAndHtml($record)
    {
        $container = static::$hookUbbAndHtml;
        if (empty($container)) {
            return $record;
        }

        foreach ($container as $hook) {
            if (empty($record[$hook])) {
                continue;
            }
            $record[$hook] = Yii::$app->oil->ubb->ubbToHtml($record[$hook]);
        }

        return $record;
    }

    /**
     * 逻辑钩子
     *
     * @access public
     *
     * @param array  $record
     * @param string $action
     *
     * @return array
     */
    public function sufHookLogic($record, $action)
    {
        if (strpos($action, 'Filter') !== false) {
            return $record;
        }

        $container = static::$hookLogic;
        if (empty($container)) {
            return $record;
        }

        foreach ($container as $key => $tag) {
            if (is_numeric($key)) {
                $key = $tag;
                $tag = 'id';
            }

            if (!isset($record[$tag])) {
                return $record;
            }

            $logicHandler = Helper::underToCamel($key) . 'Logic';

            $record[$key] = $this->callStatic($logicHandler, null, [$record]);
            $record = $this->getFieldInfo($record, $key);
        }

        return $record;
    }

    /**
     * 自定义展示列表页
     *
     * @access public
     *
     * @param array   $list
     * @param string  $caller
     * @param mixed   $page
     * @param array   $sorter
     * @param array   $filter
     * @param boolean $returnList
     * @param array   $get
     * @param boolean $handle
     *
     * @return mixed
     */
    public function showListDiy($list, $caller = null, $page = false, $sorter = null, $filter = null, $returnList = false, $get = [], $handle = true)
    {
        $caller = $caller ?: $this->getCaller(2);

        if ($page === false) {
            $page = new yii\data\Pagination(['totalCount' => count($list)]);
        }

        if (is_null($sorter)) {
            list($sorterDefault, $sorter) = $this->getSorter($get, $caller);
        }

        if (is_null($filter)) {
            list($filterDefault, $filter) = $this->getFilter($this->getSorterFromUrl($get), $caller);
        }

        $assist = $this->getListAssist($this->callStatic($caller . 'Assist', []));

        if (!empty($list) && $handle) {
            $list = $this->callMethod('sufHandleListBeforeField', $list, [
                $list,
                $caller
            ]);

            array_walk($list, function (&$value) use ($caller) {
                $value = $this->callMethod('sufHandleField', $value, [
                    $value,
                    $caller
                ]);
            });

            $list = $this->callMethod('sufHandleListAfterField', $list, [
                $list,
                $caller
            ]);
        }

        if ($returnList) {
            return [
                $list,
                $page
            ];
        }

        // 是否为模态框
        $modal = strpos($caller, 'ajaxModal') !== false;

        // 宏操作与单记录操作
        $operationForm = $this->callStatic($caller . 'OperationForm');
        $operation = $this->callStatic($caller . 'Operation');
        $operations = $this->callStatic($caller . 'Operations');

        $isset = function ($var, $default = null) {
            if (isset(static::${$var})) {
                return static::${$var};
            }

            return $default;
        };

        // 针对记录展示单选框/复选框/无
        $recordFilter = $isset($caller . 'RecordFilter', $modal ? 'radio' : false);
        $recordFilter = in_array($recordFilter, [
            'checkbox',
            'radio'
        ]) ? $recordFilter : false;

        $recordFilterName = $isset($caller . 'RecordFilterName', $recordFilter);

        $recordFilterValueName = $isset($caller . 'RecordFilterValueName', 'id');

        // 是否 ajax 分页、ajax 筛选
        $ajaxPage = $ajaxFilter = $modal;

        // 宏操作显示的方位
        $operationsPosition = $isset($caller . 'OperationsPosition', $modal ? 'bottom' : false);
        $operationsPosition = in_array($operationsPosition, [
            'top',
            'bottom'
        ]) ? $operationsPosition : 'top';

        $params = [
            'page',
            'list',
            'assist',
            'filter',
            'sorter',
            'operationForm',
            'operation',
            'operations',
            'recordFilter',
            'recordFilterName',
            'recordFilterValueName',
            'ajaxPage',
            'ajaxFilter',
            'operationsPosition'
        ];

        return $this->display('//general/list', compact(...$params));
    }

    /**
     * 展示列表页
     *
     * @access public
     *
     * @param string  $caller
     * @param boolean $returnList
     * @param boolean $logReference
     * @param array   $defCondition
     * @param boolean $handle
     *
     * @return mixed
     */
    public function showList($caller = null, $returnList = false, $logReference = true, $defCondition = [], $handle = true)
    {
        $caller = $caller ?: $this->getCaller(2);
        if ($logReference) {
            $this->logReference($this->getControllerName($caller));
        }

        $condition = $this->callMethod($caller . 'Condition', []);
        if (!isset($condition['size'])) {
            $condition['size'] = Yii::$app->params['pagenum'];
        }

        $get = Yii::$app->request->get();

        list($filterDefault, $filter) = $this->getFilter($get, $caller);
        list($sorterDefault, $sorter) = $this->getSorter($this->getSorterFromUrl($get), $caller);

        if (!empty(static::$listFunctionName)) {
            $result = $this->callMethod(static::$listFunctionName, 'function non-exists');
        } else {
            $filterDefault = $this->preHookLogicForWhere($filterDefault);
            if (!Helper::arrayEmpty($filterDefault)) {
                $_where = empty($condition['where']) ? [] : $condition['where'];
                $condition['where'] = array_merge($_where, (array) $filterDefault);
            }

            if (!empty($sorterDefault)) {
                $condition['order'] = $sorterDefault;
            }

            $model = parent::model(static::$modelName);
            $params = [
                'table' => $model->tableName,
                'db' => static::$modelDb
            ];

            $params = array_merge($params, $condition, $get);
            $defCondition = (array) $defCondition;
            foreach ($defCondition as $key => &$value) {
                if (isset($params[$key]) && is_callable($value)) {
                    $value = call_user_func($value, $params[$key]);
                }
            }
            $params = array_merge($params, $defCondition);

            $result = $this->service(static::$apiGeneralList, $params);
        }
        if (is_string($result)) {
            $this->error(Yii::t('common', $result));
        }

        // 分页
        if (isset($result[1]['pageParam'])) {
            list($list, $page) = $result;
            $pagination = new yii\data\Pagination(['totalCount' => $page['totalCount']]);
            $pagination->setPageSize($condition['size']);
            $page = $pagination;
        } else {
            $list = $result;
            $page = null;
        }

        return $this->showListDiy($list, $caller, $page, $sorter, $filter, $returnList, $get, $handle);
    }

    /**
     * 导出列表数据
     *
     * @access public
     *
     * @param string $filename
     *
     * @return mixed
     */
    public function exportList($filename)
    {
        $caller = str_replace('Export', null, $this->getCaller(2));
        list($list) = $this->showList($caller, true, false, ['size' => 0]);

        if (empty($list)) {
            $this->goReference($this->getControllerName($caller), [
                'danger' => '筛选后没有数据可供导出'
            ]);
        }

        $assist = $this->callStatic($caller . 'ExportAssist', []);
        $labels = parent::model(static::$modelName)->attributeLabels();

        $_assist = [];
        foreach ($assist as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
                $title = isset($labels[$key]) ? $labels[$key] : Yii::t('common', $key);
            } else {
                $title = $value;
            }
            $_assist[$key] = $title;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $_list = [
            array_values($_assist),
            []
        ];
        foreach ($list as $item) {
            $_list[] = array_values(Helper::pullSome($item, array_keys($_assist), true));
        }

        $sheet->fromArray($_list);
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        return null;
    }

    /**
     * 预览列表
     */
    public function actionIndex()
    {
        return $this->showList();
    }

    /**
     * 展示空表单
     *
     * @access public
     *
     * @param string $caller
     *
     * @return mixed
     */
    public function showForm($caller = null)
    {
        $caller = $caller ?: $this->getCaller(2);
        $this->logReference($this->getControllerName($caller));

        $modelInfo = static::$modelInfo;

        $assist = $this->callStatic($caller . 'Assist', []);
        $default = [];
        $list = $this->getEditAssist($assist, $default, $caller);
        $view = $this->getPageDocument($caller);

        $operation = $this->callStatic($caller . 'Operation');
        $initScript = $this->callMethod($caller . 'InitScript', []);

        $params = [
            'list',
            'modelInfo',
            'view',
            'operation',
            'initScript'
        ];

        return $this->display('//general/action', compact(...$params));
    }

    /**
     * 新增
     */
    public function actionAdd()
    {
        return $this->showForm();
    }

    /**
     * 新增动作
     * @auth-same {ctrl}/add
     *
     * @param array  $reference
     * @param string $action
     * @param array  $post
     * @param string $caller
     */
    public function actionAddForm($reference = null, $action = 'add', $post = null, $caller = null)
    {
        if (is_string($reference)) {
            $reference = [
                'fail' => $reference,
                'success' => $reference
            ];
        }
        $modelInfo = static::$modelInfo;

        if (!$action) {
            $action = str_replace('Form', '', $caller ?: $this->getCaller(2));
            $action = Helper::camelToUnder($action, '-');
        }

        $post = $post ?: Yii::$app->request->post();

        if (!empty(static::$addFunctionName)) {
            $result = $this->callMethod(static::$addFunctionName, 'function non-exists');
        } else {
            $model = parent::model(static::$modelName);

            $params = array_merge(['table' => $model->tableName], $post);
            $params = $this->callMethod('preHandleField', [], [
                $params,
                $action
            ]);
            $result = $this->service(static::$apiGeneralAdd, $params);
        }

        $appendMessage = $this->callMethod('messageHandle', null, [$result]);
        if (is_string($result)) {
            $this->goReference($reference ? $reference['fail'] : $this->getControllerName($action), [
                'danger' => Yii::t('common', $result) . $appendMessage,
                'list' => $post
            ]);
        }

        $this->goReference($reference ? $reference['success'] : $this->getControllerName('index'), [
            'success' => '新增' . $modelInfo . '成功' . $appendMessage
        ]);
    }

    /**
     * 展示指定记录表单
     *
     * @access public
     *
     * @param array   $where
     * @param string  $caller
     * @param boolean $returnRecord
     * @param boolean $logReference
     *
     * @return mixed
     */
    public function showFormWithRecord($where = [], $caller = null, $returnRecord = false, $logReference = true)
    {
        $caller = $caller ?: $this->getCaller(2);
        if ($logReference) {
            $this->logReference($this->getControllerName($caller));
        }

        if (!empty(static::$getFunctionName)) {
            $result = $this->callMethod(static::$getFunctionName, 'function non-exists');
        } else {
            $id = Yii::$app->request->get('id');
            $condition = $this->callMethod($caller . 'Condition', []);

            $model = parent::model(static::$modelName);
            $condition['table'] = $model->tableName;
            if (!$where) {
                $where = [[$model->tableName . '.id' => $id]];
            } else if (count($where) == count($where, 1)) { // 一维转二维
                $where = [$where];
            }
            $_where = empty($condition['where']) ? [] : $condition['where'];
            $condition['where'] = array_merge($_where, $where);

            $result = $this->service(static::$apiGeneralGet, $condition);
        }

        if (is_string($result)) {
            $this->error(Yii::t('common', $result));
        }

        $modelInfo = static::$modelInfo;
        $assist = $this->callStatic($caller . 'Assist', []);
        $list = $this->getEditAssist($assist, $result, $caller);

        if ($returnRecord) {
            return $result;
        }

        $view = $this->getPageDocument($caller);

        // 单记录操作
        $operation = $this->callStatic($caller . 'Operation');
        $initScript = $this->callMethod($caller . 'InitScript', []);

        $params = [
            'id',
            'list',
            'result',
            'modelInfo',
            'view',
            'operation',
            'initScript'
        ];

        return $this->display('//general/action', compact(...$params));
    }

    /**
     * 编辑
     */
    public function actionEdit()
    {
        return $this->showFormWithRecord();
    }

    /**
     * 编辑动作
     * @auth-same {ctrl}/edit
     *
     * @param array  $reference
     * @param string $action
     * @param array  $post
     * @param string $caller
     */
    public function actionEditForm($reference = null, $action = 'edit', $post = null, $caller = null)
    {
        if (is_string($reference)) {
            $reference = [
                'fail' => $reference,
                'success' => $reference
            ];
        }
        $modelInfo = static::$modelInfo;

        if (!$action) {
            $action = str_replace('Form', '', $caller ?: $this->getCaller(2));
            $action = Helper::camelToUnder($action, '-');
        }

        $post = $post ?: Yii::$app->request->post();
        if (empty($post['id'])) {
            $this->error('主键参数 ID 必须');
        }

        if (!empty(static::$updateFunctionName)) {
            $result = $this->callMethod(static::$updateFunctionName, 'function non-exists');
        } else {
            $model = parent::model(static::$modelName);
            $params = array_merge([
                'table' => $model->tableName,
                'where' => [$model->tableName . '.id' => $post['id']]
            ], $post);
            $params = $this->callMethod('preHandleField', [], [
                $params,
                $action
            ]);
            $result = $this->service(static::$apiGeneralUpdate, $params);
        }

        $appendMessage = $this->callMethod('messageHandle', null, [$result]);
        if (is_string($result)) {
            $this->goReference($reference ? $reference['fail'] : $this->getControllerName($action), [
                'danger' => Yii::t('common', $result) . $appendMessage,
                'list' => $post
            ]);
        }

        $this->goReference($reference ? $reference['success'] : $this->getControllerName('index'), [
            'success' => '更新' . $modelInfo . '成功' . $appendMessage
        ]);
    }

    /**
     * 记录前置
     *
     * @param string $reference
     */
    public function actionFront($reference = null)
    {
        if (!empty(static::$frontFunctionName)) {
            $result = $this->callMethod(static::$frontFunctionName, 'function non-exists');
        } else {
            $model = parent::model(static::$modelName);
            $result = $this->service(static::$apiGeneralFront, [
                'table' => $model->tableName,
                'id' => Yii::$app->request->get('id')
            ]);
        }

        if (is_string($result)) {
            $flash['danger'] = Yii::t('common', $result);
        } else {
            $flash['success'] = '记录前置成功';
        }

        $this->goReference($reference ?: $this->getControllerName('index'), $flash);
    }

    /**
     * 记录排序
     */
    public function actionSort()
    {
        $sort = Yii::$app->request->post('sort');
        if (empty(trim($sort))) {
            $sort = '';
        }

        $model = parent::model(static::$modelName);
        $result = $this->service(self::$apiGeneralUpdate, [
            'table' => $model->tableName,
            'where' => [
                'id' => Yii::$app->request->post('id')
            ],
            'sort' => $sort
        ]);

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(null, 'success');
    }

    /**
     * 裁切图片
     *
     * @auth-pass-all
     */
    public function actionAjaxModalCrop()
    {
        $options = Yii::$app->request->post();
        $view['title_info'] = '图片选区';

        return $this->display('general/crop', compact('options', 'view'));
    }

    /**
     * 保存裁切的图片
     *
     * @auth-pass-all
     */
    public function actionAjaxSaveCrop()
    {
        $base64 = Yii::$app->request->post('base64');
        $url = Yii::$app->request->post('url');

        $filename = str_replace(Yii::$app->params['upload_url'], null, $url);
        $file = Yii::$app->params['tmp_path'] . $filename;

        Helper::base64ToImage($base64, $file);

        $result = Yii::$app->oil->oss->upload($file);
        if (is_string($result)) {
            @unlink($file);
            $this->fail($result);
        }

        $this->success();
    }

    /**
     * 显示/隐藏菜单
     *
     * @auth-pass-all
     */
    public function actionAjaxHideMenu()
    {
        $hide = Yii::$app->request->get('hide');

        $user = Yii::$app->session->get(static::USER);
        $user['hide_menu'] = !!$hide;
        Yii::$app->session->set(static::USER, $user);

        $this->success();
    }

    /**
     * 必须登录的操作前置判断
     *
     * @access public
     * @return mixed
     */
    public function mustLogin()
    {
        if ($this->user) {
            return true;
        }

        if (Yii::$app->request->isAjax) {
            $this->fail('login first');
        } else {
            $url = Helper::unsetParamsForUrl('callback', $this->currentUrl());
            header('Location: ' . Url::toRoute([
                    '/login/index',
                    'callback' => $url
                ]));
            exit();
        }

        return false;
    }

    /**
     * 必须非登录的操作前置判断
     *
     * @access public
     * @return mixed
     */
    public function mustUnLogin()
    {
        if (!$this->user) {
            return true;
        }

        if (Yii::$app->request->isAjax) {
            $this->fail('already login');
        } else {
            $this->error(Yii::t('common', 'already login'));
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function error($message, $code = null, $trace = null)
    {
        $this->sourceCss = false;
        $this->setCommonParams(true);
        parent::error($message, $code, $trace);
    }

    /**
     * 渲染模态框
     *
     * @access public
     *
     * @param string $view
     * @param array  $params
     * @param string $title
     *
     * @return bool
     */
    public function modal($view, $params = [], $title = null)
    {
        $tpl = Yii::$app->getViewPath() . DS . ltrim($view, '/') . '.php';
        $content = $this->renderFile($tpl, $params);

        $response = ['message' => $content];
        $title && $response['title'] = $title;

        $this->success($response);

        return true;
    }

    /**
     * 渲染页面
     *
     * @access public
     *
     * @param string $view
     * @param array  $params
     *
     * @return bool|string
     */
    public function display($view, $params = [])
    {
        $this->setCommonParams();

        $fetchTpl = false;
        foreach (Helper::functionCallTrance('all') as $index => $fn) {
            if (strpos($fn, 'actionAjaxModal') === 0) {
                $fetchTpl = $index;
                break;
            }
        }

        if ($fetchTpl) {
            if (empty($params['view'])) {
                $titleKey = $this->getCaller($fetchTpl) . 'Title';
                $title = isset(static::${$titleKey}) ? static::${$titleKey} : null;
            } else {
                $title = empty($params['view']['title_info']) ? null : $params['view']['title_info'];
            }

            return $this->modal($view, $params, $title);
        }

        return $this->render($view, $params);
    }
}