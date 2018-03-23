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

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Context to load fixtures files with Alice loader.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class AlicePHPCRContext extends AbstractAliceContext
{
    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->init(
            $kernel,
            $kernel->getContainer()->get('fidry_alice_data_fixtures.loader.doctrine_phpcr')
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createSchema()
    {
        // TODO: Implement createDatabase() method.
    }

    /**
     * {@inheritdoc}
     */
    public function dropSchema()
    {
        // TODO: Implement dropDatabase() method.
    }

    /**
     * {@inheritdoc}
     */
    public function emptyDatabase()
    {
        // TODO: Implement emptyDatabase() method.
    }
}
