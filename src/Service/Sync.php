<?php

namespace Drupal\remote_config_sync\Service;

use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class Sync.
 */
class Sync {

  use StringTranslationTrait;

  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * The target storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $targetStorage;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Sync constructor.
   *
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   * @param \Drupal\Core\Config\StorageInterface $target_storage
   *   The target storage.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(ConfigManagerInterface $config_manager, StorageInterface $target_storage, FileSystemInterface $file_system) {
    $this->configManager = $config_manager;
    $this->targetStorage = $target_storage;
    $this->fileSystem = $file_system;
  }

  /**
   * Push the configuration to a remote site.
   *
   * @param string $remote
   *   The remote.
   * @param bool $import
   *   The import flag.
   *
   * @return array
   *   The push status.
   */
  public function push($remote, $import = FALSE) {
    $remote = explode('|', $remote);
    $remote_url = $remote[0];
    $remote_token = $remote[1];

    if ($this->exportConfig()) {
      return $this->uploadFile($remote_url, $remote_token, $import);
    }

    return [
      'status' => 'error',
      'message' => $this->t('Error while exporting the configuration.'),
    ];
  }

  /**
   * Export the configuration to a .tar.gz archive file.
   *
   * @return bool
   *   The export result.
   */
  protected function exportConfig() {
    $this->fileSystem->delete($this->fileSystem->getTempDirectory() . '/remote_config_sync.tar.gz');
    $archiver = new ArchiveTar($this->fileSystem->getTempDirectory() . '/remote_config_sync.tar.gz', 'gz');

    // Get raw configuration data without overrides.
    foreach ($this->configManager->getConfigFactory()->listAll() as $name) {
      if ($name == 'remote_config_sync.settings') {
        continue;
      }
      $archiver->addString("$name.yml", Yaml::encode(
        $this->configManager->getConfigFactory()->get($name)->getRawData()
      ));
    }

    // Get all override data from the remaining collections.
    foreach ($this->targetStorage->getAllCollectionNames() as $collection) {
      $collection_storage = $this->targetStorage->createCollection($collection);
      foreach ($collection_storage->listAll() as $name) {
        $archiver->addString(str_replace('.', '/', $collection) . "/$name.yml", Yaml::encode(
          $collection_storage->read($name)
        ));
      }
    }

    if (file_exists($this->fileSystem->getTempDirectory() . '/remote_config_sync.tar.gz')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Upload the configuration archive file to a remote site.
   *
   * @param string $remote_url
   *   The remote URL.
   * @param string $remote_token
   *   The remote token.
   * @param bool $import
   *   The import flag.
   *
   * @return array
   *   The upload status.
   */
  protected function uploadFile($remote_url, $remote_token, $import) {
    $file_path = $this->fileSystem->getTempDirectory() . '/remote_config_sync.tar.gz';
    $hash = hash_file('md5', $file_path);

    try {
      $client = new Client();
      $response = $client->post(rtrim($remote_url, '/') . '/api/v1/remote-config-sync', [
        'headers' => [
          'token' => $remote_token,
          'hash' => $hash,
          'import' => $import,
        ],
        'body' => file_get_contents($this->fileSystem->getTempDirectory() . '/remote_config_sync.tar.gz'),
      ]);
      $response_contents = json_decode($response->getBody()->getContents(), TRUE);
      return [
        'status' => $response_contents['status'],
        'message' => $response_contents['message'],
        'host' => $response_contents['host'],
      ];
    }
    catch (RequestException $e) {
      return [
        'status' => 'error',
        'message' => $this->t('Error while pushing the configuration: @error', ['@error' => $e->getMessage()]),
      ];
    }
  }

}
