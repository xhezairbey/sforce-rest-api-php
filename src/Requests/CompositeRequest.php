<?php declare(strict_types=1);

namespace Xhezairi\SForce\Requests;

/**
 * Class CompositeRequest
 * @package Xhezairi\SForce\Requests
 */
final class CompositeRequest extends AbstractRequest
{
    /**
     * Call CompositeRequest::createBuilder instead of this constructor.
     *
     * @param  CompositeRequestBuilder  $builder
     */
    public function __construct(CompositeRequestBuilder $builder)
    {
        $this->data = $builder->getData();
    }

    /**
     * @param  bool  $allOrNone
     * @return CompositeRequestBuilder
     */
    public static function createBuilder(bool $allOrNone = true)
    {
        return new CompositeRequestBuilder($allOrNone);
    }
}
