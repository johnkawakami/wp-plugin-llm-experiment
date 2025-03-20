<?php

namespace JTK\Admin;

abstract class AbstractAdmin {

	/**
	 * This function registers callbacks to set up the admin screens.
	 *
	 * Must be called in an 'init' action.
	 *
	 * Can be called directly if you're already being called in an 'init' action.
	 *
	 * Typical things done in register():
	 *   register_settings()
	 *   registering admin_init and admin_menu hooks
	 */
	abstract public function register();
}
