<?php declare(strict_types=1);
namespace Coveralls\Http;

use League\Event\{AbstractEvent};
use Psr\Http\Message\{RequestInterface};

/**
 * Represents the event parameter used for request events.
 */
class RequestEvent extends AbstractEvent {

  /**
   * @var RequestInterface The related HTTP request.
   */
  private $request;

  /**
   * Creates a new event parameter.
   * @param RequestInterface $request The related HTTP request.
   */
  function __construct(RequestInterface $request) {
    $this->request = $request;
  }

  /**
   * Gets the related HTTP request.
   * @return RequestInterface The related HTTP request.
   */
  function getRequest(): RequestInterface {
    return $this->request;
  }
}
