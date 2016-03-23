<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="en">

<head lang="zh-cmn-Hans">
    <base href="/"/>
    <meta charset="UTF-8">
    <title>个人博客</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="alternate icon" type="image/png" href="favicon.ico">
    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/assets/css/main.css"/>
</head>

    <?php echo $content; ?>

    <footer>
        <p class="am-padding-left">Copyright &copy; 2014-2016 <a href="https://www.fenghuilee.com/">FenghuiLee</a> & <a href="https://www.supermisv.com/" target="_blank" rel="external">Super mISV LLC</a>. Licensed under MIT license.</p>
        <p class="am-padding-left">Powered by <a href="http://www.yiiframework.com/" target="_blank" rel="external">Yii Framework</a></p>
        <p class="am-padding-left"><script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1255347304'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s4.cnzz.com/z_stat.php%3Fid%3D1255347304%26show%3Dpic2' type='text/javascript'%3E%3C/script%3E"));</script></p>
    </footer>
    <div data-am-widget="gotop" class="am-gotop am-gotop-fixed">
        <a href="#top" title="回到顶部" class="am-icon-btn am-icon-arrow-up am-active" id="amz-go-top"></a>
    </div>

    <!--[if lt IE 9]>
    <script src="//lib.sinaapp.com/js/jquery/1.10.2/jquery-1.10.2.min.js"></script>
    <script src="//cdnjscn.b0.upaiyun.com/libs/modernizr/2.8.2/modernizr.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/assets/js/main.js"></script>
    <![endif]-->

    <!--[if (gte IE 9)|!(IE)]><!-->
<!--    <script src="--><?php //echo Yii::app()->request->baseUrl; ?><!--/assets/js/polyfill.js"></script>-->
    <!--<![endif]-->
</body>
</html>
