<?php

use Kant\Filemanager\Utils;
use Kant\Helper\Url;
use Kant\Helper\Html;
use Kant\Kant;

$request = Kant::$app->request;
?>
<div class="container-fluid">
    <div class="navbar">
        <div class="navbar-inner">
            <div class="nav-collapse">
                <div class="filters">
                    <div class="row">
                        <div class="col-sm-4 col-xs-6">
                            <button class="btn btn-default upload-btn" data-toggle="tooltip"
                                    title="<?= $i18n::t('Upload') ?>">
                                <i class="glyphicon glyphicon-open"></i>
                            </button>
                            <button class="btn btn-default create-file-btn" data-toggle="tooltip"
                                    title="<?= $i18n::t('New File'); ?>">
                                <i class="glyphicon glyphicon-plus"></i><i class="glyphicon glyphicon-file"></i>
                            </button>
                            <button class="btn btn-default new-folder" data-toggle="tooltip"
                                    title="<?= $i18n::t('New Folder') ?>">
                                <i class="glyphicon glyphicon-plus"></i><i
                                        class="glyphicon glyphicon-folder-open"></i>
                            </button>
                            <button class="btn btn-default paste-here-btn" data-toggle="tooltip"
                                    title="<?= $i18n::t('Paste to this directory') ?>">
                                <i class="rficon-clipboard-apply"></i>
                            </button>
                            <button class="btn btn-default clear-clipboard-btn" data-toggle="tooltip"
                                    title="<?= $i18n::t('Clear Clipboard') ?>">
                                <i class="rficon-clipboard-clear"></i>
                            </button>
                        </div>
                        <div class="col-sm-3 col-xs-6 view-controller">
                            <button class="btn btn-default create-file-btn" data-toggle="tooltip" data-value="0"
                                    title="<?= $i18n::t('Box View') ?>">
                                <i class="glyphicon glyphicon-th"></i>
                            </button>
                            <button class="btn btn-default create-file-btn" data-toggle="tooltip" data-value="1"
                                    title="<?= $i18n::t('List View') ?>">
                                <i class="glyphicon glyphicon-align-justify"></i>
                            </button>
                            <button class="btn btn-default create-file-btn" data-toggle="tooltip" data-value="2"
                                    title="<?= $i18n::t('Columns List View') ?>">
                                <i class="glyphicon glyphicon-th-list"></i>
                            </button>
                        </div>
                        <div class="col-sm-5 col-xs-12 entire types">
                            <span><?= $i18n::t('Filters') ?>:</span>
                            <?php if($filterButtnsVisible): ?>
                            <button class="btn btn-default create-file-btn" data-toggle="tooltip"
                                    data-filter_type="file"
                                    data-target="<?= Url::current(['type' => 'file']) ?>"
                                    title="<?= $i18n::t('Files') ?>">
                                <i class="glyphicon glyphicon-file"></i>
                            </button>
                            <button class="btn btn-default create-file-btn" data-toggle="tooltip"
                                    data-filter_type="image"
                                    data-target="<?= Url::current(['type' => 'image']) ?>"
                                    title="<?= $i18n::t('Images') ?>">
                                <i class="glyphicon glyphicon-picture"></i>
                            </button>
                            <button class="btn btn-default create-file-btn" data-toggle="tooltip"
                                    data-filter_type="misc"
                                    data-target="<?= Url::current(['type' => 'misc']) ?>"
                                    title="<?= $i18n::t('Archives') ?>">
                                <i class="glyphicon glyphicon-inbox"></i>
                            </button>
                            <button class="btn btn-default create-file-btn" data-toggle="tooltip"
                                    data-filter_type="video"
                                    data-target="<?= Url::current(['type' => 'video']) ?>"
                                    title="<?= $i18n::t('Videos') ?>">
                                <i class="glyphicon glyphicon-facetime-video"></i>
                            </button>
                            <button class="btn btn-default create-file-btn" data-toggle="tooltip"
                                    data-filter_type="music"
                                    data-target="<?= Url::current(['type' => 'music']) ?>"
                                    title="<?= $i18n::t('Music') ?>">
                                <i class="glyphicon glyphicon-music"></i>
                            </button>
                            <?php endif; ?>

                            <input accesskey="f" class="filter-input " id="filter-input" name="filter"
                                   placeholder="<?= $i18n::t('Keyword') ?>" value="" type="text">

                            <?php if($filterButtnsVisible): ?>
                            <button class="btn btn-default select-type-all" data-toggle="tooltip" data-filter_type="all"
                                    title="<?= $i18n::t('All') ?>">
                                <i class="glyphicon glyphicon-remove"></i>
                            </button>
                            <?php endif; ?>
                            <input id="select-type-1" name="radio-sort" data-item="ff-item-type-1"
                                   checked="checked" class="hide" type="radio">
                            <input id="select-type-2" name="radio-sort" data-item="ff-item-type-2" class="hide"
                                   type="radio">
                            <input id="select-type-3" name="radio-sort" data-item="ff-item-type-3" class="hide"
                                   type="radio">
                            <input id="select-type-4" name="radio-sort" data-item="ff-item-type-4" class="hide"
                                   type="radio">
                            <input id="select-type-5" name="radio-sort" data-item="ff-item-type-5" class="hide"
                                   type="radio">
                            <input id="select-type-all" name="radio-sort" data-item="ff-item-type-all"
                                   class="hide" type="radio">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="nav-tools">
        <ul class="breadcrumb">
            <li class="pull-left">
                <a href="<?= Url::current(array_merge($request->query(), ['path' => "/"])) ?>"><i class="glyphicon glyphicon-home"></i></a>
            </li>
            <?= $nav; ?>

            <li class="pull-right">
                <a id="refresh" class="btn-sm" href="<?= Url::current(array_merge($request->query(), ['path' => $path])) ?>"><i
                            class="glyphicon glyphicon-refresh"></i></a>
            </li>

            <li class="pull-right">
                <div class="btn-group">
                    <a class="btn-sm dropdown-toggle sorting-btn" data-toggle="dropdown" href="#">
                        <i class="glyphicon glyphicon-signal"></i>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu pull-left sorting">
                        <li class="text-center"><strong><?= $i18n::t('Sorting'); ?></strong></li>
                        <li><a class="sorter sort-name descending" href="javascript:void('')"
                               data-sort="name"><?= $i18n::t('File Name'); ?></a></li>
                        <li><a class="sorter sort-date" href="javascript:void('')" data-sort="date"><?= $i18n::t('File Date'); ?></a></li>
                        <li><a class="sorter sort-size" href="javascript:void('')" data-sort="size"><?= $i18n::t('File Size'); ?></a></li>
                        <li><a class="sorter sort-extension " href="javascript:void('')" data-sort="extension"><?= $i18n::t('Filename'); ?>Type</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <small class="hidden-phone">(<span
                            id="files_number"><?= $currentFilesNumber ?></span> <?= $i18n::t('Files') ?> - <span
                            id="folders_number"><?= $currentFoldersNumber ?></span> <?= $i18n::t('Folders') ?>)
                </small>
            </li>
        </ul>
    </div>

    <div class="content" style="min-height:600px;">

        <div class="sorter-container list-view0">
            <div class="file-name"><a class="sorter sort-name descending" href="javascript:void('')" data-sort="name"><?= $i18n::t('File Name'); ?></a></div>
            <div class="file-date"><a class="sorter sort-date " href="javascript:void('')" data-sort="date"><?= $i18n::t('File Date'); ?></a></div>
            <div class="file-size"><a class="sorter sort-size " href="javascript:void('')" data-sort="size"><?= $i18n::t('File Size'); ?></a></div>
            <div class='img-dimension'>Dimension</div>
            <div class='file-extension'><a class="sorter sort-extension " href="javascript:void('')" data-sort="extension"><?= $i18n::t('File Type'); ?></a></div>
            <div class='file-operations'><?= $i18n::t('Operations'); ?></div>
        </div>

        <ul id="main-item-container" class="grid list-view0">
            <li data-name=".." class="back">
                <figure data-name=".." class="back-directory" data-type="">
                    <a class="folder-link" href="<?= Url::current(['path' => $back]) ?>">
                        <div class="img-precontainer">
                            <div class="img-container directory"><span></span>
                                <div class="directory-img"></div>
                            </div>
                        </div>
                        <div class="box no-effect">
                            <h4><?= $i18n::t('Back'); ?></h4>
                        </div>
                    </a>

                </figure>
            </li>
            <?php if (!empty($files)): foreach ($files as $k => $file): ?>
                <li class="file ui-draggable file-type-<?= $file['type'] ?>" data-name="<?= $file['file'] ?>">
                    <figure data-name="<?= $file['file'] ?>" data-type="file" class="">
                        <a class="link" href="<?php if($file['isDir']):?><?= Url::current(['path' => urldecode($file['relativePath'])]) ?><?php else: ?>javascript:void(0)<?php endif; ?>" alt="<?= $file['fileUrl'] ?>" data-file="<?= $file['fileUrl'] ?>"
                           data-apply="<?= $apply ?>">
                            <div class="img-precontainer">
                                <div class="filetype"><?= $file['extension'] ?></div>
                                <div class="img-container <?php if ($file['isDir']): ?>directory<?php endif; ?>">
                                    <img class="icon lazy-loaded" data-original="<?= $file['thumb'] ?>" >
                                </div>
                            </div>
                            <div class="img-precontainer-mini original-thumb">
                                <div class="filetype <?= $file['extension'] ?> hide"><?= $file['extension'] ?></div>
                                <div class="img-container-mini">
                                    <img class="lazy-loaded" data-original="<?= $file['thumb'] ?>"
                                         src="<?= $file['thumb'] ?>">
                                </div>
                            </div>
                            <div class="cover"></div>
                        </a>
                        <a class="link" href="javascript:void(0)" alt="<?= $file['fileUrl'] ?>" data-file="<?= $file['fileUrl'] ?>" data-apply="<?= $apply ?>">
                            <div class="box">
                                <h4 class="ellipsis"><?= basename($file['file'], "." . $file['extension']) ?></h4>
                            </div>
                        </a>
                        <div class="file-date"><?= date($i18n::t('Date Type'), $file['date']); ?></div>
                        <div class="file-size"><?= Utils::makeSize($file['size']) ?></div>
                        <div class="img-dimension"></div>
                        <div class="file-extension"><?= $file['extension'] ?></div>
                        <figcaption class="toolbar">
                            <a title="" class="tip-right" href="javascript:void('')" onclick="$('#form1').submit();"
                               data-original-title="Download">
                                <i class="glyphicon glyphicon-download"></i>
                            </a>
                            <a class="preview">
                                <i class="glyphicon glyphicon-eye-open icon-white"></i>
                            </a>
                            <a href="javascript:void('')" class="tip-left edit-button rename-file-paths rename-file"
                               title="" data-folder="0" data-permissions="" data-path="test/source.txt"
                               data-original-title="Rename">
                                <i class="glyphicon glyphicon-pencil"></i>
                            </a>
                            <a href="javascript:void('')" class="tip-left erase-button delete-file" title=""
                               data-confirm="Are you sure you want to delete this file?" data-path="test/source.txt"
                               data-original-title="Erase">
                                <i class="glyphicon glyphicon-trash"></i>
                            </a>
                        </figcaption>
                    </figure>
                </li>
            <?php endforeach; endif; ?>
        </ul>

    </div>
</div>


<!-- uploader div start -->
<div class="uploader">
    <div class="text-center">
        <button class="btn btn-primary close-uploader">
            <i class="glyphicon glyphicon-backward"></i> <?= $i18n::t('Return To Files List') ?>
        </button>
    </div>
    <div class="space10"></div>
    <div class="space10"></div>
    <div class="tabbable upload-tabbable"> <!-- Only required for left/right tabs -->
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab1" data-toggle="tab"><?= $i18n::t('Base Upload') ?></a></li>
            <li><a href="#taburl" data-toggle="tab"><?= $i18n::t('URL Upload') ?></a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab1">
                <form action="<?= $homeUrl . '?action=upload' ?>" method="post" enctype="multipart/form-data" id="kantDropzone"
                      class="dropzone">
                    <input type="hidden" name="path" id="cur_path" value="../source/test/"/>
                    <input type="hidden" name="path_thumb" value="../thumbs/test/"/>
                    <div class="fallback">
                        <h3>Upload:</h3><br/>
                        <input name="file" type="file"/>
                    </div>
                </form>
                <div class="upload-help">
                    <?= $i18n::t('Drag Drop Files Help') ?>
                </div>
            </div>
            <div class="tab-pane" id="taburl">
                <br/>
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="upload-url">URL</label>
                        <div class="col-sm-10">
                            <input type="text" id="upload-url" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-2">
                            <button class="btn btn-primary" id="uploadURL"><?= $i18n::t('Upload') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<div class="hidden-values">
    <?= Html::hiddenInput('popup', $request->get('popup'), ['id' => 'popup']); ?>
    <?= Html::hiddenInput('callback', $request->get('callback'), ['id' => 'callback']); ?>
    <?= Html::hiddenInput('fieldid', $request->get('fieldid'), ['id' => 'fieldid']); ?>
    <?= Html::hiddenInput('view', $request->get('view'), ['id' => 'view']); ?>
    <?= Html::hiddenInput('clipboard', $request->get('clipboard'), ['id' => 'clipboard']); ?>
</div>
<!-- uploader div end -->
<script type="text/javascript">
    var ClientOptions = <?= json_encode($clientOptions) ?>;
    var FileI18n = {
        "Copy": "<?= $i18n::t('Copy') ?>",
        "Cut": "<?= $i18n::t('Cut') ?>",
        "Duplicate": "<?= $i18n::t('Duplicate') ?>",
        "Extract": "<?= $i18n::t('Extract') ?>",
        "File Info": "<?= $i18n::t('File Info') ?>",
        "File Permission": "<?= $i18n::t('File Permission') ?>",
        "Paste Here": "<?= $i18n::t('Paste Here') ?>",
        "Show Url": "<?= $i18n::t('Show Url') ?>",
        "File Extension Is Not Allowed": "<?= $i18n::t('File Extension Is Not Allowed') ?>",
        "File Exceeds The Max Size Allowed": "<?= $i18n::t('File Exceeds The Max Size Allowed: {maxFileSize}MB', ['maxFileSize' => $clientOptions['maxFilesize']])?>",
        "Drop file here to upload": "<?= $i18n::t('Drop file here to upload') ?>",
        "SERVER ERROR": "<?= $i18n::t('SERVER ERROR') ?>"

    }
    var Exts = <?= json_encode($exts) ?>;

    FileManager.prototype.dropzone = function () {

        var allowedExtensions = this.options['allowedExtensions'];

        Dropzone.options.kantDropzone = {
            dictInvalidFileType: this.i18n['File Extension Is Not Allowed'],
            dictFileTooBig: this.i18n['File Exceeds The Max Size Allowed'],
            dictDefaultMessage: this.i18n['Drop file here to upload'],
            dictResponseError: this.i18n['SERVER ERROR'],
            paramName: "file",
            maxFilesize: this.options['maxFilesize'],
            headers: {"<?= $request::CSRF_HEADER ?>": "<?= $request->getCsrfToken() ?>"},
            url: "<?= $homeUrl . '?action=upload' ?>",
            accept: function (file, done) {
                var extension = file.name.split('.').pop();
                extension = extension.toLowerCase();
                if ($.inArray(extension, allowedExtensions) > -1) {
                    done();
                }
                else {
                    done(this.options.dictInvalidFileType);
                }
            }
        };

    }


    $(document).ready(function () {
        var fm = new FileManager(ClientOptions, FileI18n, Exts);
        fm.init();
        fm.dropzone();
    });


</script>