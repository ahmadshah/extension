<?php namespace Orchestra\Extension;

use RuntimeException;

class Finder {

	/**
	 * Application instance.
	 *
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app = null;
	
	/**
	 * List of paths.
	 *
	 * @var array
	 */
	protected $paths = array();

	/**
	 * List of reserved name.
	 *
	 * @var array
	 */
	protected $reserved = array(
		'orchestra',
		'resources',
		'orchestra/asset',
		'orchestra/auth',
		'orchestra/extension',
		'orchestra/facile',
		'orchestra/foundation',
		'orchestra/html',
		'orchestra/memory',
		'orchestra/model',
		'orchestra/platform',
		'orchestra/resources',
		'orchestra/routing',
		'orchestra/services',
		'orchestra/support',
		'orchestra/testbench',
		'orchestra/view',
		'orchestra/widget',
	);

	/**
	 * Construct a new finder.
	 *
	 * @access public
	 * @param  \Illuminate\Foundation\Application   $app
	 * @return void
	 */
	public function __construct($app)
	{
		$this->app = $app;
		$appPath   = rtrim($app['path'], '/').'/';
		$basePath  = rtrim($app['path.base'], '/').'/';

		// In most cases we would only need to concern with the following 
		// path; application folder, vendor folders and workbench folders.
		$this->paths = array(
			"{$appPath}",
			"{$basePath}vendor/*/*/",
			"{$basePath}workbench/*/*/"
		);
	}

	/**
	 * Add a new path to finder.
	 *
	 * @access public	
	 * @param  string   $path
	 * @return self
	 */
	public function addPath($path)
	{
		if ( ! in_array($path, $this->paths)) $this->paths[] = $path;

		return $this;
	}

	/**
	 * Detect available extensions.
	 *
	 * @access public
	 * @return array
	 * @throws \RuntimeException
	 */
	public function detect()
	{
		$extensions = array();

		// Loop each path to check if there orchestra.json available within
		// the paths. We would only treat packages that include orchestra.json
		// as an Orchestra Platform extension.
		foreach ($this->paths as $path)
		{
			$manifests = $this->app['files']->glob("{$path}orchestra.json");

			// glob() method might return false if there an errors, convert 
			// the result to an array.
			is_array($manifests) or $manifests = array();

			foreach ($manifests as $manifest)
			{
				list($vendor, $package) = $this->getPackageSegmentsFromManifest($manifest);
				$name = null;

				// Each package should have vendor/package name pattern, 
				// except when we deal with app. 
				if (rtrim($this->app['path'], '/') === rtrim($path, '/'))
				{
					$name = 'app';
				}
				elseif ( ! is_null($vendor) and ! is_null($package))
				{
					$name = "{$vendor}/{$package}";
				}
				else continue;

				if (in_array($name, $this->reserved))
				{
					throw new RuntimeException("Unable to register reserved name [{$name}] as extension.");
				}

				$extensions[$name] = $this->getManifestContents($manifest);
			}
		}

		return $extensions;
	}

	/**
	 * Get manifest contents.
	 *
	 * @access protected
	 * @param  string   $manifest
	 * @return array
	 * @throws \Orchestra\Extension\ManifestRuntimeException
	 */
	protected function getManifestContents($manifest)
	{
		$jsonable = json_decode($this->app['files']->get($manifest));

		// If json_decode fail, due to invalid json format. We going to 
		// throw an exception so this error can be fixed by the developer 
		// instead of allowing the application to run with a buggy config.
		if (is_null($jsonable))
		{
			throw new ManifestRuntimeException("Cannot decode file [{$manifest}]");
		}

		// Generate a proper manifest configuration for the extension. This 
		// would allow other part of the application to use this configuration
		// to migrate, load service provider as well as preload some 
		// configuration.
		return array(
			'path'        => str_replace('orchestra.json', '', $manifest),
			'name'        => (isset($jsonable->name) ? $jsonable->name : null),
			'description' => (isset($jsonable->description) ? $jsonable->description : null),
			'author'      => (isset($jsonable->author) ? $jsonable->author : null),
			'url'         => (isset($jsonable->url) ? $jsonable->url : null),
			'version'     => (isset($jsonable->version) ? $jsonable->version : '>0'),
			'config'      => (isset($jsonable->config) ? $jsonable->config : array()),
			'provide'     => (isset($jsonable->provide) ? $jsonable->provide : array()),
		);
	}

	/**
	 * Get package name from manifest.
	 * 
	 * @access protected
	 * @param  string   $manifest
	 * @return array
	 */
	protected function getPackageSegmentsFromManifest($manifest)
	{
		$vendor   = null;
		$package  = null; 
		$fragment = explode('/', $manifest);

		// Remove orchestra.json from fragment as we are only interested with
		// the two segment before it.
		array_pop($fragment);

		if (count($fragment) > 2)
		{
			$package = array_pop($fragment);
			$vendor  = array_pop($fragment);
		}

		return array($vendor, $package);
	}
}
