<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php
    $hp_version = 'v0.8-beta';
    $url_performance = "http://www.typechodev.com/hp_performance.html";
    $url_feedback = "http://www.typechodev.com/hp_feedback.html";
    $url_transform = "http://www.typchodev.com/hp_transform.html";
    $url_news = "http://www.typechodev.com/hp_news.html";
?>
<div class="typecho-page-title">
    <h2 style="color: blue">Typecho高性能优化版<span style="font-size: small;">(<?php echo $hp_version;?>)</span></h2>
</div>
<div class="row typecho-page-main">
    <div class="col-mb-12 welcome-board" role="main">
        <p>Typecho高性能优化版,由<a href="http://www.typechodev.com">TypechoDev</a>提供,在Typecho 1.0 (14.10.10)的基础上优化而来,在<a href="https://www.linode.com/pricing">Linode 2GB</a>
        的配置上,使用200w规模post的数据量进行测试,性能表现良好.可从<a href="<?php echo $url_performance; ?>">这里</a>查阅最新版本的性能测试数据.
        </p>
        <h4>使用注意事项:</h4>
        <ul>
            <li>> 理论可以兼容绝大部分TE插件,但不保证可以兼容全部.<span style="color: blue">如需定制插件,请<a href="mailto:leimiu2014@gmail.com">联系作者.</a></span></li>
            <li>> 优化后的搜索仅支持英文搜索,中文搜索随后版本加上.</li>
            <li>> 高性能Typecho不能直接安装在原始版本上.如需转换,可从<a href="<?php echo $url_transform; ?>">这里</a>获取转换脚本.</li>
        </ul>
        <h4>帮助和反馈:</h4>
        <ul>
            <li>> 反馈或者建议,请从<a href="<?php echo $url_feedback; ?>">这里</a>反馈.非常感谢.</li>
            <li>> 请从<a href="<?php echo $url_news; ?>">这里</a>获取新版本动态及更新程序.</li>
        </ul>
    </div>
</div>
