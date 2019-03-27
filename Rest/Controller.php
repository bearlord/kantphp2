<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace Kant\Rest;

use Kant\Kant;
use Kant\Filter\Auth\CompositeAuth;
use Kant\Filter\ContentNegotiator;
use Kant\Filter\RateLimiter;
use Kant\Http\Response;
use Kant\Filter\VerbFilter;

/**
 * Controller is the base class for RESTful API controller classes.
 *
 * Controller implements the following steps in a RESTful API request handling cycle:
 *
 * 1. Resolving response format (see [[ContentNegotiator]]);
 * 2. Validating request method (see [[verbs()]]).
 * 3. Authenticating user (see [[\yii\filters\auth\AuthInterface]]);
 * 4. Rate limiting (see [[RateLimiter]]);
 * 5. Formatting response data (see [[serializeData()]]).
 *
 * For more details and usage information on Controller, see the [guide article on rest controllers](guide:rest-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \Kant\Controller\Controller
{

    /**
     *
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'Kant\Rest\Serializer';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML
                ]
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs()
            ],
            'authenticator' => [
                'class' => CompositeAuth::className()
            ],
            'rateLimiter' => [
                'class' => RateLimiter::className()
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterActions($action, $result)
    {
        $result = parent::afterActions($action, $result);
        return $this->serializeData($result);
    }

    /**
     * Declares the allowed HTTP verbs.
     * Please refer to [[VerbFilter::actions]] on how to declare the allowed verbs.
     * 
     * @return array the allowed HTTP verbs.
     */
    protected function verbs()
    {
        return [];
    }

    /**
     * Serializes the specified data.
     * The default implementation will create a serializer based on the configuration given by [[serializer]].
     * It then uses the serializer to serialize the given data.
     * 
     * @param mixed $data
     *            the data to be serialized
     * @return mixed the serialized data.
     */
    protected function serializeData($data)
    {
        return Kant::createObject($this->serializer)->serialize($data);
    }
}