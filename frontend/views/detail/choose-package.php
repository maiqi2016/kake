<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'detail';
?>

<header>
    选择套餐
    <div class="menu detail" kk-menu-lm>
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>
<div class="body" product-id="<?= $productId ?>">
    <div class="blank" ng-init='init(<?= json_encode($packageList) ?>, <?= json_encode($packageBind) ?>)'></div>
    <div class="combo">
        <div class=" detail-hotel-1">
            <span> <img src="<?= $params['frontend_source'] ?>/img/classify.svg"/></span>
            选择套餐
        </div>
        <ul>
            <?php if (!empty($packageList)): ?>
                 <?php foreach ($packageList as $item): ?>

                    <?php
                    $id = $item['id'];
                    $number = "buy.package['limit_" . $id . "'].number";

                    $showP = 1;
                    foreach($packageBind as $k => $bind) {
                        if (in_array($id, $bind) && current($bind) != $id) {
                            $showP = 0;
                        }
                    }
                    ?>

                    <?php if ($item['min_purchase_limit'] != 0): ?>
                        <li class="combo_1">
                            <div class="combo-1">
	                            	<div class="lable">
	                            		<div class="click-b" kk-tap="packageTap(<?= $id ?>, <?= $showP ?>)">
                                            <b ng-if="<?= $showP ?>" ng-class="{'current': <?= $number ?>}">
                                            </b>
                                        </div>
	                               	<span class="title"><?= $item['name'] ?></span>
	                               	<i class="price">￥<?= $item['min_price'] ?></i>
	                            	</div>
                            </div>
                            <div class="combo-2 kk-animate ng-hide" ng-class="{'kk-b2s-show': <?= $number ?>}"
                                 ng-show="<?= $number ?>">
                                <i></i>

                                <div class="combo-3">
                                    <pre><?= $item['info'] ?></pre>
                                </div>
                                <div class="combo-4">
                                    <div class="combo-4-1">
                                        购买数量
                                        (<?= $item['min_purchase_limit'] < 0 ? '无限制' : '≤' . $item['min_purchase_limit'] . '份' ?>
                                        )
                                    </div>
                                    <div class="combo-4-2">
                                        <span class="reduction" kk-tap="goodsDel(<?= $id ?>)">-</span>
                                        <span class="num" ng-bind="<?= $number ?>"></span>
                                        <span class="add"
                                              kk-tap="goodsAdd(<?= $id ?>, <?= $item['min_purchase_limit'] ?>)">+</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="combo_1">
                            <div class="combo-1 disabled">
                                <b></b><?= $item['name'] ?> <span>￥<?= $item['min_price'] ?> (已达购买上限)</span>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    <div class="blank">

    </div>
    <div class="linkman">
        <div class=" detail-hotel-1">
            <span> <img src="<?= $params['frontend_source'] ?>/img/classify.svg"/></span>
            联系人信息
        </div>
        <?php if (!empty($contact)): ?>
        <p ng-init="buy.user_info.name = '<?= $contact["real_name"] ?>'"></p>
        <p ng-init="buy.user_info.phone = <?= $contact['phone'] ?>"></p>
        <?php endif; ?>
        <input type="text" class="input-border" name="name" ng-model="buy.user_info.name" placeholder="姓名"/>
        <input type="number" class="input-border" name="phone" ng-model="buy.user_info.phone" placeholder="手机号码"/>

        <div class="auth-code">
            <input name="captcha" class="input-border" ng-model="buy.user_info.captcha" placeholder="验证码"/>

            <div class="auth-code-1" kk-sms="{{buy.user_info.phone}}" data-type="2">发送验证码</div>
        </div>
    </div>
    <div class="blank">

    </div>
    <div class="payment">
        <div class=" detail-hotel-1">
            <span> <img src="<?= $params['frontend_source'] ?>/img/classify.svg"/></span>
            选择支付方式
        </div>
        <ul>
            <li class="payment-wechat" kk-tap="buy.payment_method = 'wx'"
                ng-class="{'current': buy.payment_method == 'wx'}">
                <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/wechat.png"/>
                <label class="pay">微信支付</label>
            </li>
            <li class="payment-allpay" kk-tap="buy.payment_method = 'ali'"
                ng-class="{'current': buy.payment_method == 'ali'}">
                <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/allpay.png"/>
                <label class="pay">支付宝支付</label>
            </li>
        </ul>
    </div>
</div>

<footer>
    <div class="price"><p>￥<span ng-bind="totalPrice"></span></p></div>
    <div class="buy" kk-tap="goToPayment()">立即付款</div>
</footer>
