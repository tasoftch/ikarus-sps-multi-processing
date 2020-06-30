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


use Ikarus\SPS\Exception\SPSException;
use Ikarus\SPS\Plugin\Cyclic\AbstractCyclicPlugin;
use Ikarus\SPS\Plugin\SetupPluginInterface;
use Ikarus\SPS\Plugin\TearDownPluginInterface;

abstract class AbstractSpawnedPlugin extends AbstractCyclicPlugin implements SetupPluginInterface, TearDownPluginInterface
{
	private $processID = 0;

	/**
	 * @inheritDoc
	 * Check that the pcntl extension is installed.
	 */
	public function __construct(string $identifier = NULL)
	{
		if(!function_exists('pcntl_fork'))
			throw new SPSException("Spawn SPS plugins are only available if the php extension PCNTL is installed");
		parent::__construct($identifier);
	}

	/**
	 *	Gets the current process id after running the main sps.
	 * This method returns 0 if the plugin runs in the main sps.
	 *
	 * @return int
	 */
	public function getProcessID()
	{
		return $this->processID;
	}

	/**
	 * Returns true if the current plugin is the child process.
	 *
	 * @return bool
	 */
	protected function isChildProcess(): bool {
		return $this->processID > 0;
	}

	/**
	 * Spawn now
	 */
	abstract protected function spawn();

	/**
	 * @inheritDoc
	 */
	public function setup()
	{
		static $rand = 0;
		$rand+=10000;

		switch ( $this->processID = pcntl_fork() ) {
			case -1:
				throw new SPSException("Can not fork the current process");
			case 0:
				// Is child process
				usleep( $rand );
				$this->spawn();
				exit();
			default:
				// Parent process
		}
	}

	/**
	 * @inheritDoc
	 */
	public function tearDown()
	{
		if($this->processID) {
			// Parent process must kill running child plugin
			posix_kill( $this->processID, SIGTERM );
		}
	}
}