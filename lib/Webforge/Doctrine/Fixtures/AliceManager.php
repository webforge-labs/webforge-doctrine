<?php

namespace Webforge\Doctrine\Fixtures;

use Nelmio\Alice\Fixtures\Loader as AliceLoader;
use Symfony\Component\Console\Output\OutputInterface;

class AliceManager {

  private $loader;

  public static function createLoader(Array $providers) {
    $loader = new AliceLoader();
    foreach ($providers as $provider) {
      $loader->addProvider($provider);
    }
    return $loader;
  }

  public function __construct(AliceLoader $loader) {
    $this->loader = $loader;
  }

  public function loadFixtures(Array $files, $objectManager, OutputInterface $output, $purge = FALSE) {
    if ($purge) {
      $this->purge($objectManager, $output);
    }

    $persister = new \Nelmio\Alice\Persister\Doctrine($objectManager, $doFlush = FALSE);

    $output->writeln('persisting...');
    foreach ($files as $file) {
      $objects = $this->loader->load($file);
      $output->writeln('<comment>  file: '.$file.'</comment>');
      $persister->persist($objects);
    }

    $output->writeln('flushing...');
    $objectManager->flush();
    $output->writeln('<info>done.</info>');
  }

  private function purge($objectManager, OutputInterface $output) {
    $output->writeln('purging...');
    $connection = $objectManager->getConnection();
    $platform = $connection->getDatabasePlatform();
    $configuration = $objectManager->getConfiguration();

    $connection->executeQuery('set foreign_key_checks = 0');
    foreach ($objectManager->getMetadataFactory()->getAllMetadata() as $class) {
      if ($this->isTabledMetadata($class)) {
        continue;
      }

      $tbl = $this->getTableName($class, $platform, $configuration);
      $connection->executeUpdate($platform->getTruncateTableSQL($tbl, true));
    }
    $connection->executeQuery('set foreign_key_checks = 1');
  }

  private function isTabledMetadata($class) {
    if (isset($class->isEmbeddedClass) && $class->isEmbeddedClass) {
      return TRUE;
    }

    if ($class->isMappedSuperclass) {
      return TRUE;
    }
    
    if ($class->isInheritanceTypeSingleTable() && $class->name !== $class->rootEntityName) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   *
   * @param \Doctrine\ORM\Mapping\ClassMetadata $class
   * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
   * @return string
   */
  private function getTableName($class, $platform, $configuration) {
    if (isset($class->table['schema']) && !method_exists($class, 'getSchemaName')) {
      return $class->table['schema'].'.'.$configuration->getQuoteStrategy()->getTableName($class, $platform);
    }
    return $configuration->getQuoteStrategy()->getTableName($class, $platform);
  }
}
