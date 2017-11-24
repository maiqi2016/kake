<?php

namespace frontend\controllers;

use common\components\Helper;
use EasyWeChat\Message\Image as Img;
use EasyWeChat\Message\Text;
use Yii;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * WeChat reply controller
 */
class WeChatController extends GeneralController
{
    /**
     * @var object
     */
    public $api = null;

    /**
     * @var string
     */
    public $n = PHP_EOL;

    /**
     * @var string
     */
    private $staff = 'kf2002@KAKE_Hotel';

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['reply'])) {
            $action->controller->enableCsrfValidation = false;
        }

        !$this->api && $this->api = Yii::$app->wx;

        return parent::beforeAction($action);
    }

    /**
     * 监听消息
     */
    public function actionReply()
    {
        if (!Yii::$app->request->get('signature')) {
            return null;
        }

        $this->api->listen([
            'text' => function ($message) {
                $message->Content = trim($message->Content);

                $user = $this->api->user->get($message->FromUserName);
                $user->nickname = Helper::filterEmjoy($user->nickname);

                if (preg_match('/^[\d\w]{8}$/i', $message->Content)) {
                    return $this->replyOnlyCode($message, $user);
                }

                return $this->replyCompanyAndProfile($message, $user);
            },

            'event_subscribe' => function ($message) {
                $name = $message->EventKey ?: '官方推广';
                $groupId = $this->api->group($name);
                $this->api->user_group->moveUser($message->FromUserName, $groupId);
            },

            'event_scan' => function ($message) {
                return '🙄扫码来源：' . $message->EventKey;
            }
        ]);
    }

    /**
     * 回复抽奖码
     *
     * @access  private
     * @example xS13hL6s
     *
     * @param object $message
     * @param array  $user
     *
     * @return string
     */
    private function replyOnlyCode($message, $user)
    {
        $result = $this->service('activity.log-winning-code', [
            'code' => $message->Content,
            'openid' => $user->openid,
            'nickname' => $user->nickname
        ]);

        if (is_string($result)) {
            return "Oops! An error has occurred.{$this->n}{$this->n}${result}";
        }

        if (!empty($result['error'])) {
            switch ($result['error']) {
                case 'user_already_receive':
                    if ($result['winning']) {
                        return '真的中奖了🙄🙄🙄！记得留下可联系到你的手机号码+姓名哦~';
                    } else {
                        return '真的没中呀😭~关注喀客喀客旅行，福利多多，再接再厉！';
                    }
                    break;

                case 'code_error':
                    return '这个抽奖码不正确😌，请核对哟~';
                    break;

                case 'code_already_received':
                    return '这个抽奖码已经被别的小姐姐核领了🙄，如果你确认输入无误，请联系KAKE解决~';
                    break;

                default :
                    return 'Unknown error.';
                    break;
            }
        }

        if ($result['winning']) {
            return '恭喜你中奖了👻👏🍾🎉 喀客客服将随后与你联系，请留下你的手机号码+姓名，并保持畅通~';
        }

        return '很遗憾这次你没有中奖🙄🙄🙄，关注喀客旅行，下次继续，再接再厉！';
    }

    /**
     * 回复合作公司简称或并追加个人信息
     *
     * @access  private
     * @example 喀客+Leon+15021275672 Or 喀客
     *
     * @param object $message
     * @param array  $user
     *
     * @return mixed
     */
    private function replyCompanyAndProfile($message, $user)
    {
        $model = parent::model('ActivityLotteryCode');

        $companyOnly = Helper::pullSome($model->_company, $model->_company_only);
        $companyOnly = array_map('strtolower', $companyOnly);

        if (in_array(strtolower($message->Content), $companyOnly)) {
            $company = $message->Content;
            $name = null;
            $phone = null;
        } else {
            $text = str_replace('＋', '+', $message->Content);
            $char = substr_count($text, '+');
            if ($char < 2) {
                return null;
            }

            list($company, $name, $phone) = explode('+', $text);

            // 名字/手机号码验证
            if (empty($name) || empty($phone)) {
                return '名字和手机号码用于中奖联络方式，请规范填写哦~';
            }
        }

        // 公司代码验证
        $company = strtolower($company);
        if (false === ($code = array_search($company, $model->_company))) {
            return '该品牌还不是喀客旅行的合作伙伴~';
        }

        if ($code < $model->_max_activity_id) {
            return '哎呀，你来晚了！抽奖活动已经结束了！';
        }

        // 时间判断
        if (isset($model->_activity_date[$code])) {

            $date = $model->_activity_date[$code];

            if (isset($date['begin']) && TIME < strtotime($date['begin'])) {
                return "抽奖活动还未开始，不要太心急哦~开始时间：${date['begin']}~ 爱你么么哒";
            }
            if (isset($date['end']) && TIME > strtotime($date['end'])) {
                return '哎呀，你来晚了！抽奖活动已经结束了！';
            }
        }

        $result = $this->service('activity.log-lottery-code', [
            'openid' => $user->openid,
            'nickname' => $user->nickname,
            'company' => $code,
            'real_name' => $name,
            'phone' => $phone
        ]);
        if (is_string($result)) {
            return "Oops! An error has occurred.{$this->n}{$this->n}${result}";
        }

        // 已参与判断
        if (!empty($result['exists'])) {
            return "你已经参与过啦~{$this->n}抽奖码：${result['code']}，祝你好运~";
        }

        $msg = base64_encode("您的抽奖码是：${result['code']}，请妥善保管");
        $url = Yii::$app->params['frontend_url'] . '?popup=lottery-code&msg=' . $msg;

        return "抽奖码生成成功，<a href='{$url}'>点击这里查看</a>";
    }

    /**
     * 客服回复文字
     *
     * @access private
     *
     * @param string $text
     * @param object $message
     *
     * @return void
     */
    private function staffReplyText($text, $message)
    {
        $text = new Text(['content' => $text]);
        $this->api->staff->message($text)->by($this->staff)->to($message->FromUserName)->send();
    }

    /**
     * 客服回复图片
     *
     * @access private
     *
     * @param string $imgPath
     * @param object $message
     *
     * @return void
     */
    private function staffReplyImg($imgPath, $message)
    {
        $result = $this->api->material_temporary->uploadImage($imgPath);
        $img = new Img(['media_id' => $result->media_id]);
        $this->api->staff->message($img)->by($this->staff)->to($message->FromUserName)->send();
    }

    /**
     * 生成抽奖码图片
     *
     * @access private
     *
     * @param string $company
     * @param string $code
     *
     * @return string
     */
    private function drawLotteryImg($company, $code)
    {
        $bg = self::getPathByUrl('img/activity/lottery-bg.jpg', 'frontend_source');
        $img = Image::make($bg);

        $fonts = self::getPathByUrl('fonts/hanyi.ttf', 'frontend_source');

        // 添加文本
        $text = function ($text, $size, $y, $fonts, $width = 750) use ($img) {

            list($w) = Helper::textPx($text, $fonts, $size, 0.78);
            $x = ($width - $w) / 2;

            $img->text($text, $x, $y, function ($font) use ($fonts, $size) {
                $font->file($fonts);
                $font->size($size);
            });
        };

        // 打印公司名称
        $text($company, 38, 320, $fonts);

        // 打印抽奖码
        $text($code, 32, 834, $fonts);

        $tmp = Yii::$app->params['tmp_path'] . '/' . $code . '.jpg';
        $img->save($tmp);

        return $tmp;
    }
}
