<?php

namespace Drupal\jsonapi_extras;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Simplifies the process of generating a JSON:API version of an entity.
 *
 * @api
 */
class EntityToJsonApi {

  /**
   * The HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * EntityToJsonApi constructor.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The HTTP kernel.
   */
  public function __construct(HttpKernelInterface $http_kernel) {
    $this->httpKernel = $http_kernel;
  }

  /**
   * Return the requested entity as a raw string.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to generate the JSON from.
   * @param string[] $includes
   *   The list of includes.
   *
   * @return string
   *   The raw JSON string of the requested resource.
   */
  public function serialize(EntityInterface $entity, array $includes = []) {
    $route_name = sprintf('jsonapi.%s--%s.individual', $entity->getEntityTypeId(), $entity->bundle());
    $jsonapi_url = Url::fromRoute($route_name, ['entity' => $entity->uuid()])->toString(TRUE)->getGeneratedUrl();
    $query = [];
    if ($includes) {
      $query = ['include' => implode(',', $includes)];
    }
    $request = Request::create($jsonapi_url, 'GET', $query);
    $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    return $response->getContent();
  }

  /**
   * Return the requested entity as an structured array.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to generate the JSON from.
   * @param string[] $includes
   *   The list of includes.
   *
   * @return array
   *   The JSON structure of the requested resource.
   */
  public function normalize(EntityInterface $entity, array $includes = []) {
    return Json::decode($this->serialize($entity, $includes));
  }

}
