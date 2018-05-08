<?php

/*
 * This file is part of the Fidry\AliceBundleExtension package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceBundleExtension\Context;

use Behat\Gherkin\Node\TableNode;
use Nelmio\Alice\PersisterInterface;

interface AliceContextInterface
{
    public function createSchema();

    public function dropSchema();

    public function emptyDatabase();

    /**
     * @param string             $fixturesFile Path to the fixtures
     * @param PersisterInterface $persister
     */
    public function thereAreFixtures($fixturesFile, $persister = null);

    /**
     * @param TableNode          $fixturesFiles Path to the fixtures
     * @param PersisterInterface $persister
     */
    public function thereAreSeveralFixtures(TableNode $fixturesFiles, $persister = null);

    /**
     * @param string $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath);

    /**
     * @return string
     */
    public function getBasePath();
}
