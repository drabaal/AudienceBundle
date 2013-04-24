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

use Doctrine\Bundle\DoctrineBundle\Registry;
use Tga\AudienceBundle\Entity\VisitorSession;

/**
 * Give an external access to Processor using services
 */
class ExternalAccessor
{
	/**
	 * @var Processor
	 */
	protected $processor;

	/**
	 * Constructor
	 */
	public function __construct(Registry $doctrine)
	{
		/** @var $sessions VisitorSession[] */
		$sessions = $doctrine->getManager()->createQueryBuilder()
			->select('s, c')
			->from('TgaAudienceBundle:VisitorSession', 's')
			->leftJoin('s.calls', 'c')
			->orderBy('s.lastVisit')
			->getQuery()
			->getResult();

		$this->processor = new Processor($sessions);
	}

	/**
	 * @return \Tga\AudienceBundle\Stats\Processor
	 */
	public function getProcessor()
	{
		return $this->processor;
	}
}