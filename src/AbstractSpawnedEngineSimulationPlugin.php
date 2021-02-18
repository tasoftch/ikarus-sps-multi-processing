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

use Ikarus\SPS\Register\MemoryRegisterInterface;

/**
 * The engine simulation plugin spawns the sps process and invoke its own update method in a separate process.
 *
 * @package Ikarus\SPS
 */
abstract class AbstractSpawnedEngineSimulationPlugin extends AbstractSpawnedPlugin implements EngineDependencyInterface
{
	/** @var EngineInterface */
	private $engine;
	private $interval;
	/** @var MemoryRegisterInterface */
	private $memoryRegister;

	/**
	 * AbstractSpawnedEngineSimulationPlugin constructor.
	 * @param int $interval in milliseconds
	 * @param MemoryRegisterInterface|null $register
	 * @param string|null $identifier
	 * @param string|null $domain
	 */
	public function __construct(int $interval = 0, MemoryRegisterInterface $register = NULL, string $identifier = NULL, string $domain = NULL)
	{
		parent::__construct($identifier, $domain);
		$this->memoryRegister = $register;
		$this->interval = $interval;
	}

	/**
	 * @param MemoryRegisterInterface $memoryRegister
	 * @return static
	 */
	public function setMemoryRegister(MemoryRegisterInterface $memoryRegister)
	{
		$this->memoryRegister = $memoryRegister;
		return $this;
	}

	/**
	 * @return MemoryRegisterInterface
	 */
	public function getMemoryRegister(): ?MemoryRegisterInterface
	{
		return $this->memoryRegister;
	}

	protected function spawn()
	{
		$management = $this->getMemoryRegister() ?: $this->getEngine()->getMemoryRegister();
		/** @var CyclicEngine $engine */
		$engine = $this->getEngine();
		$intv = ($this->getInterval() ?: $engine->getInterval());
		while (1) {
			$this->update($management);
			usleep( $intv * 1e3 );
		}
	}


	/**
	 * @return EngineInterface
	 */
	public function getEngine(): EngineInterface
	{
		return $this->engine;
	}

	/**
	 * @param EngineInterface|null $engine
	 * @return static
	 */
	public function setEngine(?EngineInterface $engine)
	{
		$this->engine = $engine;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getInterval(): int
	{
		return $this->interval;
	}
}