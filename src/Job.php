<?php declare(strict_types=1);
namespace Coveralls;

/**
 * Represents the coverage data from a single run of a test suite.
 */
class Job implements \JsonSerializable {

  /**
   * @var string The current SHA of the commit being built to override the `git` parameter.
   */
  private $commitSha = '';

  /**
   * @var GitData|null The Git data that can be used to display more information to users.
   */
  private $git;

  /**
   * @var bool Value indicating whether the build will not be considered done until a webhook has been sent to Coveralls.
   */
  private $isParallel = false;

  /**
   * @var string The secret token for the repository.
   */
  private $repoToken = '';

  /**
   * @var \DateTime|null The timestamp of when the job ran.
   */
  private $runAt;

  /**
   * @var string The unique identifier of the job on the CI service.
   */
  private $serviceJobId = '';

  /**
   * @var string The CI service or other environment in which the test suite was run.
   */
  private $serviceName = '';

  /**
   * @var string The build number.
   */
  private $serviceNumber = '';

  /**
   * @var string The associated pull request identifier of the build.
   */
  private $servicePullRequest = '';

  /**
   * @var \ArrayObject The list of source files.
   */
  private $sourceFiles;

  /**
   * Creates a new job.
   * @param SourceFile[] $sourceFiles The list of source files.
   */
  function __construct(array $sourceFiles = []) {
    $this->sourceFiles = new \ArrayObject($sourceFiles);
  }

  /**
   * Creates a new job from the specified JSON map.
   * @param object $map A JSON map representing a job.
   * @return static The instance corresponding to the specified JSON map.
   */
  static function fromJson(object $map): self {
    return (new static(isset($map->source_files) && is_array($map->source_files) ? array_map([SourceFile::class, 'fromJson'], $map->source_files) : []))
      ->setCommitSha(isset($map->commit_sha) && is_string($map->commit_sha) ? $map->commit_sha : '')
      ->setGit(isset($map->git) && is_object($map->git) ? GitData::fromJson($map->git) : null)
      ->setParallel(isset($map->parallel) && is_bool($map->parallel) ? $map->parallel : false)
      ->setRepoToken(isset($map->repo_token) && is_string($map->repo_token) ? $map->repo_token : '')
      ->setRunAt(isset($map->run_at) && is_string($map->run_at) ? new \DateTime($map->run_at) : null)
      ->setServiceJobId(isset($map->service_job_id) && is_string($map->service_job_id) ? $map->service_job_id : '')
      ->setServiceName(isset($map->service_name) && is_string($map->service_name) ? $map->service_name : '')
      ->setServiceNumber(isset($map->service_number) && is_string($map->service_number) ? $map->service_number : '')
      ->setServicePullRequest(isset($map->service_pull_request) && is_string($map->service_pull_request) ? $map->service_pull_request : '');
  }

  /**
   * Gets the current SHA of the commit being built to override the `git` parameter.
   * @return string The SHA of the commit being built.
   */
  function getCommitSha(): string {
    return $this->commitSha;
  }

  /**
   * Get the Git data that can be used to display more information to users.
   * @return GitData The Git data that can be used to display more information to users.
   */
  function getGit(): ?GitData {
    return $this->git;
  }

  /**
   * Gets the secret token for the repository.
   * @return string The secret token for the repository.
   */
  function getRepoToken(): string {
    return $this->repoToken;
  }

  /**
   * Gets the timestamp of when the job ran.
   * @return \DateTime|null The timestamp of when the job ran.
   */
  function getRunAt(): ?\DateTime {
    return $this->runAt;
  }

  /**
   * Gets the unique identifier of the job on the CI service.
   * @return string The unique identifier of the job on the CI service.
   */
  function getServiceJobId(): string {
    return $this->serviceJobId;
  }

  /**
   * Gets the CI service or other environment in which the test suite was run.
   * @return string The CI service or other environment in which the test suite was run.
   */
  function getServiceName(): string {
    return $this->serviceName;
  }

  /**
   * Gets the build number.
   * @return string The build number.
   */
  function getServiceNumber(): string {
    return $this->serviceNumber;
  }

  /**
   * Gets the associated pull request identifier of the build.
   * @return string The associated pull request identifier of the build.
   */
  function getServicePullRequest(): string {
    return $this->servicePullRequest;
  }

  /**
   * Gets the list of source files.
   * @return \ArrayObject The source files.
   */
  function getSourceFiles(): \ArrayObject {
    return $this->sourceFiles;
  }

  /**
   * Gets a value indicating whether the build will not be considered done until a webhook has been sent to Coveralls.
   * @return bool `true` if the build will not be considered done until a webhook has been sent to Coverall, otherwise `false`.
   */
  function isParallel(): bool {
    return $this->isParallel;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  function jsonSerialize(): \stdClass {
    $map = new \stdClass;

    if (mb_strlen($repoToken = $this->getRepoToken())) $map->repo_token = $repoToken;
    if (mb_strlen($serviceName = $this->getServiceName())) $map->service_name = $serviceName;
    if (mb_strlen($serviceNumber = $this->getServiceNumber())) $map->service_number = $serviceNumber;
    if (mb_strlen($serviceJobId = $this->getServiceJobId())) $map->service_job_id = $serviceJobId;
    if (mb_strlen($servicePullRequest = $this->getServicePullRequest())) $map->service_pull_request = $servicePullRequest;

    $map->source_files = array_map(function(SourceFile $item) {
      return $item->jsonSerialize();
    }, $this->getSourceFiles()->getArrayCopy());

    if ($this->isParallel()) $map->parallel = true;
    if ($git = $this->getGit()) $map->git = $git->jsonSerialize();
    if (mb_strlen($commitSha = $this->getCommitSha())) $map->commit_sha = $commitSha;
    if ($runAt = $this->getRunAt()) $map->run_at = $runAt->format('c');

    return $map;
  }

  /**
   * Sets the current SHA of the commit being built to override the `git` parameter.
   * @param string $value The new SHA of the commit being built.
   * @return $this This instance.
   */
  function setCommitSha(string $value): self {
    $this->commitSha = $value;
    return $this;
  }

  /**
   * Sets the Git data that can be used to display more information to users.
   * @param GitData|null $value The new Git data.
   * @return $this This instance.
   */
  function setGit(?GitData $value): self {
    $this->git = $value;
    return $this;
  }

  /**
   * Sets a value indicating whether the build will not be considered done until a webhook has been sent to Coveralls.
   * @param bool $value `true` if the build will not be considered done until a webhook has been sent to Coverall, otherwise `false`.
   * @return $this This instance.
   */
  function setParallel(bool $value): self {
    $this->isParallel = $value;
    return $this;
  }

  /**
   * Sets the secret token for the repository.
   * @param string $value The new secret token.
   * @return $this This instance.
   */
  function setRepoToken(string $value): self {
    $this->repoToken = $value;
    return $this;
  }

  /**
   * Sets the timestamp of when the job ran.
   * @param \DateTime|null $value The new timestamp.
   * @return $this This instance.
   */
  function setRunAt(?\DateTime $value): self {
    $this->runAt = $value;
    return $this;
  }

  /**
   * Gets the unique identifier of the job on the CI service.
   * @param string $value The new unique identifier of the job on the CI service.
   * @return $this This instance.
   */
  function setServiceJobId(string $value): self {
    $this->serviceJobId = $value;
    return $this;
  }

  /**
   * Gets the CI service or other environment in which the test suite was run.
   * @param string $value The new CI service in which the test suite was run.
   * @return $this This instance.
   */
  function setServiceName(string $value): self {
    $this->serviceName = $value;
    return $this;
  }

  /**
   * Gets the build number.
   * @param string $value The new build number.
   * @return $this This instance.
   */
  function setServiceNumber(string $value): self {
    $this->serviceNumber = $value;
    return $this;
  }

  /**
   * Gets the associated pull request identifier of the build.
   * @param string $value The new pull request identifier.
   * @return $this This instance.
   */
  function setServicePullRequest(string $value): self {
    $this->servicePullRequest = $value;
    return $this;
  }
}
