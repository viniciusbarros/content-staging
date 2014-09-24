WordPress Content Staging
=========================

Environments
------------

You need to set up two environments:

* Content Staging (where content editors do their work).
* Production (the publicly accessible environment).

The Content Staging environment need access to xmlrpc.php on the Production environment (wordpress/xmlrpc.php).

The Production environment need access to the attachments directory (usually wordpress/wp-content/uploads) on the Content Staging environment.

Installation
------------

Add the *content-staging* plugin to your plugins directory (e.g. wordpress/wp-content/plugins) on both environments.

Activate the plugin on both environments.

Configuration
-------------

Add the following to your config file (e.g. wp-config.php) on your *Content Staging* environment:

	define( 'CONTENT_STAGING_SECRET_KEY', '_SAME_RANDOM_KEY_ON_BOTH_ENVIRONMENTS_' );
	define( 'CONTENT_STAGING_ENDPOINT', 'https://www.YOUR-PRODUCTION-SITE.com' );
	define( 'CONTENT_STAGING_TRANSFER_TIMEOUT', 60 );

Add the following to your config file (e.g. wp-config.php) on your *Production* environment:

	define( 'CONTENT_STAGING_SECRET_KEY', '_SAME_RANDOM_KEY_ON_BOTH_ENVIRONMENTS_' );
	define( 'CONTENT_STAGING_ENDPOINT', 'https://www.YOUR-CONTENT-STAGING-SITE.com' );
	define( 'CONTENT_STAGING_TRANSFER_TIMEOUT', 60 );

Deploy Process
--------------

A batch goes through a couple of different steps on its way to being deployed on the production environment. These steps are:

* **Prepare** - Runs on *content stage*. Prepare batch data that we want to send to production.
* **Pre-Flight** - Runs on *production*. Verifies that batch data can be imported on production.
* **Deploy** - Runs on *content stage*. Send batch data to production.
* **Import** - Runs on * production*. Imports batch data.

Hooks
-----

Most of the hooks follow a naming schema that indicates at what point in the deployment process they are triggered:

| Environment   | When              | Hook Prefix  |
| ------------- | ----------------- | ------------ |
| Content Stage | Before pre-flight | sme_prepare  |
| Production    | During pre-flight | sme_verify   |
| Production    | After pre-flight  | sme_verified |
| Content Stage | After pre-flight  | sme_prepared |
| Content Stage | Before deploy     | sme_deploy   |
| Production    | During deploy     | sme_import   |
| Production    | After deploy      | sme_imported |
| Content Stage | After deploy      | sme_deployed |

### Filter Hooks

**sme\_endpoint** <br/>
Change endpoint for XML-RPC request.

**sme\_post\_relationship\_keys** <br/>
Postmeta keys whose records contains relations between posts.

**sme\_prepare\_post\_ids** <br/>
Filter array of post IDs to be included in batch. By adding a post ID to the array the corresponding post will be included in the batch. Runs on *content stage* before pre-flight.

**sme\_prepare\_posts** <br/>
Filter or add posts to batch. Runs on *content stage* just before data is sent production during pre-flight.

**sme\_prepare\_attachments** <br/>
Filter or add attachments to batch. Runs on *content stage* just before data is sent to production during pre-flight.

**sme\_prepare\_users** <br/>
Filter or add users to batch. Runs on *content stage* just before data is sent to production during pre-flight.

**sme\_import\_attachments** <br/>
Filter array of attachments. Runs just before attachments is imported on production.

### Action Hooks

**sme\_prepare\_custom\_data** <br/>
Add custom data to a batch. Runs on content stage just before data is sent to production during pre-flight.

**sme\_import\_attachments** <br/>
Inject your custom attachment importer. Runs just before attachments is imported on production.

**sme\_import\_\[ADDON\_NAME\]** <br/>
Import custom add-on data. Replace \[ADDON\_NAME\] with the name of your add-on. Runs on production during batch import.

Creating Add-ons
----------------

Extending the WordPress **Content Staging** plugin is pretty straightforward, here is a simple example:

	/**
	 * Prepare custom data to be sent from content stage to production.
	 */
	function my_addon( $batch ) {
		// Give your add-on a unique name.
		$addon = 'my_awesome_addon';

		// Some data you want to add to the batch.
		$data = 'Hello World';

		// Add your add-on data to the batch.
		$batch->add_custom_data( $addon, $data );
	}

	// Register your add-on.
	add_action( 'sme_prepare_custom_data', 'my_addon' );

	/**
	 * Import custom data on production when batch is deployed.
	 */
	 function import_addon_data( $data ) {
	 	// Do something with your add-on data.
	 }

	 // Notice how we add the name of your add-on to the import hook.
	 add_action( 'sme_import_my_awesome_addon', 'import_addon_data' );

Passing Messages Back To Content Stage
--------------------------------------

During pre-flight and deploy you might want to pass messages from the production environment back to content stage so they can be displayed to the user. Doing so is quite easy, here's an example for you:

	function my_custom_attachment_importer( $attachments, $importer ) {
		// Do something fancy with provided attachments.

		// Oh-uh, something went wrong! Notify user.
		$importer->add_message( 'Something went terribly wrong!', 'error' );

		// No way to recover from this, fail the batch import.
		$importer->set_status( 2 ); // 2 = Failed.
	}

	add_action( 'sme_import_attachments', 'my_custom_attachment_importer', 10, 2 );