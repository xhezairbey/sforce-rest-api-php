<?php declare(strict_types=1);

namespace Xhezairi\SForce\Requests;

class CompositeRequestBuilder
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var int $count
     */
    private $count;

    /**
     * Call CompositeRequest::createBuilder over this constructor.
     *
     * @param  bool  $value
     */
    public function __construct(bool $value)
    {
        $this->data['allOrNone'] = $value;
    }

    public function addSubRequest()
    {
        $this->count = !isset($this->count) ? 0 : $this->count++;
        $this->data['compositeRequest'][$this->count] = [];

        return $this;
    }

    public function setReferenceId(string $referenceId)
    {
        $this->data['compositeRequest'][$this->count]['referenceId'] = $referenceId;

        return $this;
    }

    public function setMethod(string $method)
    {
        $this->data['compositeRequest'][$this->count]['method'] = $method;

        return $this;
    }

    public function setBody(array $body)
    {
        $this->data['compositeRequest'][$this->count]['body'] = $body;

        return $this;
    }

    public function setUrl(string $url)
    {
        $this->data['compositeRequest'][$this->count]['url'] = $url;

        return $this;
    }

    public function isAllOrNone(): bool
    {
        return $this->data['allOrNone'];
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @return CompositeRequest
     */
    public function build(): CompositeRequest
    {
        return new CompositeRequest($this);
    }
}
