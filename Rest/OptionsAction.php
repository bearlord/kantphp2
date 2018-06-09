<?php
/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Rest;

use Kant\Kant;

/**
 * OptionsAction responds to the OPTIONS request by sending back an `Allow` header.
 *
 * For more details and usage information on OptionsAction, see the [guide article on rest controllers](guide:rest-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class OptionsAction extends \Kant\Action\Action
{

    /**
     *
     * @var array the HTTP verbs that are supported by the collection URL
     */
    public $collectionOptions = [
        'GET',
        'POST',
        'HEAD',
        'OPTIONS'
    ];

    /**
     *
     * @var array the HTTP verbs that are supported by the resource URL
     */
    public $resourceOptions = [
        'GET',
        'PUT',
        'PATCH',
        'DELETE',
        'HEAD',
        'OPTIONS'
    ];

    /**
     * Responds to the OPTIONS request.
     * 
     * @param string $id            
     */
    public function run($id = null)
    {
        if (Kant::$app->getRequest()->getMethod() !== 'OPTIONS') {
            Kant::$app->getResponse()->setStatusCode(405);
        }
        $options = $id === null ? $this->collectionOptions : $this->resourceOptions;
        Kant::$app->getResponse()
            ->getHeaders()
            ->set('Allow', implode(', ', $options));
    }
}
