<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions\Salesforce;

/**
 * Settings handler object
 */
class Settings extends Option {

	/**
	 * Setting key
	 *
	 * @var string
	 */
	protected static $key = 'amnesty_salesforce_petitions';

	/**
	 * Instance variable
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Option data
	 *
	 * @var array
	 */
	protected static $option = [];

}
