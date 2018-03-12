<?php

namespace frontend\controllers;

use Oil\src\Helper;
use Yii;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Activity controller
 */
class ActivityController extends GeneralController
{
    /**
     * 我和酒店的故事
     */
    public function actionStory()
    {
        $this->sourceCss = ['activity/activity'];
        $this->sourceJs = [
            'activity/activity',
            'jquery.ajaxupload'
        ];

        $this->seo(['title' => '我和酒店的故事']);

        return $this->render('story');
    }

    /**
     * ajax 上传照片
     */
    public function actionUploadPhoto()
    {
        $this->uploader([
            'suffix' => [
                'png',
                'jpg',
                'jpeg',
                'jpe',
                'gif'
            ],
            'pic_sizes' => '200-MAX*200-MAX',
            'max_size' => 1024 * 5
        ]);
    }

    /**
     * 生成截屏图
     *
     * @access private
     *
     * @param string $story
     * @param string $text
     *
     * @return string
     */
    private function screenShot($story, $text)
    {
        $bg = parent::getPathByUrl('img/activity/story-bg.jpg', 'frontend_source');
        $story = parent::getPathByUrl($story);
        $ele = parent::getPathByUrl('img/activity/story-ele.png', 'frontend_source');

        $story = Image::make($story);
        $data = Helper::calThumb(564, 330, $story->width(), $story->height());
        $story->resize($data['width'], $data['height']);

        $img = Image::make($bg);

        $x = intval($data['left'] + 93);
        $y = intval($data['top'] + 150);
        $img->insert($story, 'top-left', $x, $y);

        $img->insert($ele);
        $fonts = parent::getPathByUrl('fonts/hanyi.ttf', 'frontend_source');

        $textArr = Helper::strSplit($text, 14, [
            'zh-cn',
            1,
            3.7 / 7
        ]);
        foreach ($textArr as $line => $str) {
            $y = $line * 40 + 770;

            $img->text($str, 160, $y, function ($font) use ($fonts) {
                $font->file($fonts);
                $font->size(32);
            });
        }

        $tmp = Yii::$app->params['tmp_path'] . '/' . $this->user->id . '.jpg';
        $img->save($tmp);

        return parent::getUrlByPath($tmp, 'jpg', '-', 'screen-shot-');
    }

    /**
     * 提交酒店故事数据
     */
    public function actionAjaxStory()
    {
        $post = Yii::$app->request->post();

        $result = $this->service('activity.add-activity-story', [
            'user_id' => $this->user->id,
            'attachment' => $post['attachment'],
            'story' => $post['story']
        ]);

        if (is_string($result)) {
            $this->fail($result);
        }

        $img = $this->screenShot($post['img'], $post['story']);
        $this->success(['img' => $img]);
    }
}