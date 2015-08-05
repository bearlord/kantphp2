<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tinymce Demo</title>
        <script type="text/javascript" src="<?php echo APP_URL . "public/" ?>js/tinymce/tinymce.min.js"></script>
        <script src="http://siteapp.baidu.com/static/webappservice/uaredirect.js" type="text/javascript"></script>
        <script type="text/javascript">
            tinymce.init({
                selector: "textarea#elm1",
                theme: "modern",
                height: 300,
                plugins: [
                    "advlist autolink lists link image charmap print preview hr anchor pagebreak",
                    "searchreplace wordcount visualblocks visualchars code fullscreen",
                    "insertdatetime media nonbreaking save table contextmenu directionality",
                    "emoticons template paste textcolor"
                ],
                language: 'zh_CN',
                toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
            });
        </script>

    </head>

    <body>
        <!-- place in body of your html document -->
        <textarea id="elm1" name="area"></textarea>
    </body>
</html>
