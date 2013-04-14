<?php namespace Orchestra\Extension;

use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() 
	{
		$this->registerIlluminateServiceProvider();
		$this->registerExtension();
		$this->registerExtensionFinder();
		$this->registerExtensionProvider();
	}

	/**
	 * Register the service provider for `orchestra.service.provider`.
	 *
	 * @return void
	 */
	public function registerIlluminateServiceProvider()
	{
		$this->app['orchestra.service.provider'] = $this->app->share(function ($app)
		{
			return new \Illuminate\Foundation\ProviderRepository(
				$app['files'],
				$app['config']->get('orchestra/extension::manifest')
			);
		});
	}

	/**
	 * Register the service provider for Extension.
	 *
	 * @return void
	 */
	protected function registerExtension()
	{
		$this->app['orchestra.extension'] = $this->app->share(function ($app)
		{
			return new Extension($app);
		});
	}

	/**
	 * Register the service provider for Extension Finder.
	 *
	 * @return void
	 */
	protected function registerExtensionFinder()
	{
		$this->app['orchestra.extension.finder'] = $this->app->share(function ($app)
		{
			return new Finder($app);
		});
	}

	/**
	 * Register the service provider for Extension Provider.
	 *
	 * @return void
	 */
	protected function registerExtensionProvider()
	{
		$this->app['orchestra.extension.provider'] = $this->app->share(function ($app)
		{
			return new ProviderRepository($app);
		});
	}
}