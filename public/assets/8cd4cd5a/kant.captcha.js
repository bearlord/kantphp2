/**
 * Kant Captcha widget.
 * 
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
(function ($) {
    $.fn.kantCaptcha = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.kantCaptcha');
            return false;
        }
    };

    var defaults = {
        refreshUrl: undefined,
        hashKey: undefined
    };

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                var settings = $.extend({}, defaults, options || {});
                $e.data('kantCaptcha', {
                    settings: settings
                });

                $e.on('click.kantCaptcha', function () {
                    methods.refresh.apply($e);
                    return false;
                });

            });
        },

        refresh: function () {
            var $e = this,
                settings = this.data('kantCaptcha').settings;
            $.ajax({
                url: $e.data('kantCaptcha').settings.refreshUrl,
                dataType: 'json',
                cache: false,
                success: function (data) {
                    $e.attr('src', data.url);
                    $('body').data(settings.hashKey, [data.hash1, data.hash2]);
                }
            });
        },

        destroy: function () {
            return this.each(function () {
                $(window).unbind('.kantCaptcha');
                $(this).removeData('kantCaptcha');
            });
        },

        data: function () {
            return this.data('kantCaptcha');
        }
    };
})(window.jQuery);

