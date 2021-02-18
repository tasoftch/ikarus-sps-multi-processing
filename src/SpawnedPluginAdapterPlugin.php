<?php
/**
 * BSD 3-New License
 *
 * Copyright (c) 2020, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Ikarus\SPS;

use Ikarus\SPS\Plugin\PluginInterface;
use Ikarus\SPS\Plugin\SetupPluginInterface;
use Ikarus\SPS\Plugin\TearDownPluginInterface;
use Ikarus\SPS\Register\MemoryRegisterInterface;

class SpawnedPluginAdapterPlugin extends AbstractSpawnedEngineSimulationPlugin implements SetupPluginInterface, TearDownPluginInterface, SpawnInfoInterface
{
	/** @var PluginInterface */
	private $plugin;

	public function __construct(PluginInterface $plugin, MemoryRegisterInterface $memoryRegister = NULL, int $frequency = 0, string $identifier = NULL, string $domain = NULL)
	{
		parent::__construct($frequency, $memoryRegister, $identifier, $domain);
		$this->plugin = $plugin;
	}

	/**
	 * @param PluginInterface $plugin
	 * @return static
	 */
	public function setPlugin(PluginInterface $plugin)
	{
		$this->plugin = $plugin;
		return $this;
	}

	/**
	 * @return PluginInterface
	 */
	public function getPlugin(): PluginInterface
	{
		return $this->plugin;
	}

	/**
	 * @inheritDoc
	 */
	public function update(MemoryRegisterInterface $pluginManagement)
	{
		if($this->isChildProcess()) {
			$pluginManagement->beginCycle();
			$this->getPlugin()->update($pluginManagement);
			$pluginManagement->endCycle();
		}
	}

    /**
     * @inheritDoc
     */
	public function setEngine(?EngineInterface $engine): void
	{
		parent::setEngine($engine);
		if($this->plugin instanceof EngineDependencyInterface)
			$this->plugin->setEngine($engine);
	}

    /**
     * @inheritDoc
     */
	public function setup()
	{
		if($this->plugin instanceof SetupPluginInterface)
			$this->plugin->setup();
		
		parent::setup();
	}

    /**
     * @inheritDoc
     */
	public function tearDown()
	{
		if($this->plugin instanceof TearDownPluginInterface)
			$this->plugin->tearDown();

		parent::tearDown();
	}

    /**
     * @inheritDoc
     */
	public function initialize(MemoryRegisterInterface $memoryRegister)
	{
		$this->plugin->initialize($memoryRegister);
	}

    /**
     * @inheritDoc
     */
    public function processWillSpawn()
    {
        if($this->plugin instanceof SpawnInfoInterface)
            $this->plugin->processWillSpawn();
    }

    /**
     * @inheritDoc
     */
    public function mainProcessDidSpawn()
    {
        if($this->plugin instanceof SpawnInfoInterface)
            $this->plugin->mainProcessDidSpawn();
    }

    /**
     * @inheritDoc
     */
    public function childProcessDidSpawn()
    {
        if($this->plugin instanceof SpawnInfoInterface)
            $this->plugin->childProcessDidSpawn();
    }
}