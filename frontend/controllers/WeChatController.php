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
     * @var string
     */
    public $staff = 'kf2002@KAKE_Hotel';

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['reply'])) {
            $action->controller->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * 监听消息
     */
    public function actionReply()
    {
        $wx = Yii::$app->wx;

        if (Yii::$app->request->get('signature')) {
            $wx->listen([
                'text' => function ($message) use ($wx) {
                    return $this->replyTextLottery($message, $wx);
                },

                'event_subscribe' => function ($message) use ($wx) {
                    $name = $message->EventKey ? str_replace('qrscene_', '', $message->EventKey) : '官方推广';
                    $groupId = $wx->group($name);
                    $wx->user_group->moveUser($message->FromUserName, $groupId);
                },

                'event_scan' => function ($message) use ($wx) {
                    // return '🙄扫码来源：' . $message->EventKey;
                }
            ]);
        }
    }

    /**
     * 回复抽奖活动
     *
     * @param object $message
     * @param object $wx
     *
     * @return string
     */
    private function replyTextLottery($message, $wx)
    {
        $br = PHP_EOL;
        $text = trim($message->Content);

        $user = $wx->user->get($message->FromUserName);
        $user->nickname = Helper::filterEmjoy($user->nickname);

        // 回复格式 { ([\d\w]{8}) }
        if (preg_match('/^[\d\w]{8}$/i', $text)) {
            $result = $this->service('activity.log-winning-code', [
                'code' => $text,
                'openid' => $user->openid,
                'nickname' => $user->nickname
            ]);

            if (is_string($result)) {
                return "Oops! An error has occurred.{$br}{$br}${result}";
            }

            if (!empty($result['error'])) {
                switch ($result['error']) {
                    case 'user_already_receive':
                        if ($result['winning']) {
                            return '咦，我刚才说的是真的，你真的中奖了🙄🙄🙄！记得留下可联系到你的手机号码+姓名哦~';
                        } else {
                            return '我的小可爱，刚刚我说的是真的😭~关注喀客喀客旅行，福利多多，再接再厉！';
                        }
                        break;

                    case 'code_error':
                        return '这个抽奖码不正确😌，请核对哟~';
                        break;

                    case 'code_already_received':
                        return '这个抽奖码已经被小姐姐核领了🙄，如果你确认输入无误，请联系KAKE解决~';
                        break;
                }
            } else {
                if ($result['winning']) {
                    return '我的小可爱，恭喜你中奖了👻👏🍾🎉！惊不惊喜，开不开心？鼓掌鼓掌！喀客客服将随后与你联系，请留下你的手机号码+姓名，并保持畅通~带上心爱的人去看电影吧~';
                } else {
                    return '我的小可爱，很遗憾这次你没有中奖🙄🙄🙄，关注喀客旅行，下次继续，再接再厉！';
                }
            }
        }

        // 回复格式 { 品牌名+姓名+手机号码 }
        // 格式判断
        if (in_array(strtolower($text), [
            '阿里巴巴'
        ])) {
            $company = $text;
            $name = null;
            $phone = null;
        } else {
            $text = str_replace('＋', '+', $text);
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

        $model = parent::model('ActivityLotteryCode');

        // 公司代码验证
        $company = strtolower($company);
        if (false === ($code = array_search($company, $model->_company))) {
            return '该品牌还不是喀客旅行的合作伙伴~';
        }

        if ($code < 26) {
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
            return "Oops! An error has occurred.{$br}{$br}${result}";
        }

        // 已参与判断
        if (!empty($result['exists'])) {
            return "宝贝，不要太贪心哦~你已经参与过啦~{$br}抽奖码：${result['code']}，祝你好运~";
        }

        $text = new Text(['content' => "WoW~ 这是喀客旅行为你提供的抽奖码：${result['code']}！希望你能抽中奖品～"]);
        $wx->staff->message($text)->by($this->staff)->to($message->FromUserName)->send();

        $file = $this->lotteryImg('喀客KAKE x ' . $company, $result['code']);
        $result = $wx->material_temporary->uploadImage($file);

        return new Img(['media_id' => $result->media_id]);
    }

    /**
     * 生成抽奖码图片
     *
     * @access protected
     *
     * @param string $company
     * @param string $code
     *
     * @return string
     */
    protected function lotteryImg($company, $code)
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
