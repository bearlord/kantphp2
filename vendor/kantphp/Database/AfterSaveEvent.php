<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Database;

use Kant\Foundation\Event;

/**
 * AfterSaveEvent represents the information available in [[ActiveRecord::EVENT_AFTER_INSERT]] and [[ActiveRecord::EVENT_AFTER_UPDATE]].
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class AfterSaveEvent extends Event
{

    /**
     *
     * @var array The attribute values that had changed and were saved.
     */
    public $changedAttributes;
}
