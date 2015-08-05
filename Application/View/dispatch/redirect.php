<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $this->lang('system_warning'); ?></title>
    </head>
    <body>
        <style>
            .wrapper{width: 100%; margin: 0px auto;}
            .code, .desc, buttons{width: 40%; margin: 10px auto;}
            .center{text-align: center}
        </style>
        <div class="wrapper">
            <div class="code center"><span style="font-size: 36px;"><?php echo $this->lang('warning'); ?></span><i class="icon-warning-sign"></i></div>
            <div class="desc center"><?php echo $message; ?></div>
            <div class="buttons center"><?php if ($url == 'goback' || $url == ''): ?>
                <div class="pull-left">
                    <a class="btn" href="javascript:history.back();"><i class="icon-arrow-left"></i><?php echo $this->lang('history_back'); ?></a>
                </div>
                <?php if (isset($_SERVER['HTTP_REFERER']) == false): ?>
                <div class="pull-right">
                    <a class="btn" href="javascript:window.close();"><i class="icon-remove"></i><?php echo $this->lang('close'); ?></a>
                </div><?php endif; ?>
                    <?php elseif ($url == "close"): ?>
                <div class="pull-left">
                    <a class="btn" href="javascript:window.close();"><i class="icon-remove"></i><?php echo $this->lang('close'); ?></a>
                </div>
                    <?php elseif ($url): ?>
                <div class="pull-right">
                    <a class="btn" href="<?php echo $url; ?>"><i class="icon-arrow-right"></i><?php echo $this->lang('click_here_redirect'); ?>(<i id="wait"><?php echo $second; ?></i>)</a> 
                        <script type="text/javascript">
                            (function () {
                                var wait = document.getElementById('wait'), href = '<?php echo $url; ?>';
                                var interval = setInterval(function () {
                                    var time = wait.innerHTML;
                                    if (time > 0) {
                                        time--;
                                    }
                                    if (time <= 1) {
                                        if (parent.window) {
                                            parent.window.location.href = href;
                                        } else {
                                            location.href = href;
                                        }
                                        clearInterval(interval);
                                    }
                                    ;
                                }, 1000);
                            })();
                        </script>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </body>
</html>
