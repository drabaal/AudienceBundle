<?php

/*
 * This file is part of the TgaAudienceBundle package.
 *
 * (c) Titouan Galopin <http://titouangalopin.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tga\AudienceBundle\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Tga\AudienceBundle\Browser\UserAgent;

/**
 * Kernel listener, to store request and load datas for each call.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class KernelListener
{
	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Kernel
	 */
	private $kernel;

	/**
	 * @var Registry
	 */
	private $doctrine;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var array
	 */
	private $sessionData;

	/**
	 * @var float
	 */
	private $startTime;

	/**
	 * Constructor
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->request = $container->get('request');
		$this->kernel = $container->get('kernel');
		$this->doctrine = $container->get('doctrine');
		$this->sessionData = array();

		$this->config = array(
			'sessionDuration' => $container->getParameter('tga_audience.session_duration'),
			'disabledRoutes' => $container->getParameter('tga_audience.disabled_routes'),
			'environnements' => $container->getParameter('tga_audience.environnements'),
		);
	}

	/**
	 * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
		$this->startTime = microtime(true);
	}

	/**
	 * @param PostResponseEvent $event
	 */
	public function onKernelTerminate(PostResponseEvent $event)
	{
		if(! in_array($this->kernel->getEnvironment(), $this->config['environnements']))
			return;

		if(in_array($this->request->get('_route'), $this->config['disabledRoutes']))
			return;

		if(strpos($this->request->get('_controller'), 'Tga\AudienceBundle') !== false)
			return;

		if (substr($this->request->get('_route'), 0, 1) == '_')
			return;

		if (session_id() != '') {
			$this->sessionData = $_SESSION;
		}

		$timeToLoad = microtime(true) - $this->startTime;

		// Get the session and update it if required
		/** @var $em EntityManager */
		$em = $this->doctrine->getManager();

		$session = $em->createQueryBuilder()
			->select('s, c')
			->from('TgaAudienceBundle:VisitorSession', 's')
			->leftJoin('s.calls', 'c')
			->where('s.ip = :ip')
			->andWhere('s.lastVisit > :invalidateTime')
			->setParameter('ip', $this->getIp($this->request))
			->setParameter('invalidateTime', time() - $this->config['sessionDuration'])
			->getQuery()
			->getOneOrNullResult();

		if (! $session) {
			$session = new \Tga\AudienceBundle\Entity\VisitorSession();

			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$infos = $this->getBrowser($_SERVER['HTTP_USER_AGENT']);
			} else {
				$infos = new UserAgent();
			}

			$session->setBrowser($infos->getBrowser())
				->setBrowserVersion($infos->getBrowserVersion())
				->setPlatform($infos->getPlatform())
				->setIp($this->getIp($this->request))
				->setDatas($this->sessionData);
		}

		$session->setLastVisit(new \DateTime());

		$em->persist($session);

		if(! $session->lastPageIs($this->request->getRequestUri())) {
			$call = new \Tga\AudienceBundle\Entity\VisitorCall();

			$call->setSession($session)
				->setDate(new \DateTime())
				->setController($this->request->get('_controller'))
				->setRoute($this->request->get('_route'))
				->setRequestUri($this->request->getRequestUri())
				->setReferer(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null)
				->setTimeToLoad($timeToLoad)
				->setHost($this->request->getHttpHost())
			;

			$em->persist($call);
		}

		$em->flush();
	}

	/**
	 * Find the browser
	 *
	 * @param string $userAgentString
	 * @return UserAgent
	 * @author <ruudrp@live.nl>
	 */
	private function getBrowser($userAgentString)
	{
		$userAgent = new UserAgent();

		// First get the platform
		if(preg_match('/android/i', $userAgentString)) {
			$userAgent->setPlatform(UserAgent::PLATFORM_ANDROID);
		} elseif(preg_match('/iphone/i', $userAgentString)) {
			$userAgent->setPlatform(UserAgent::PLATFORM_IPHONE);
		} elseif(preg_match('/ipod/i', $userAgentString)) {
			$userAgent->setPlatform(UserAgent::PLATFORM_IPOD);
		} elseif(preg_match('/ipad/i', $userAgentString)) {
			$userAgent->setPlatform(UserAgent::PLATFORM_IPAD);
		} elseif(preg_match('/macintosh|mac os x/i', $userAgentString)) {
			$userAgent->setPlatform(UserAgent::PLATFORM_MAC);
		} elseif(preg_match('/linux/i', $userAgentString)) {
			$userAgent->setPlatform(UserAgent::PLATFORM_LINUX);
		} elseif(preg_match('/windows|win32/i', $userAgentString)) {
			$userAgent->setPlatform(UserAgent::PLATFORM_WINDOWS);
		}

		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i', $userAgentString) && !preg_match('/Opera/i', $userAgentString)) {
			$userAgent->setBrowser(UserAgent::BROWSER_IE);
			$ub = 'MSIE';
		} elseif(preg_match('/Firefox/i', $userAgentString)) {
			$userAgent->setBrowser(UserAgent::BROWSER_FIREFOX);
			$ub = 'Firefox';
		} elseif(preg_match('/Chrome/i', $userAgentString)) {
			$userAgent->setBrowser(UserAgent::BROWSER_CHROME);
			$ub = 'Chrome';
		} elseif(preg_match('/Safari/i', $userAgentString)) {
			$userAgent->setBrowser(UserAgent::BROWSER_SAFARI);
			$ub = 'Safari';
		} elseif(preg_match('/Opera/i', $userAgentString)) {
			$userAgent->setBrowser(UserAgent::BROWSER_OPERA);
			$ub = 'Opera';
		} elseif(preg_match('/Netscape/i', $userAgentString)) {
			$userAgent->setBrowser(UserAgent::BROWSER_NETSCAPE);
			$ub = 'Netscape';
		}

		if (isset($ub)) {
			// Get the correct version number
			$known = array('Version', $ub, 'other');
			$pattern = '#(?<browser>'. join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

			preg_match_all($pattern, $userAgentString, $matches);

			$i = count($matches['browser']);

			if($i != 1) {
				//we will have two since we are not using 'other' argument yet
				//see if version is before or after the name
				if (strripos($userAgentString, 'Version') < strripos($userAgentString, $ub)) {
					$version = $matches['version'][0];
				}
				else {
					$version = $matches['version'][1];
				}
			}
			else {
				$version = $matches['version'][0];
			}

			// check if we have a number
			if(empty($version)) {
				$version = 'Unknown';
			}

			$version = explode('.', $version);
			$version = $version[0];

			$userAgent->setBrowserVersion($version);
		}

		return $userAgent;
	}
	
	protected function getIp(Request $request)
	{
		if ($request->server->has('HTTP_X_FORWARDED_FOR')) {
			return $request->server->get('HTTP_X_FORWARDED_FOR');
		}
		
		return $request->getClientIp();
	}
}
