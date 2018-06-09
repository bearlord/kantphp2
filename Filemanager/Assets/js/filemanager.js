"use strict";

var FileManager = function (options, i18n, exts) {
    this.options = options;
    this.i18n = i18n;
    this.exts = exts;
    this.popup = parseInt($("#popup").val());
    this.win = (1 === this.popup) ? (window.opener ? window.opener : window.parent) : window.parent;
    this.callback = $("#callback").val();
};

FileManager.prototype = {
    init: function () {
        this.lazyLoad();
        this.bindBoxNoEffect();
        this.bindViewController();
        this.initApply();
        this.makeContextMenu();
        this.makeUploader();
        this.makeSort();

    },
    setI18n: function (val) {
        this.i18n = val;
    },

    /**
     * Apply image to fieldid elements
     * @param String file_url
     * @param String fieldid
     * @returns 
     */
    applyImage: function (file_url, fieldid) {
        return this.applyTypedResources(file_url, fieldid);
    },

    /**
     * Apply image to fieldid elements
     * @param String file_url
     * @param String fieldid
     * @returns 
     */
    applyMedia: function (file_url, fieldid) {
        return this.applyTypedResources(file_url, fieldid);
    },

    /**
     * Apply files to fieldid elements
     * @param String file_url
     * @param String fieldid
     * @returns 
     */
    applyFiles: function (file_url, fieldid) {
        return this.applyTypedResources(file_url, fieldid);
    },

    /**
     * Apply typed resoures to fieldid elements
     * @param String file_url
     * @param String fieldid
     * @returns 
     */
    applyTypedResources: function (file_url, fieldid) {
        if (fieldid) {
            $("#" + fieldid, this.win.document).val(file_url).trigger('change');
            !!this.callback ? ((!!this.win[this.callback] && "function" === typeof this.win[this.callback]) && this.win[this.callback](file_url, fieldid)) 
                            : ((!!this.win['filemanager_callback'] && "function" === typeof this.win['filemanager_callback']) && this.win['filemanager_callback'][file_url, fieldid]);
            this.close();
            return;
        }
        this.applyAny(file_url);
        return true;
    },

    /**
     * Apply all typed resoures to fieldid elements
     * @param String file_url
     * @param String fieldid
     * @returns 
     */
    applyAll: function (file_url, fieldid) {
        var name = file_url.substr(file_url.lastIndexOf("/") + 1);
        var ext = file_url.split(".").pop();
        var html = "";
        if ($.inArray(ext, this.exts['image']) >= 0) {
            html = '<img src="' + file_url + '" alt="' + name + '" />';
        } else if ($.inArray(ext, ["ogg", "mp3", "wav"]) >= 0) {
            html = '<audio controls src="' + file_url + '" type="audio/' + ext + '">' + name + "</audio>";
        } else if ($.inArray(ext, ["mp4", "ogg", "webm"]) >= 0) {
            html = '<video controls source src="' + file_url + '" type="video/' + ext + '">' + name + "</video>"
        } else {
            html = '<a href="' + file_url + '" title="' + name + '" target="_blank">' + name + "</a>"
        }
        if (parent.tinymce.majorVersion < 4) {
            return false;
        }
        parent.tinymce.activeEditor.insertContent(html);
        parent.tinymce.activeEditor.windowManager.close();
        return true;
    },

    /**
     * Apply elements to editor
     * @param {String} file_url
     * @returns {Boolean}
     */
    applyAny: function (file_url) {
        if (parent.tinymce) {
            if (parent.tinymce.majorVersion < 4) {
                return false;
            }
            if (parent.tinymce.activeEditor.windowManager.getParams()) {
                parent.tinymce.activeEditor.windowManager.getParams().setUrl(file_url);
                parent.tinymce.activeEditor.windowManager.close();
            }
        }
    },

    /**
     * Apply file url to tinymce
     */
    initApply: function () {
        var $this = this;
        $(document).on('click', 'a.link', function () {
            var apply = $(this).data('apply');
            if (!!apply) {
                var file_url = $(this).data('file');
                var fieldid = $("#fieldid").val();
                $.proxy($this, apply, file_url, fieldid)();
            }

        });
    },

    /**
     * Bind box no-effect
     */
    bindBoxNoEffect: function () {
        $("figure").on("mouseover", function () {
            0 == $("#view").val() && $("#main-item-container").hasClass("no-effect-slide") === !1 && $(this).find(".box:not(.no-effect)").animate({
                top: "-26px"
            },
                    {
                        queue: !1,
                        duration: 300
                    });
        }).on("mouseout", function () {
            $(this).find(".box:not(.no-effect)").animate({
                top: "0px"
            },
                    {
                        queue: !1,
                        duration: 300
                    });
        });
    },

    /**
     * Bind view-controller
     */
    bindViewController: function () {
        var that = this;
        $(".view-controller button").on("click",
                function () {
                    $(".view-controller button").removeClass("btn-inverse");
                    $(this).addClass("btn-inverse");
                    $.ajax({
                        url: "ajax_calls.php?action=view&type=" + $(this).attr("data-value")
                    });
                    "undefined" != typeof $("ul.grid")[0] && $("ul.grid")[0] && ($("ul.grid")[0].className = $("ul.grid")[0].className.replace(/\blist-view.*?\b/g, "")),
                            "undefined" != typeof $(".sorter-container")[0] && $(".sorter-container")[0] && ($(".sorter-container")[0].className = $(".sorter-container")[0].className.replace(/\blist-view.*?\b/g, ""));
                    var value = $(this).attr("data-value");
                    $("#view").val(value);
                    $("ul.grid").addClass("list-view" + value);
                    $(".sorter-container").addClass("list-view" + value);
                    $(this).data('value') >= 1 ? that.reRender(14) : ($("ul.grid li").css("width", 124), $("ul.grid figure").css("width", 122));
                    $(".lazy-loaded").lazyload();
                });
    },

    /**
     * Make sort
     */
    makeSort: function () {
        $("[data-filter_type]").on("click",
                function () {
                    $(".filters button").removeClass("btn-inverse");
                    $(this).addClass("btn-inverse");
                    var filter_type = $(this).data("filter_type");
                    if (filter_type === 'all') {
                        $(".grid li").show(300);
                    } else {
                        $(".grid li").not(".file-type-" + filter_type).hide(300);
                        $(".file-type-" + filter_type).show(300);
                    }
                });
        return;

    },

    /**
     * Make uploader
     */
    makeUploader: function () {
        $(".upload-btn").on("click",
                function () {
                    $(".uploader").show(500)
                });

        $(".close-uploader").on("click",
                function () {
                    $(".uploader").hide(500);
                    setTimeout(function () {
                        var operator = "?";
                        if (window.location.search.length > 0) {
                            operator = "&";
                        }
                        window.location.href = $("#refresh").attr("href") + operator + (new Date()).getTime();
                    },
                            420);
                });
    },

    /**
     * Context actions
     */
    contextActions: {},
    makeContextMenu: function () {
        var _this = this;
        $.contextMenu({
            selector: "figure:not(.back-directory), .list-view2 figure:not(.back-directory)",
            autoHide: !0,
            build: function (e) {
                e.addClass("selected");
                var t = {
                    callback: function (r, t) {
                        _this.contextActions[r](e)
                    },
                    items: {}
                };
                return (e.find(".img-precontainer-mini .filetype").hasClass("jpeg")),
                        e.hasClass("directory") && 0 != $("#type_param").val() && (t.items.select = {
                    name: _this.i18n['Select'],
                    icon: "",
                    disabled: !1
                }),
                        t.items.copy_url = {
                            name: _this.i18n['Show Url'],
                            icon: "url",
                            disabled: !1
                        },
                        (e.find(".img-precontainer-mini .filetype").hasClass("zip") || e.find(".img-precontainer-mini .filetype").hasClass("tar") || e.find(".img-precontainer-mini .filetype").hasClass("gz")) && (t.items.unzip = {
                    name: _this.i18n['Extract'],
                    icon: "extract",
                    disabled: !1
                }),
                        e.find(".img-precontainer-mini .filetype").hasClass("edit-text-file-allowed") && (t.items.edit_text_file = {
                    name: $("#lang_edit_file").val(),
                    icon: "edit",
                    disabled: !1
                }),
                        e.hasClass("directory") || (t.items.duplicate = {
                    name: _this.i18n['Duplicate'],
                    icon: "duplicate",
                    disabled: !1
                }),
                        e.hasClass("directory") || 1 != ClientOptions['copyCutFilesAllowed'] ? e.hasClass("directory") && 1 == ClientOptions['copyCutDirsAllowed'] && (t.items.copy = {
                    name: _this.i18n['Copy'],
                    icon: "copy",
                    disabled: !1
                },
                        t.items.cut = {
                            name: _this.i18n['Cut'],
                            icon: "cut",
                            disabled: !1
                        }) : (t.items.copy = {
                    name: _this.i18n['Copy'],
                    icon: "copy",
                    disabled: !1
                },
                        t.items.cut = {
                            name: _this.i18n['Cut'],
                            icon: "cut",
                            disabled: !1
                        }),
                        0 == $("#clipboard").val() || e.hasClass("directory") || (t.items.paste = {
                    name: this.i18n['Paste Here'],
                    icon: "clipboard-apply",
                    disabled: !1
                }),
                        e.hasClass("directory") || 1 != ClientOptions['chmodFilesAllowed'] ? e.hasClass("directory") && 1 == ClientOptions['chmodDirsAllowed'] && (t.items.chmod = {
                    name: _this.i18n['File Permission'],
                    icon: "key",
                    disabled: !1
                }) : t.items.chmod = {
                    name: _this.i18n['File Permission'],
                    icon: "key",
                    disabled: !1
                },
                        t.items.sep = "----",
                        t.items.info = {
                            name: "<strong>" + _this.i18n['File Info'] + "</strong>",
                            disabled: !0
                        },
                        t.items.name = {
                            name: e.attr("data-name"),
                            icon: "label",
                            disabled: !0
                        },
                        "img" == e.attr("data-type") && (t.items.dimension = {
                    name: e.find(".img-dimension").html(),
                    icon: "dimension",
                    disabled: !0
                }),
                        "true" !== $("#show_folder_size").val() && "true" !== $("#show_folder_size").val() || (e.hasClass("directory") ? t.items.size = {
                    name: e.find(".file-size").html() + " - " + e.find(".nfiles").val() + " " + $("#lang_files").val() + " - " + e.find(".nfolders").val() + " " + $("#lang_folders").val(),
                    icon: "size",
                    disabled: !0
                } : t.items.size = {
                    name: e.find(".file-size").html(),
                    icon: "size",
                    disabled: !0
                }),
                        t.items.date = {
                            name: e.find(".file-date").html(),
                            icon: "date",
                            disabled: !0
                        },
                        t
            },
            events: {
                hide: function () {
                    $("figure").removeClass("selected")
                }
            }
        });
        $(document).on("contextmenu",
                function (e) {
                    if (!$(e.target).is("figure")) {
                        return !1
                    }
                });
    },
    reRender: function (pos) {
        var width = $(".breadcrumb").width() + pos,
                view = parseInt($("#view").val());
        if ($(".uploader").css("width", width), view > 0) {
            if (1 === view) {
                $("ul.grid li, ul.grid figure").css("width", "100%");
            } else {
                var i = Math.floor(width / 380);
                0 === i && (i = 1, $("h4").css("font-size", 12));
                width = Math.floor(width / i - 3);
                $("ul.grid li, ul.grid figure").css("width", width);
            }
        }
    },
    close: function () {
        if (1 === parseInt(jQuery("#popup").val())) {
            window.close();
        }

        if ("function" === typeof parent.jQuery(".modal").modal) {
            parent.jQuery(".modal").modal("hide");
        }

        if ("undefined" !== typeof parent.jQuery && parent.jQuery) {
            if ("object" === typeof parent.jQuery.fancybox) {
                parent.jQuery.fancybox.close();
            } else if ("object" === typeof parent.$.fancybox) {
                parent.$.fancybox.close();
            }
        }

    },

    //lazy load
    lazyLoad: function () {
        $(".lazy-loaded").lazyload();
    }

};