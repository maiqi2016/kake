<!DOCTYPE html>
<html lang="zh-cn"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <meta name="renderer" content="webkit">
    <meta name="renderer" content="webkit">
    <title>KAKE业务及后台管理详细文档</title>
</head>

<body>
<div class="main">
<h1>业务模块</h1>

<p>&nbsp;</p>

<h2>配置模块</h2>

<p>&nbsp;</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;很大一部分的项目配置可以在此模块中完成，常用配置如下：</p>

<p>&nbsp; &nbsp;&nbsp;</p>

<p>&nbsp; &nbsp; - 网页标题</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;微信分享标题、微信分享内容描述</p>

<p>&nbsp; &nbsp; -&nbsp;验证码发送倒计时时长</p>

<p>&nbsp; &nbsp; -&nbsp;验证码有效时长</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;<span helvetica="" neue="" style="font-family: ">参与分佣的最低金额标准</span></p>

<p>&nbsp; &nbsp; -&nbsp;公司电话(产品详情页拨打电话的号码)</p>

<p>&nbsp; &nbsp; -&nbsp;新增产品套餐时默认的套餐购买上线次数</p>

<p>&nbsp; &nbsp; -&nbsp;产生订单时需要通知的管理员</p>

<p>&nbsp; &nbsp; -&nbsp;订单列表页面展示条数</p>

<p>&nbsp; &nbsp; -&nbsp;产品列表页面展示条数</p>

<p>&nbsp; &nbsp; -&nbsp;首页焦点图显示个数（单纯的图片广告）</p>

<p>&nbsp; &nbsp; -&nbsp;首页焦点图显示个数（酒店产品）</p>

<p>&nbsp; &nbsp; -&nbsp;首页banner广告（横条）</p>

<p>&nbsp; &nbsp; -&nbsp;首页精品推荐酒店个数</p>

<p>&nbsp; &nbsp; -&nbsp;首页闪购酒店个数</p>

<p>&nbsp; &nbsp; -&nbsp;开启系统升级中</p>

<p>&nbsp; &nbsp; -&nbsp;是否使用缓存</p>

<p>&nbsp;</p>

<h2>用户管理模块<br>
&nbsp;</h2>

<p>&nbsp;&nbsp;&nbsp;&nbsp;此模块记录用户是常用信息，一般用于查找用户的相关订单、ID，然后对用户的角色管理、登录管理、冻结管理和对用户的权限分配。</p>

<p>&nbsp; &nbsp; 除此之外，还包含以下：</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;</p>

<p>&nbsp; &nbsp; - 记录了所有用户的登录信息，包含后台登录、微信登录、手机号码登录的记录</p>

<p>&nbsp; &nbsp; - 项目的运行日志（需要配置计划任务来查看管理）</p>

<p>&nbsp; &nbsp; - 接口的运行日志</p>

<p>&nbsp; &nbsp; - 短信验证码的日志，包含后台登录短信、前台登录短信、下单前联系人的短信、分销活动短信</p>

<p>&nbsp; &nbsp; - 活动抽奖码日志（公众号对话框回复公司名活动）</p>

<p>&nbsp; &nbsp; - 酒店故事活动日志</p>

<p>&nbsp; &nbsp; - 兑奖日志 (批量生成抽奖码由市场分发给现场参与的形式，活动结束后可在公众号对话框回复抽奖码确认中奖情况)</p>

<p>&nbsp;</p>

<h2>目的地管理模块</h2>

<p>&nbsp;</p>

<p>&nbsp; &nbsp; 管理项目中所有用到的目的地，及目的地所归属的板块，可按照目前的格式，编辑新增即可在前端对应的显示。</p>

<p>&nbsp; &nbsp; 数据主要用于前台目的地列表、前台板块列表、酒店所属地区选择</p>

<h2>&nbsp;</h2>

<h2>产品管理模块</h2>

<h2>&nbsp;</h2>

<p>&nbsp; &nbsp; <span style="color:#FF0000;"><strong>产品上游：</strong></span></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 最早实为酒店管理，酒店管理也是目前所偏向的业务范围，但也不局限于酒店管理，如后续需要增加餐饮、玩乐类型的场所（即所谓的产品上游），就可以灵活的在此模块统一的进行管理了。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 产品上游需指定对应的目的地。</p>

<p>&nbsp;</p>

<p>&nbsp; &nbsp; <strong><span style="color:#FF0000;">产品：</span></strong></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 即是用户看到的最直观的商品概念，该产品可衍生出：酒店产品、玩乐产品、餐饮产品等。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 产品需要指定对应的产品上游。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 产品的维度目前在项目中的适用性及普及性是最广的，主要是酒店产品和分销体系。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 提示：</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - 如果产品的价格有误（包含折扣有误）或者套餐缺失等数据问题会影响前台的产品正常展示。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - 针对产品架构的折扣设置项是针对该产品下的所有套餐生效，设置时应当注意，并且折扣应当设置折扣类型和时间段。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - 前台展示的产品销量 = 虚拟销量（产品上线初期为0的问题规避） + 真实销量</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - 表现形式的不一致将决定该产品显示在哪个模块</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp; - 晚次目前只有显示作用</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - 售罄选项类似下架（将状态修改为删除），唯一的区别还可以在前台正常显示，而下架产品将提示已下架。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - 推广链接用于分销管理的产品列表中显示</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;- 排序将决定产品的显示前后问题，越小排序值展示约靠前（不小于1的整数）</p>

<p>&nbsp;</p>

<p>&nbsp; &nbsp; <span style="color:#FF0000;"><strong>产品套餐：</strong></span></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 为了最小粒度化产品的规格，可以针对产品添加最少一个套餐</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 而且套餐可以规避订单中心的展示、预约、明确化追踪等问题。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 新增套餐时需要指定对应的产品，除非你在新增产品的同时也新增了套餐。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 提示：</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - 套餐简介编辑时的所见即用户所见的个数，尽量保持统一格式。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - 底价只用于备注提醒管理员。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - 限购数量规定单个用户对该套餐的永久最多购买次数</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp; - 竞价表明该套餐是否会参与最低价格显示在各页面的列表数据中。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - 核销表明该套餐为简洁售后流程，即无预约之后的流程，统一由商家扫码或输入核销码统一确定</p>

<p>&nbsp;</p>

<p>&nbsp; &nbsp; <span style="color:#FF0000;"><strong>套餐打包：</strong></span></p>

<p>&nbsp; &nbsp; &nbsp; &nbsp; 套餐打包用于将套餐捆绑销售，针对以下附加产品，比如餐食或门票类型的套餐优惠，又不能单独售卖，可以在此处对两个以上的产品套餐进行捆绑销售，在前端显示时，当用户勾选任意打包套餐中的一个时，将自动勾选该打包的剩余所有套餐。</p>

<p>&nbsp;</p>

<h2>订单管理模块</h2>

<h2>&nbsp;</h2>

<p>&nbsp; &nbsp; 订单管理有主订单和子订单的区分，单次购买用户只能对同一个产品进行购买，目前实现购物车等多个产品的套餐购买只能通过多次走购买流程实现，即会产生同数量个主订单。</p>

<p>&nbsp; &nbsp;</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#FF0000;"><strong>主订单：</strong></span></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 主订单以一次购买流程（一个产品）为维度产生，主要包含订单编号、订单联系人相关信息、总价格、套餐购买概况、支付状态、支付方式、分销商出处、购买用户、所属上游等基础数据信息；可实时查询订单支付状态或退款状态。</p>

<p>&nbsp;</p>

<p>&nbsp; &nbsp; <span style="color:#FF0000;"><strong>子订单：</strong></span></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 子订单最小粒度与产品套餐保持一致，即一个套餐对应一个子订单，主要包含套餐相关信息、 购买数量、预约情况；并可以有不同状态下对应有操作预约申请、操作退款申请、操作发票申请等操作，且子订单的相关操作有将有订单操作日志模块进行记录。</p>

<p>&nbsp;</p>

<h2>分销管理模块</h2>

<h2>&nbsp;</h2>

<p>&nbsp; &nbsp; 分销商可以通过固定的链接，或者前端的菜单或固定的二维码进入分销商注册页面；提交注册申请后，后台分销商申请列表中会列出所有的分销商申请记录，可选择性的评估是否通过，从而在质量和数量上控制分销商的规模，也方便后续的分销商管理；同意成为分销商后，该分销商默认继承当前已有的可分销产品，并继承响应的排序规则，在个人分销商设置中可以单独对个体分销商进行排序和管理。</p>

<p>&nbsp; &nbsp; 针对分销商的分享产品有两种方便的排序功能：</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - <strong>统一排序</strong>：即对所有的分销商进行统一排序，排序中对可分销的产品进行拖动排序，提交后将按此顺序改变每一个分销商分销产品的排序；</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - <strong>克隆排序</strong>：针对单一的分销商可以克隆任意你想要与他相同排序的分销商。</p>

<p>&nbsp;</p>

<p>&nbsp; &nbsp; <span style="color:#FF0000;"><strong>产品分销设置：</strong></span></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 针对后台管理的所有产品，表明该产品是否可用于分销商选择性的进行分销，不在列表中的产品将不被分销。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 针对选择的产品，可进行分佣类型的选择，主要包含固定额度分佣和百分比分佣。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 并且可以针对分销出去的数量进行阶段划分固定分佣额度或者分佣的百分比比例。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;</p>

<p>&nbsp; &nbsp; <span style="color:#FF0000;"><strong>分销记录：</strong></span></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 这里记录着所有分销商的所有分销记录，包含对应订单、子订单的概况（售卖个数、子订单状态、价格等信息）；</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 列表中会自动计算该分销订单的入围分佣额度和淘汰额度，并判断该订单是否可结算分佣，和自动统计所属的分佣档次。</p>

<p>&nbsp;</p>

<p>&nbsp; &nbsp; <strong><span style="color:#FF0000;">余额账目和提现记录：</span></strong></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 每个分销商产生分销订单后在前端的页面中有结算按钮，需手动结算，结算后的额度会分配到后台的余额账目，余额可永久保存，不会过期，并且余额达到一定额度后（目前为100），在上面说到的配置管理有也可以动态配置（环境与配置 &gt; 文件预配置 &gt; <span style="color:#008000;">withdraw_min</span><strong>&nbsp;</strong>最后一个）。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 分销商可在前端分销商管理对应页面中进行分佣提现，提现后后台提现记录中将出现该申请，可对申请进行同意和拒绝操作，该操作无任何后仅仅将余额扣除相应额度，操作人员应确保相应的金额已经由财务转账给支付宝（如果使用的微信方式将自动从微信商户账号扣除转账，无需人工操作）。</p>

<p>&nbsp;</p>

<h2>核销管理模块</h2>

<h2>&nbsp;</h2>

<p>&nbsp; &nbsp; 核销的产品基本上属于简洁类型的流程产品，无需预约，直接有商户进行扫描二维码或者输入核销码进行确认即可；一个供应商可以有多个核销员，一个用户也可以属于多个供应商，并对所有的核销子订单有核销的相关日志记录。</p>

<p>&nbsp;</p>

<h2>活动管理模块</h2>

<h2>&nbsp;</h2>

<p>&nbsp; &nbsp; 奖品管理即是日历活动中可以对任意一天，或者任意连续天进行活动设置，设置后，用户可以根据日历判断活动的概况并进行参与。</p>

<p>&nbsp; &nbsp; 后台会记录所有参与日历活动的所有记录。</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;</p>

<p>&nbsp; &nbsp; 当一个活动结束后，后台对应该活动的活动记录后面将会出现开奖管理的操作按钮。点击操作按钮后会根据开奖规则进行计算开奖码，然后用户可以在活动参与界面查看中奖情况。</p>

<p>&nbsp;</p>

<h2>其他管理</h2>

<h2>&nbsp;</h2>

<p>&nbsp; &nbsp; <strong><span style="color:#FF0000;">图片和广告：</span></strong>主要用于首页的焦点图和横条广告的管理。</p>

<p>&nbsp; &nbsp;</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;<strong><span style="color:#FF0000;">微信菜单：</span></strong>使用特定的JSON格式字符串对微信公众对话框底部的菜单进行修改。</p>

<p>&nbsp; &nbsp;</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#FF0000;"><strong>微信模板消息：</strong></span>可以针对现有的模板对指定用户或用户群体进行发送响应的模板消息。</p>

<p>&nbsp; &nbsp;</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;<strong><span style="color:#FF0000;">微信二维码：</span></strong>目前主要用于关注二维码、分销商注册二维码、分销活动二维码。不同的二维码会有以下的区别：</p>

<p>&nbsp;</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 关注推广：<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - A. 用户扫描后出现关注界面(如果用户未关注)；</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - B. 随后跳转到对话框。</p>

<p>&nbsp;</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 分销商推广：</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - A. 用户扫描后出现关注界面(如果用户未关注)；</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - B. 随后跳转到对话框，并弹出欢迎关注致辞和注册分销商的入口链接；</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - C. 微信后台创建同名的用户分组；</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - D. 并在微信后台将该分销商加入到该分组中。</p>

<p>&nbsp;</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; 分销商活动推广：</p>

<p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; - A. 用户扫描后出现关注界面(如果用户未关注)；</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; - B. 随后跳转到对话框，并弹出欢迎关注致辞和分销商活动的入口链接；</p>

</div>

</body></html>