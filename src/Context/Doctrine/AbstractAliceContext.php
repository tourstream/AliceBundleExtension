<?php

/*
 * This file is part of the Fidry\AliceBundleExtension package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceBundleExtension\Context\Doctrine;

use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\Common\Persistence\ObjectManager;
use Fidry\AliceBundleExtension\Context\AliceContextInterface;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterAwareInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Context to load fixtures files with Alice loader.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
abstract class AbstractAliceContext implements KernelAwareContext, AliceContextInterface
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string[]
     */
    protected $classes;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @param string|null $basePath
     */
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath;
    }

    /**
     * @param KernelInterface                  $kernel
     * @param LoaderInterface                  $loader
     * @param string                           $basePath
     */
    final public function init(
        KernelInterface $kernel,
        LoaderInterface $loader,
        $basePath = null
    ) {
        $this->kernel = $kernel;
        $this->loader = $loader;

        if (null !== $basePath) {
            $this->basePath = $basePath;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    abstract public function setKernel(KernelInterface $kernel);

    /**
     * {@inheritdoc}
     */
    final public function setBasePath($basePath)
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @Transform /^service$/
     *
     * @param string $serviceId
     *
     * @return object
     *
     * @throws ServiceNotFoundException
     */
    public function castServiceIdToService(string $serviceId)
    {
        return $this->kernel->getContainer()->get($serviceId);
    }

    /**
     * @Transform /^persister$/
     *
     * @param string $serviceId
     *
     * @return PersisterInterface
     *
     * @throws ServiceNotFoundException
     */
    public function castServiceIdToPersister(string $serviceId)
    {
        $service = $this->castServiceIdToService($serviceId);

        return $this->resolvePersister($service);
    }

    /**
     * {@inheritdoc}
     */
    public function thereAreFixtures($fixturesFile, $persister = null)
    {
        $this->loadFixtures([$fixturesFile], $persister);
    }

    /**
     * {@inheritdoc}
     */
    public function thereAreSeveralFixtures(TableNode $fixturesFileRows, $persister = null)
    {
        $fixturesFiles = [];

        foreach ($fixturesFileRows->getRows() as $fixturesFileRow) {
            $fixturesFiles[] = $fixturesFileRow[0];
        }

        $this->loadFixtures($fixturesFiles, $persister);
    }

    /**
     * @param array              $fixturesFiles
     * @param PersisterInterface $persister
     */
    private function loadFixtures($fixturesFiles, $persister = null)
    {
        if (true === is_string($persister) && $this->loader instanceof PersisterAwareInterface) {
            $persister = $this->castServiceIdToPersister($persister);
            $this->loader->withPersister($persister);
        }

        $fixtureBundles = [];
        $fixtureDirectories = [];

        foreach ($fixturesFiles as $key => $fixturesFile) {
            if (0 === strpos($fixturesFile, '/')) {
                if (is_dir($fixturesFile)) {
                    $fixtureDirectories[] = $fixturesFile;
                    unset($fixturesFiles[$key]);
                }

                continue;
            }

            if (0 === strpos($fixturesFile, '@')) {
                if (false === strpos($fixturesFile, '.')) {
                    $fixtureBundles[] = $this->kernel->getBundle(substr($fixturesFile, 1));
                    unset($fixturesFiles[$key]);
                }

                continue;
            }

            $fixturesFiles[$key] = sprintf('%s/%s', $this->basePath, $fixturesFile);
        }

        /**
        if (false === empty($fixtureBundles)) {
            $fixturesFiles = array_merge(
                $fixturesFiles,
                $this->fixturesFinder->getFixtures($this->kernel, $fixtureBundles, $this->kernel->getEnvironment())
            );
        }

        if (false === empty($fixtureDirectories)) {
            $fixturesFiles = array_merge(
                $fixturesFiles,
                $this->fixturesFinder->getFixturesFromDirectory($fixtureDirectories)
            );
        } **/

        $this->loader->load( $fixturesFiles);
        //$this->loader->load( $this->fixturesFinder->resolveFixtures($this->kernel, $fixturesFiles));
    }

    /**
     * @param ObjectManager|PersisterInterface $persister
     *
     * @return PersisterInterface
     *
     * @throws \InvalidArgumentException
     */
    final protected function resolvePersister($persister)
    {
        switch (true) {
            case $persister instanceof PersisterInterface:
                return $persister;
            case $persister instanceof ObjectManager:
                return new ObjectManagerPersister($persister);

            default:
                throw new \InvalidArgumentException(sprintf(
                    'Invalid persister type, expected %s or %s. Got %s instead.',
                    PersisterInterface::class,
                    ObjectManager::class,
                    get_class($persister)
                ));
        }
    }
}
