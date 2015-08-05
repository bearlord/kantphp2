<?php !defined('IN_KANT') && exit('Access Denied'); ?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $this->lang('system_warning'); ?></title>
    </head>

    <body>
        <style>
            body { background: none repeat scroll 0 0 transparent;
                   border: 0 none;
                   margin: 0;
                   padding: 0;
                   vertical-align: baseline;
                   color: #666666;
                   font-size: 12px; 
                   line-height: 21px;
            }
            a { text-decoration: none; color: #666; outline: none; }
            a:link { color: #666; text-decoration: none; }
            a:visited { color: #666; text-decoration: none; }
            a:hover { color: #c00; text-decoration: underline; }
            .t_c { text-align: center; }

            .padding10 {
                padding: 10px;
            }
            .bodywrapper { margin: 100px auto; 
                           background: none repeat scroll 0 0 #FCFCFC; 	
                           margin: 10px auto; 
                           padding: 20px;
            }
            .bodywrapper h1 { font-size: 32px; margin-bottom: 20px; }
            .contentwrapper { 
                padding: 20px; 
                border: 1px solid #DDDDDD;
                border-radius: 2px 2px 2px 2px;
            }

            #action { margin: 10px auto; }
            #copyright { margin: 10px auto; font-size:10px; }
        </style>
        <script type="text/javaScript">
            function redirect(url) {
            window.location.href = url;
            }
        </script>
        <div class="bodywrapper">
            <div class="contentwrapper padding10 t_c">
                <h1> <?php echo $message; ?> </h1>
                <p id="action">
                    <?php if ($url == 'goback' || $url == ''): ?>
                        <a href="javascript:history.back();">[ <?php echo $this->lang('history_back'); ?> ]</a>
                    <?php elseif ($url == "close"): ?>
                        <a href="javascript:window.close();">[ <?php echo $this->lang('close'); ?> ]</a>
                    <?php elseif ($url): ?>
                        <a href="<?php echo $url; ?>">[ <?php echo $this->lang('click_here_redirect'); ?>(<b id="wait"><?php echo $second; ?></b>) ]</a> 
                        <script type="text/javascript">
                            (function() {
                                var wait = document.getElementById('wait'),
                                        href = '<?php echo $url; ?>';
                                var interval = setInterval(function() {
                                    var time = --wait.innerHTML;
                                    if (time <= 0) {
                                        if (parent.window) {
                                            parent.window.location.href = href;
                                        } else {
                                            location.href = href;
                                        }
                                        clearInterval(interval);
                                    }
                                    ;
                                },
                                        1000);
                            })();
                        </script>
                    <?php endif ?>
                </p>
                <div id="copyright" class="t_c">
                    <p>copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
</html>
