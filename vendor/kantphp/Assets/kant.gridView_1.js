/**
 * Kant GridView widget.
 * 
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
(function ($) {
    $.fn.kantGridView = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.kantGridView');
            return false;
        }
    };

    var defaults = {
        filterUrl: undefined,
        filterSelector: undefined
    };

    var gridData = {};

    var gridEvents = {
        /**
         * beforeFilter event is triggered before filtering the grid.
         * The signature of the event handler should be:
         *     function (event)
         * where
         *  - event: an Event object.
         *
         * If the handler returns a boolean false, it will stop filter form submission after this event. As
         * a result, afterFilter event will not be triggered.
         */
        beforeFilter: 'beforeFilter',
        /**
         * afterFilter event is triggered after filtering the grid and filtered results are fetched.
         * The signature of the event handler should be:
         *     function (event)
         * where
         *  - event: an Event object.
         */
        afterFilter: 'afterFilter'
    };

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                var settings = $.extend({}, defaults, options || {});
                var id = $e.attr('id');
                if (gridData[id] === undefined) {
                    gridData[id] = {};
                }

                gridData[id] = $.extend(gridData[id], {settings: settings});

                var enterPressed = false;
                $(document).off('change.kantGridView keydown.kantGridView', settings.filterSelector)
                    .on('change.kantGridView keydown.kantGridView', settings.filterSelector, function (event) {
                        if (event.type === 'keydown') {
                            if (event.keyCode !== 13) {
                                return; // only react to enter key
                            } else {
                                enterPressed = true;
                            }
                        } else {
                            // prevent processing for both keydown and change events
                            if (enterPressed) {
                                enterPressed = false;
                                return;
                            }
                        }

                        methods.applyFilter.apply($e);

                        return false;
                    });
            });
        },

        applyFilter: function () {
            var $grid = $(this), event;
            var settings = gridData[$grid.attr('id')].settings;
            var data = {};
            $.each($(settings.filterSelector).serializeArray(), function () {
                if (!(this.name in data)) {
                    data[this.name] = [];
                }
                data[this.name].push(this.value);
            });

            var namesInFilter = Object.keys(data);

            $.each(kant.getQueryParams(settings.filterUrl), function (name, value) {
                if (namesInFilter.indexOf(name) === -1 && namesInFilter.indexOf(name.replace(/\[\]$/, '')) === -1) {
                    if (!$.isArray(value)) {
                        value = [value];
                    }
                    if (!(name in data)) {
                        data[name] = value;
                    } else {
                        $.each(value, function (i, val) {
                            if ($.inArray(val, data[name])) {
                                data[name].push(val);
                            }
                        });
                    }
                }
            });

            var pos = settings.filterUrl.indexOf('?');
            var url = pos < 0 ? settings.filterUrl : settings.filterUrl.substring(0, pos);

            $grid.find('form.gridview-filter-form').remove();
            var $form = $('<form/>', {
                action: url,
                method: 'get',
                'class': 'gridview-filter-form',
                style: 'display:none',
                'data-pjax': ''
            }).appendTo($grid);
            $.each(data, function (name, values) {
                $.each(values, function (index, value) {
                    $form.append($('<input/>').attr({type: 'hidden', name: name, value: value}));
                });
            });

            event = $.Event(gridEvents.beforeFilter);
            $grid.trigger(event);
            if (event.result === false) {
                return;
            }

            $form.submit();

            $grid.trigger(gridEvents.afterFilter);
        },

        setSelectionColumn: function (options) {
            var $grid = $(this);
            var id = $(this).attr('id');
            if (gridData.id === undefined) {
                gridData[id] = {};
            }
            gridData[id].selectionColumn = options.name;
            if (!options.multiple || !options.checkAll) {
                return;
            }
            var checkAll = "#" + id + " input[name='" + options.checkAll + "']";
            var inputs = options.class ? "input." + options.class : "input[name='" + options.name + "']";
            var inputsEnabled = "#" + id + " " + inputs + ":enabled";
            $(document).off('click.kantGridView', checkAll).on('click.kantGridView', checkAll, function () {
                $grid.find(inputs + ":enabled").prop('checked', this.checked);
            });
            $(document).off('click.kantGridView', inputsEnabled).on('click.kantGridView', inputsEnabled, function () {
                var all = $grid.find(inputs).length == $grid.find(inputs + ":checked").length;
                $grid.find("input[name='" + options.checkAll + "']").prop('checked', all);
            });
        },

        getSelectedRows: function () {
            var $grid = $(this);
            var data = gridData[$grid.attr('id')];
            var keys = [];
            if (data.selectionColumn) {
                $grid.find("input[name='" + data.selectionColumn + "']:checked").each(function () {
                    keys.push($(this).parent().closest('tr').data('key'));
                });
            }
            return keys;
        },

        destroy: function () {
            return this.each(function () {
                $(window).unbind('.kantGridView');
                $(this).removeData('kantGridView');
            });
        },

        data: function () {
            var id = $(this).attr('id');
            return gridData[id];
        }
    };
})(window.jQuery);
