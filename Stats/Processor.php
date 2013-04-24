<?php

/*
 * This file is part of the TgaAudienceBundle package.
 *
 * (c) Titouan Galopin <http://titouangalopin.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tga\AudienceBundle\Stats;

use Tga\AudienceBundle\Browser\ExternalSource;
use Tga\AudienceBundle\Entity\VisitorCall;
use Tga\AudienceBundle\Entity\VisitorSession;

/**
 * Analyse TgaAudience datas to retrieve website statistics
 */
class Processor
{
	/** @var array */
	protected $uniqueVisitors;

	/** @var int */
	protected $uniqueVisitorsCount;

	/** @var array */
	protected $pageCalls;

	/** @var int */
	protected $pageCallsCount;

	/** @var float */
	protected $averageVisitedPages;

	/** @var float */
	protected $averageDuration;

	/** @var float */
	protected $averageTimeToLoad;

	/** @var array */
	protected $platforms;

	/** @var array */
	protected $browsers;

	/** @var array */
	protected $browsersVersions;

	/** @var array */
	protected $mostUsedRoutes;

	/** @var array */
	protected $mostUsedBrowsers;

	/** @var array */
	protected $externalSources;

	/** @var array */
	protected $mostUsedExternalSources;

	/**
	 * Constructor.
	 * Create all the statistics using the datas.
	 * @param VisitorSession[] $sessions
	 */
	public function __construct(array $sessions)
	{
		if (empty($sessions)) {
			return;
		}

		$uniqueVisitors = array();
		$pagesCalls = array();
		$uniqueVisitorsCount = 0;
		$pagesCallsCount = 0;
		$averageVisitedPages = 0;
		$averageDuration = 0;
		$averageTimeToLoad = 0;
		$platforms = array();
		$browsers = array();
		$browsersVersions = array();
		$mostUsedRoutes = array();
		$mostUsedBrowsers = array();
		$externalSources = array();

		$calls = array();

		/*
		 * Calls, sessions and visits
		 */
		$callsCount = 0;

		for ($i = 1; $i <= date('d'); $i ++) {
			$uniqueVisitors[$i] = 0;
			$pagesCalls[$i] = 0;
		}

		foreach ($sessions as $session) {
			if ($session
				->getLastVisit()
				->format('m') == (new \DateTime())->format('m')
			) {
				// Unique
				$uniqueVisitors[(int)$session
					->getLastVisit()
					->format('d')] ++;
				$uniqueVisitorsCount ++;

				// All calls
				$pagesCalls[(int)$session
					->getLastVisit()
					->format('d')] += count($session->getCalls());
				$pagesCallsCount += count($session->getCalls());

				if (is_object($session->getLastCall()) && is_object($session->getFirstCall())) {
					$averageDuration += $session
						->getLastCall()
						->getDate()
						->getTimestamp() - $session
						->getFirstCall()
						->getDate()
						->getTimestamp();
				}

				foreach ($session->getCalls() as $call) {
					$averageTimeToLoad += $call->getTimeToLoad();
					$callsCount ++;

					$calls[] = $call;
				}
			}
		}

		$averageVisitedPages = round(($pagesCallsCount - $uniqueVisitorsCount) / $uniqueVisitorsCount, 2);
		$averageTimeToLoad = round($averageTimeToLoad / $callsCount, 2) * 1000;

		$averageDuration = $averageDuration / $uniqueVisitorsCount;

		if ($averageDuration == 0) {
			$averageDuration = null;
		}

		foreach ($uniqueVisitors as $date => $count) {
			unset($uniqueVisitors[$date]);
			$uniqueVisitors[] = array($date, $count);
		}

		foreach ($pagesCalls as $date => $count) {
			unset($pagesCalls[$date]);
			$pagesCalls[] = array($date, $count);
		}


		$this->uniqueVisitors = $uniqueVisitors;
		$this->uniqueVisitorsCount = $uniqueVisitorsCount;
		$this->pageCalls = $pagesCalls;
		$this->pageCallsCount = $pagesCallsCount;
		$this->averageDuration = $averageDuration;
		$this->averageTimeToLoad = $averageTimeToLoad;
		$this->averageVisitedPages = $averageVisitedPages;


		/*
		 * Visitors browsers, platform and behaviors
		 */
		$routes = array();
		$countRoutes = 0;
		$countBrowsersVersions = 0;

		foreach ($sessions as $session) {
			if ($session->getPlatform() != null) {
				if (! isset($platforms[$session->getPlatform()]))
						{
							$platforms[$session->getPlatform()] = 0;
						}

				$platforms[$session->getPlatform()] ++;
			}

			if ($session->getBrowser() != null) {
				if (! isset($browsers[$session->getBrowser()]))
					$browsers[$session->getBrowser()] = 0;

				if (! isset($browsersVersions[$session->getBrowser() . ' ' . $session->getBrowserVersion()]))
					$browsersVersions[$session->getBrowser() . ' ' . $session->getBrowserVersion()] = 0;

				$browsers[$session->getBrowser()] ++;
				$browsersVersions[$session->getBrowser() . ' ' . $session->getBrowserVersion()] ++;
				$countBrowsersVersions ++;
			}

			foreach ($session->getCalls() as $call) {
				if (! isset($routes[$call->getRoute()]))
					$routes[$call->getRoute()] = 0;

				$routes[$call->getRoute()] ++;
				$countRoutes ++;
			}
		}

		foreach ($platforms as $platform => $nb) {
			unset($platforms[$platform]);
			$platforms[] = array($platform, $nb);
		}

		foreach ($browsers as $browser => $nb) {
			unset($browsers[$browser]);
			$browsers[] = array($browser, $nb);
		}

		arsort($routes);
		arsort($browsersVersions);

		$i = 1;

		foreach ($routes as $route => $nb) {
			if ($i > 50)
				break;

			$mostUsedRoutes[$route] = array('place' => $i, 'route' => $route, 'nb' => $nb, 'percentage' => round(($nb / $countRoutes) * 100, 2),);

			$i ++;
		}

		$i = 1;

		foreach ($browsersVersions as $browsersVersion => $nb) {
			if ($i > 50)
				break;

			$mostUsedBrowsers[$browsersVersion] = array('place' => $i, 'name' => $browsersVersion, 'nb' => $nb, 'percentage' => round(($nb / $countBrowsersVersions) * 100, 2),);

			$i ++;
		}

		$this->platforms = $platforms;
		$this->browsers = $browsers;
		$this->browsersVersions = $browsersVersions;
		$this->mostUsedRoutes = $mostUsedRoutes;
		$this->mostUsedBrowsers = $mostUsedBrowsers;


		/*
		 * Traffic sources
		 */
		$sources = array();

		foreach ($calls as $call) {
			$referer = $call->getReferer();

			if (! empty($referer)) {
				$referer = parse_url($referer, PHP_URL_HOST);

				if ($_SERVER['HTTP_HOST'] != $referer) {
					$sources[] = str_replace('www.', '', $referer);
				}
			}
		}

		$count = 0;

		foreach ($sources as $source) {
			if (! isset($externalSources[$source])) {
				$externalSources[$source] = 0;
			}

			$externalSources[$source] ++;
			$count ++;
		}

		$allExternalSources = $externalSources;

		$i = 0;

		foreach ($externalSources as $externalSource => $nb) {
			unset($externalSources[$externalSource]);

			if ($i <= 10) {
				$externalSources[] = array($externalSource, $nb);
				$i ++;
			}
		}

		arsort($allExternalSources);

		$i = 1;

		foreach ($allExternalSources as $source => $value) {
			if ($i > 50) {
				break;
			}

			$allExternalSources[$i] = new ExternalSource($source, $value, round(($value / $count) * 100, 2));

			$i++;
		}

		$this->externalSources = $externalSources;
		$this->mostUsedExternalSources = $allExternalSources;
	}

	/**
	 * @return float
	 */
	public function getAverageDuration()
	{
		return $this->averageDuration;
	}

	/**
	 * @return float
	 */
	public function getAverageTimeToLoad()
	{
		return $this->averageTimeToLoad;
	}

	/**
	 * @return float
	 */
	public function getAverageVisitedPages()
	{
		return $this->averageVisitedPages;
	}

	/**
	 * @return array
	 */
	public function getBrowsers()
	{
		return $this->browsers;
	}

	/**
	 * @return array
	 */
	public function getBrowsersVersions()
	{
		return $this->browsersVersions;
	}

	/**
	 * @return array
	 */
	public function getExternalSources()
	{
		return $this->externalSources;
	}

	/**
	 * @return array
	 */
	public function getMostUsedBrowsers()
	{
		return $this->mostUsedBrowsers;
	}

	/**
	 * @return array
	 */
	public function getMostUsedExternalSources()
	{
		return $this->mostUsedExternalSources;
	}

	/**
	 * @return array
	 */
	public function getMostUsedRoutes()
	{
		return $this->mostUsedRoutes;
	}

	/**
	 * @return array
	 */
	public function getPageCalls()
	{
		return $this->pageCalls;
	}

	/**
	 * @return int
	 */
	public function getPageCallsCount()
	{
		return $this->pageCallsCount;
	}

	/**
	 * @return array
	 */
	public function getPlatforms()
	{
		return $this->platforms;
	}

	/**
	 * @return array
	 */
	public function getUniqueVisitors()
	{
		return $this->uniqueVisitors;
	}

	/**
	 * @return int
	 */
	public function getUniqueVisitorsCount()
	{
		return $this->uniqueVisitorsCount;
	}
}