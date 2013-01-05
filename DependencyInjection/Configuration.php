<?php

namespace Tga\AudienceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('tga_audience');

		$rootNode
			->children()
				->integerNode('session_duration')->defaultValue(300)->end()
				->variableNode('disabled_routes')->end()
				->variableNode('environnements')->end()
			->end();

		return $treeBuilder;
	}
}
