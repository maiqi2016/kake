<?php

namespace backend\controllers;

use Oil\src\Helper;
use Yii;

/**
 * 计划任务管理
 *
 * @auth-inherit-except index add edit front sort
 */
class MissionController extends GeneralController
{
    /**
     * 缓存任务列表
     */
    public function actionCache()
    {
        return $this->display('cache');
    }

    /**
     * 一键清除缓存
     */
    public function actionAjaxClearAllCache()
    {
        $info = null;

        $info .= Yii::$app->cache->flush() ? '后台缓存清除成功' : '后台缓存清除失败';

        $info .= '<br>';
        $result = $this->api('frontend', 'general.clear-cache');
        $info .= ($result['state'] < 1) ? ('前台缓存清除失败: ' . $result['info']) : '前台缓存清除成功';

        $info .= '<br>';
        Helper::removeDirectory(Yii::getAlias('@frontend/runtime/static'), false);
        $info .= '清除前台静态文件成功';

        if (in_array($this->user->id, $this->getRootUsers())) {
            $info .= '<br>';
            $result = $this->service('general.clear-cache');
            $info .= is_string($result) ? ('服务缓存清除失败: ' . $result) : '服务缓存清除成功';
        }

        $this->success(null, $info);
    }

    /**
     * 清空后台缓存
     */
    public function actionAjaxClearBackendCache()
    {
        Yii::$app->cache->flush();
        $this->success(null, '缓存清除成功');
    }

    /**
     * 清空前台缓存
     */
    public function actionAjaxClearFrontendCache()
    {
        $result = $this->api('frontend', 'site.clear-cache');
        if ($result['state'] < 1) {
            $this->fail('缓存清除失败: ' . $result['info']);
        }

        $this->success(null, '缓存清除成功');
    }

    /**
     * 清空服务缓存
     */
    public function actionAjaxClearServiceCache()
    {
        $result = $this->service('general.clear-cache');
        if (is_string($result)) {
            $this->fail('缓存清除失败: ' . $result);
        }

        $this->success(null, '缓存清除成功');
    }
}
