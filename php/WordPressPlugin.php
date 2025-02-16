<?php

namespace Coyote;

use Coyote\Traits\Logger;
use Coyote\Controllers\RestApiController;
use Coyote\Controllers\SettingsController;

if (!defined('WPINC')) {
    exit;
}

class WordPressPlugin
{
    use Logger;

    public const PLUGIN_NAME = "Coyote";
    public const I18N_NS = 'coyote';
    public const LOG_PATH = COYOTE_PLUGIN_PATH . 'coyote.log';

    public function __construct(string $pluginFile)
    {
        WordPressActionsAndFilters::setupPluginHooks($pluginFile);

        if (!PluginConfiguration::isInstalled()) {
            return;
        }

        WordPressActionsAndFilters::setupPluginActionsAndFilters($pluginFile);

        /*
         * setupControllers after plugins_loaded so wp-includes/pluggable.php is loaded
         */
        add_action('init', [$this, 'setupControllers']);
    }

    public static function setupControllers(): void
    {
        if (WordPressHelper::userIsAdmin()) {
            (new SettingsController());
        }

        if (
            PluginConfiguration::hasStoredApiProfile() &&
            PluginConfiguration::hasUpdatesEnabled() &&
            !is_null(PluginConfiguration::getApiOrganizationId())
        ) {
            // allow remote updates
            self::logDebug('Updates enabled.');

            (new RestApiController(
                PluginConfiguration::PLUGIN_VERSION,
                PluginConfiguration::API_VERSION,
                PluginConfiguration::getApiOrganizationId()
            ));
        } else {
            self::logDebug('Updates disabled.');
        }
    }

    private static function getPluginFile(string $type, string $name): string
    {
        return COYOTE_PLUGIN_PATH . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $name;
    }

    public static function getSqlFile(string $name): string
    {
        return self::getPluginFile('sql', $name);
    }

    /**
     * Check if the plugin has been updated
     */
    public static function checkForUpdates(): void
    {
        /*
         * If the plugin version is newer than registered in the database the plugin has been updated to a newer version
         * fire action hook that can be used to run custom update scripts
         * the current and new version numbers are passed as arguments
         */
        if (version_compare(PluginConfiguration::getStoredPluginVersion(), PluginConfiguration::PLUGIN_VERSION, '<')) {
            self::pluginUpdatedHandler(
                PluginConfiguration::getStoredPluginVersion(),
                PluginConfiguration::PLUGIN_VERSION
            );
        }
    }

    /**
     * Plugin has been updated, run migration code
     *
     * @param $currentVersion
     * @param $newVersion
     */
    public static function pluginUpdatedHandler($currentVersion, $newVersion): void
    {
    }
}
