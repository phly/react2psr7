<?php
/**
 * @link      http://github.com/phly/react2psr7 for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/react2psr7/blob/master/LICENSE.md New BSD License
 */

namespace React2Psr7;

use Interop\Container\ContainerInterface;

class StaticFilesFactory
{
    /**
     * Create and return a StaticFiles instance.
     *
     * Uses the application configuration, if present, to marshal an instance
     * of the StaticFiles middleware. The expected configuration structure is:
     *
     * <code>
     * 'static_files' => [
     *     'root' => 'path to the root containing static files to serve',
     *     'content_types' => [
     *         'extension' => 'content/type',
     *     ],
     * ]
     * </code>
     *
     * If no root is provided, the "public" subdirectory of the current working
     * directory is used. If no content types are provided, the defaults in the
     * StaticFiles class are used.
     *
     * @param ContainerInterface $container
     * @return StaticFiles
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $this->getConfig($container);

        return new StaticFiles(
            $this->marshalRootPath($config),
            isset($config['content_types']) ? $config['content_types'] : null
        );
    }

    /**
     * Get application static files configuration, if any.
     *
     * Returns the 'static_files' value from the application configuration, if
     * present.
     *
     * @param ContainerInterface $container
     * @return array
     */
    private function getConfig(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            return [];
        }

        $config = $container->get('config');
        return isset($config['static_files']) ? $config['static_files'] : [];
    }

    /**
     * Marshal the static files root path from configuration.
     *
     * If the static files configuration includes a root path, returns it;
     * otherwise, uses the `public` subdirectory from the current working path.
     *
     * @param array $config
     * @return string
     */
    private function marshalRootPath(array $config)
    {
        return isset($config['root'])
            ? $config['root']
            : getcwd() . '/public';
    }
}
