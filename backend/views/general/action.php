<?php
/* @var $this yii\web\View */

use yii\helpers\Url;
use yii\helpers\Html;
use Oil\src\Helper;
use backend\components\ViewHelper;

$flash = \Yii::$app->session->hasFlash('list') ? \Yii::$app->session->getFlash('list') : [];

$controller = \Yii::$app->controller->id;
$action = \Yii::$app->controller->action->id;
$modal = empty($view['modal']) ? false : true;
$modelInfo = empty($view['info_perfect']) ? $modelInfo : null;
?>

<?php if (!$modal): ?>
    <div class="title col-sm-offset-1">
        <span class="glyphicon glyphicon-<?= $view['title_icon'] ?>"></span> <?= $view['title_info'] ?><?= $modelInfo ?>
    </div>
<?php endif; ?>

<?php
$action = null;
if (!empty($view['action'])) {
    if ($modal) {
        $script = empty($view['action']) ? 'false' : ViewHelper::escapeScript($view['action']);
        $action = 'onsubmit="return ' . $script . '"';
    } else {
        $action = 'method="post" action="' . Url::toRoute([$controller . '/' . $view['action']]) . '"';
    }
}
?>
<form class="form-horizontal" <?= $action ?>>
    <?php
    if (!$modal) {
        echo Html::input('hidden', Yii::$app->request->csrfParam, Yii::$app->request->csrfToken);
    }

    if (!empty($view['action']) && strpos($view['action'], 'edit') !== false) {
        echo Html::input('hidden', 'id', $id);
    }
    ?>

    <?php $pre_same_row = false ?>
    <?php foreach ($list as $field => $item): ?>

        <?php
        $empty = function ($key, $default = null, $data = null, $fn = 'empty') use ($item) {
            $data = $data ?: $item;
            $fn = $fn . 'Default';

            return Helper::$fn($data, $key, $default);
        };

        // 主标签声明
        $element = $empty('elem', 'input');

        // 标签属性值
        $av_name = $empty('name', $field);
        $av_type = $empty('type', 'text');
        $av_html = $empty('html', false);
        $av_value = !empty($flash[$av_name]) ? $flash[$av_name] : $empty('value', null, null, 'isset');
        $av_script = ViewHelper::escapeScript($empty('script'));
        $av_class = $empty('class');
        $av_assist = $empty('assist');

        // 标签属性字符串
        $as_readonly = empty($item['readonly']) ? null : 'readonly=readonly';
        $as_placeholder = 'placeholder="' . $empty('placeholder') . '"';
        $as_name = ($av_assist ? 'id' : 'name') . '="' . $av_name . '"';
        $as_type = 'type="' . $av_type . '"';
        $as_script = $av_script ? 'onclick="' . $av_script . '"' : '';

        if (!is_array($av_value)) {
            $av_value = $av_html ? $av_value : Html::encode($av_value);
            $as_value = 'value="' . strval($av_value) . '"';
        }

        $as_tip = null;
        if ($tip = $empty('tip')) {
            $as_tip = 'data-toggle="tooltip" data-html="true" data-placement="' . $empty('pos', 'right') . '" title="' . $tip . '"';
        }

        // 下一个 item 与当前 item 同一行
        $same_row = $empty('same_row');

        // 开始标签和结尾标签
        $html_begin_div = $pre_same_row ? null : '<div class="form-group box_' . $av_name . ' ' . ($empty('hidden', false) ? 'hidden' : null) . '">';
        // 栅格数和标题
        $html_label = ($item['title'] === false) ? null : '<label class="col-sm-2 control-label">' . $empty('title') . '</label>';
        // 补充html
        $html_end_div = $same_row ? null : '</div>';

        $pre_same_row = $same_row;

        // 显示条件
        $show_condition = $empty('show', null);
        // 监控值的变更
        $value_change = $empty('change', null);
        ?>

        <?php if ($element == 'input' && $av_type == 'file'): ?> <!-- input.file description -->
        <div class="form-group">
            <label class="col-sm-2 control-label"></label>
            <div class="col-sm-<?= $empty('label_tips', 4) ?>" <?= $as_tip ?>>
                <table class="table table-bordered table-striped">
                    <tbody>
                    <?php if ($empty('multiple')): ?>
                        <tr>
                            <td><kbd>允许多张</kbd></td>
                            <td><code class="success">是</code></td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($item['rules'] as $k => $v): ?>
                        <tr>
                            <td><kbd><?= $item['rules_info'][$k] ?></kbd></td>
                            <td><code class="info"><?= is_array($v) ? implode(',', $v) : $v ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?= $html_begin_div ?>
    <?= $html_label ?>

    <?php if ($element == 'input'): ?> <!-- input -->
        <div class="col-sm-<?= $empty('label', 3) ?> <?= $av_class ?>" <?= $as_tip ?>>
            <?php $av_type == 'file' && $as_name = 'id="' . $av_name . '"' ?>
            <input class="form-control"
                <?= $as_name ?>
                <?= $as_script ?>
                <?= $as_readonly ?>
                <?= $as_placeholder ?>
                <?= $as_type ?>
                <?= $as_value ?>>
        </div>

    <?php
    if ($av_type == 'file') :
    $previewRule = Helper::emptyDefault($list, $empty('preview_name'), []);
    $json = [
        'triggerTarget' => "#{$av_name}",
        'action' => Url::toRoute(['general/ajax-upload']),
        'data' => [
            'tag' => $empty('tag'),
            'controller' => Yii::$app->controller->id,
            'action' => Yii::$app->controller->action->id,
            Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
        ],
        'attachmentName' => $empty('field_name'),
        'previewName' => $empty('preview_name'),
        'multiple' => $empty('multiple') ? 1 : 0,
        'previewLabel' => Helper::emptyDefault($previewRule, 'img_label', 4)
    ];
    ?>
        <script type="text/javascript">
            $(function () {
                $.uploadAttachment(<?= json_encode($json, JSON_UNESCAPED_UNICODE) ?>);
            });
        </script>
    <?php endif; ?>
    <?php elseif ($element == 'img'): ?> <!-- img -->
        <div class="col-sm-<?= $empty('label', 10) ?> <?= $av_class ?>" <?= $as_tip ?> <?= $as_script ?>>
            <div class="row" <?= $as_name ?>>
                <?php
                $attachment = (array) $empty($field, $empty('value'), $flash);
                if (empty($attachment)) {
                    $attachment = [];
                }

                $uploader = Helper::issetDefault($list, $empty('upload_name'));

                $attachmentName = Helper::emptyDefault($uploader, 'field_name');
                $previewName = Helper::emptyDefault($uploader, 'preview_name', $field);
                $multiple = Helper::emptyDefault($uploader, 'multiple') ? 1 : 0;

                $json = [
                    'attachmentName' => $attachmentName,
                    'previewName' => $previewName,
                    'previewLabel' => $empty('img_label', 4),
                    'multiple' => $multiple,
                    'action' => !$empty('readonly')
                ];
                ?>
                <div class="for-script">
                    <?php if (!empty($attachment)): ?>
                        <script type="text/javascript">
                            $(function () {
                                <?php
                                foreach ($attachment as $id => $url):
                                $json['data'] = compact('id', 'url');
                                $_json = json_encode($json, JSON_UNESCAPED_UNICODE);
                                ?>
                                $.createThumb(<?= $_json ?>);
                                <?php endforeach; ?>
                            });
                        </script>
                    <?php endif; ?>

                    <?php if ($multiple): ?>
                        <script type="text/javascript">
                            $(function () {
                                $.sortable('div[name="<?= $previewName ?>"]', 'input[name="<?= $attachmentName ?>"]');
                            });
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif ($element == 'select'): ?> <!-- select -->
        <div class="col-sm-<?= $empty('label', 2) ?> <?= $av_class ?>" <?= $as_tip ?> <?= $as_script ?>>
            <?php
            $value = $empty('value');
            $selected = Helper::issetDefault($flash, $field, $value['selected']);
            echo Helper::createSelect($value['list'], $as_name, $selected, 'key', $empty('readonly', false));
            ?>
        </div>
    <?php elseif ($element == 'radio'): ?> <!-- radio -->
        <div class="col-sm-<?= $empty('label', 2) ?> <?= $av_class ?>" <?= $as_tip ?> <?= $as_script ?>>
            <?php
            $value = $empty('value');
            $selected = Helper::issetDefault($flash, $field, $value['selected']);
            echo Helper::createRadio($value['list'], $as_name, $selected, 'key', $empty('readonly', false));
            ?>
        </div>
    <?php elseif ($element == 'checkbox'): ?> <!-- checkbox -->
        <div class="col-sm-<?= $empty('label', 2) ?> <?= $av_class ?>" <?= $as_tip ?> <?= $as_script ?>>
            <?php
            $value = $empty('value');
            $selected = Helper::issetDefault($flash, $field, $value['selected']);
            if (is_string($selected)) {
                $selected = explode(',', $selected);
            }
            echo Helper::createCheckbox($value['list'], $as_name, $selected, 'key', $empty('readonly', false));
            ?>
        </div>
    <?php elseif ($element == 'textarea'): ?> <!-- textarea -->
        <div class="col-sm-<?= $empty('label', 6) ?> <?= $av_class ?>" <?= $as_tip ?>>
            <?php $as_row = 'rows="' . $empty('row', 3) . '"' ?>
            <textarea class="form-control"
                <?= $as_name ?>
                <?= $as_row ?>
                <?= $as_script ?>
                <?= $as_placeholder ?>><?= $av_value ?></textarea>
        </div>
    <?php elseif ($item['elem'] == 'ckeditor'): ?> <!-- ckeditor -->
        <div class="col-sm-<?= $empty('label', 10) ?> <?= $av_class ?>" <?= $as_tip ?> <?= $as_script ?>>
            <textarea
                <?= $as_name ?>
                <?= $as_placeholder ?>><?= $av_value ?></textarea>
        </div>
        <script type="text/javascript">
            var <?= $av_name ?> =
            CKEDITOR.replace('<?= $av_name ?>', {
                width: <?= $empty('width', 700) ?>,
                height: <?= $empty('height', 300) ?>,
                files: []
            });
        </script>
    <?php elseif ($item['elem'] == 'tag'): ?>  <!-- tag -->
        <div class="col-sm-<?= $empty('label', 6) ?> <?= $av_class ?>"
            <?= $as_tip ?>
            <?= $as_name ?>
            <?= $as_script ?>
             format="<?= $empty('format') ?>"></div>
    <?php
    if (!empty($av_value)):
    $json = [
        'containerName' => $av_name,
        'fieldName' => $empty('field_name'),
        'fieldNameNew' => 'new_' . $empty('field_name')
    ]; ?>
        <script type="text/javascript">
            $(function () {
                <?php
                foreach ($av_value as $pk):
                $json['data'] = $pk;
                $_json = json_encode($json, JSON_UNESCAPED_UNICODE);
                ?>
                $.createTag(<?= $_json ?>);
                <?php endforeach; ?>
            });
        </script>
    <?php endif; ?>
    <?php elseif ($item['elem'] == 'button'): ?>  <!-- button -->
        <div class="col-sm-<?= $empty('label', 6) ?> <?= $av_class ?>"
             format="<?= $empty('format') ?>"
            <?= $as_tip ?>
            <?= $as_name ?>>
            <button class="btn btn-<?= $empty('level', 'primary') ?>"
                    type="button"
                <?= $as_script ?>><?= $av_value ?></button>
        </div>
    <?php elseif ($element == 'text'): ?> <!-- text -->
        <div class="col-sm-<?= $empty('label', 3) ?>" <?= $as_tip ?>>
        <?php $tag = $empty('tag', 'p') ?>
        <<?= $tag ?> class="<?= $av_class ?>" <?= $as_name ?> <?= $as_script ?>><?= $av_value ?></<?= $tag ?>>
        </div>
    <?php endif; ?>

    <?php if (!empty($show_condition) || !empty($value_change)): ?>
        <script type="text/javascript">
            $(function () {
                var son = 'input, select, textarea';

                // 该栏显示的条件判断
                <?php if (!empty($show_condition)): ?>
                var change = function () {
                    var showNum = '<?= count($show_condition) ?>';
                    var show = 0;
                    <?php foreach ($show_condition as $k => $v): ?>
                    var value = $('.box_<?= $k ?>').find(son).val();
                    show += eval('<?= $v ?>') ? 1 : 0;
                    <?php endforeach; ?>

                    var box = $('.box_<?= $av_name ?>');
                    if (show >= showNum) {
                        box.removeClass('hidden');
                    } else {
                        box.addClass('hidden');
                    }
                };

                change();
                <?php foreach ($show_condition as $k => $v): ?>
                $('.box_<?= $k ?>').find(son).change(function () {
                    change();
                });
                <?php endforeach; ?>
                <?php endif; ?>

                // 监控值的修改
                <?php if (!empty($value_change)): ?>
                $('.box_<?= $av_name ?>').find(son).change(function () {
                    try {
                        var fn = '<?= $value_change ?>';
                        if (fn.indexOf('(') !== -1 || fn.indexOf(')') !== -1) {
                            eval(fn);
                        } else {
                            var val = eval('(' + $(this).val() + ')');
                            if ($._isString(val)) {
                                eval(fn + '("' + $(this).val() + '", ".box_<?= $av_name ?>")');
                            } else {
                                eval(fn + '(' + $(this).val() + ', ".box_<?= $av_name ?>")');
                            }
                        }
                    } catch (e) {
                        $.alert('`change` 表达式或表达式内容报错<br><br>' + e.message, 'danger');
                    }
                });
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
        <?= $html_end_div ?>
    <?php endforeach; ?>

    <?php if (!empty($initScript)): ?>
        <script type="text/javascript">
            $(function () {
                // 页面初始化执行
                <?= implode(';', $initScript) ?>
            });
        </script>
    <?php endif; ?>

    <br>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <?php if (!empty($view['button_info'])): ?>
                <button type="submit"
                        class="btn btn-<?= empty($view['button_level']) ? 'primary' : $view['button_level'] ?>">
                    <?= $view['button_info'] ?><?= $modal ? null : $modelInfo ?>
                </button>
            <?php endif; ?>

            <?php if (!empty($operation)): ?>
                <?php
                $result = empty($result) ? [] : $result;
                echo ViewHelper::createButtonForRecord($operation, $result, $controller);
                ?>
            <?php endif; ?>
        </div>
    </div>
    <br>
</form>