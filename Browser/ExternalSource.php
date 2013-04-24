<?php

/*
 * This file is part of the TgaAudienceBundle package.
 *
 * (c) Titouan Galopin <http://titouangalopin.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tga\AudienceBundle\Browser;

/**
 * External source model
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class ExternalSource
{
	/**
	 * @var string
	 */
	protected $domain;

	/**
	 * @var integer
	 */
	protected $countVisitors;

	/**
	 * @var float
	 */
	protected $percentage;


	/**
	 * @param $domain
	 * @param $countVisitors
	 * @param $percentage
	 */
	public function __construct($domain, $countVisitors, $percentage)
	{
		$this->domain = $domain;
		$this->countVisitors = $countVisitors;
		$this->percentage = $percentage;
	}

	/**
	 * @param int $countVisitors
	 * @return ExternalSource
	 */
	public function setCountVisitors($countVisitors)
	{
		$this->countVisitors = $countVisitors;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountVisitors()
	{
		return $this->countVisitors;
	}

	/**
	 * @param string $domain
	 * @return ExternalSource
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * @param float $percentage
	 * @return ExternalSource
	 */
	public function setPercentage($percentage)
	{
		$this->percentage = $percentage;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getPercentage()
	{
		return $this->percentage;
	}
}