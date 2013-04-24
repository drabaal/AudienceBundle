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
 * UserAgent informations storage
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class UserAgent implements \Serializable
{
	const PLATFORM_ANDROID = 'Android';
	const PLATFORM_IPHONE = 'iPhone';
	const PLATFORM_IPOD = 'iPod';
	const PLATFORM_IPAD = 'iPad';
	const PLATFORM_LINUX = 'Linux';
	const PLATFORM_MAC = 'Mac OS';
	const PLATFORM_WINDOWS = 'Windows';

	const BROWSER_IE = 'Internet Explorer';
	const BROWSER_FIREFOX = 'Mozilla Firefox';
	const BROWSER_CHROME = 'Google Chrome';
	const BROWSER_SAFARI = 'Apple Safari';
	const BROWSER_OPERA = 'Opera';
	const BROWSER_NETSCAPE = 'Netscape';

	static $mobilePlatforms = array(
		self::PLATFORM_ANDROID,
		self::PLATFORM_IPOD,
		self::PLATFORM_IPAD,
		self::PLATFORM_IPHONE
	);

	/**
	 * @var string
	 */
	protected $browser;

	/**
	 * @var string
	 */
	protected $browserVersion;

	/**
	 * @var string
	 */
	protected $platform;

	/**
	 * @var boolean
	 */
	protected $isMobile;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->browser = 'Unknown';
		$this->browserVersion = 'Unknown';
		$this->platform = 'Unknown';
		$this->isMobile = false;
	}

	/**
	 * Serialize the object
	 *
	 * @implements \Serializable
	 * @return string
	 */
	public function serialize()
	{
		return serialize(array($this->browser, $this->browserVersion, $this->platform, $this->isMobile));
	}

	/**
	 * Unserialize the string
	 *
	 * @implements \Serializable
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		list($this->browser, $this->browserVersion, $this->platform, $this->isMobile) = unserialize($serialized);
	}

	/**
	 * @param string $browser
	 * @return UserAgent
	 */
	public function setBrowser($browser)
	{
		$this->browser = $browser;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getBrowser()
	{
		return $this->browser;
	}

	/**
	 * @param string $browserVersion
	 * @return UserAgent
	 */
	public function setBrowserVersion($browserVersion)
	{
		$this->browserVersion = $browserVersion;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getBrowserVersion()
	{
		return $this->browserVersion;
	}

	/**
	 * @param boolean $isMobile
	 * @return UserAgent
	 */
	public function setIsMobile($isMobile)
	{
		$this->isMobile = $isMobile;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsMobile()
	{
		return $this->isMobile;
	}

	/**
	 * @param string $platform
	 * @return UserAgent
	 */
	public function setPlatform($platform)
	{
		$this->platform = $platform;

		if (in_array($platform, self::$mobilePlatforms)) {
			$this->isMobile = true;
		} else {
			$this->isMobile = false;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPlatform()
	{
		return $this->platform;
	}
}