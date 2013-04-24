<?php

/*
 * This file is part of the TgaAudienceBundle package.
 *
 * (c) Titouan Galopin <http://titouangalopin.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tga\AudienceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Tga\AudienceBundle\Stats\Processor;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Interface for statistics analyse.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class StatsController extends Controller
{
	/**
	 * @Route("", name="tga_audience_index")
	 * @Template()
	 */
	public function indexAction()
	{
		/** @var $processor Processor */
		$processor = $this->get('tga_audience.stats')->getProcessor();

		$uniqueVisitors = array_merge(array(array('Date', 'Visitors')), $processor->getUniqueVisitors());
		$pagesCalls = array_merge(array(array('Date', 'Calls')), $processor->getPageCalls());

		return array(
			'uniqueVisitors' => json_encode($uniqueVisitors),
			'uniqueVisitorsCount' => $processor->getUniqueVisitorsCount(),
			'pagesCalls' => json_encode($pagesCalls),
			'pagesCallsCount' => $processor->getPageCallsCount(),
			'averageVisitedPages' => $processor->getAverageVisitedPages(),
			'averageDuration' => $processor->getAverageDuration(),
			'averageTimeToLoad' => $processor->getAverageTimeToLoad()
		);
	}

	/**
	 * @Route("/visitors", name="tga_audience_visitors")
	 * @Template()
	 */
	public function visitorsAction()
	{
		/** @var $processor Processor */
		$processor = $this->get('tga_audience.stats')->getProcessor();

		$platforms = array_merge(array(array('Platform', 'Count')), $processor->getPlatforms());
		$browsers = array_merge(array(array('Browser', 'Count')), $processor->getBrowsers());

		return array(
			'platforms' => json_encode($platforms),
			'browsers' => json_encode($browsers),
			'mostUsedRoutes' => $processor->getMostUsedRoutes(),
			'browsersVersions' => $processor->getMostUsedBrowsers(),
		);
	}

	/**
	 * @Route("/traffic", name="tga_audience_traffic")
	 * @Template()
	 */
	public function trafficAction()
	{
		/** @var $processor Processor */
		$processor = $this->get('tga_audience.stats')->getProcessor();

		$externalSources = array_merge(array(array('Source', 'Count')), $processor->getExternalSources());

		return array(
			'externalSources' => json_encode($externalSources),
			'allExternalSources' => $processor->getMostUsedExternalSources()
		);
	}
}
