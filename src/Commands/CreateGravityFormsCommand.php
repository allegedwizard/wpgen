<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Commands\Traits\RegisterComponentInMainClass;
use WPGen\Config;

/**
 * Scaffolds a Gravity Forms integration: a GravityForms component that
 * registers a GFFeedAddOn (feed list, feed settings with a generic field
 * map + conditional logic, process_feed with entry notes), an entry-note
 * avatar filter, and optionally a Forms > Settings tab backed by a
 * Settings helper (API URL + key with a PHP-constant override).
 */
class CreateGravityFormsCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        CheckWorkingDirectory,
        QueryOptions;

    protected $options = [];

    protected static $defaultName = 'create:gravity-forms';

    protected function configure() {
        $this
            ->setDescription( 'Add a Gravity Forms feed add-on (FeedAddOn, Settings, SettingsPage) to plugin.' )
            ->setHelp( 'Creates src/GravityForms with a GFFeedAddOn, entry-note filter, and an optional Forms > Settings tab. Run from your plugin root.' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        // Verify we're in a plugin directory.
        $this->isWPGenPluginDirectory( $input, $output );

        $options = Config::get()->gravityFormsOptions();
        $this->mergeOptions( $options );
        $this->querySecondaryOptions( $input, $output, $options );

        $name = trim( $this->options['gf_addon_name']['value'] );

        // Feed add-on slug: lowercase + underscores (also used as the GF note type).
        $slug = strtolower( preg_replace( '/[^a-z0-9]+/i', '_', $name ) );
        $slug = trim( $slug, '_' );
        $this->options['gf_addon_slug'] = ['value' => $slug];

        // PascalCase class prefix (e.g. "My CRM" => "MyCrm" => MyCrmFeedAddOn).
        $class = str_replace( '_', ' ', $slug );
        $class = ucwords( $class );
        $class = str_replace( ' ', '', $class );
        $this->options['gf_addon_class'] = ['value' => $class];

        // The component only instantiates the settings page when generated.
        $settings_page_line = '';
        if ( $this->options['gf_settings_page']['value'] ) {
            $settings_page_line = "        new {$class}SettingsPage;\n";
        }
        $this->options['gf_settings_page_line'] = ['value' => $settings_page_line];

        $this->registerComponentInMainClass( $input, $output, 'GravityForms\GravityFormsComponent' );
    }

    /**
     * Process and copy stub files to target directory.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute( InputInterface $input, OutputInterface $output ) {

        $component_path = getcwd() . '/src/GravityForms/';
        if ( ! is_dir( $component_path ) ) {
            mkdir( $component_path, 0755, true );
        }

        $stub_path = APP_ROOT . 'stubs/gravity-forms/';
        $class = $this->options['gf_addon_class']['value'];

        $this->processFiles( $stub_path, $component_path, [
            ['source' => 'gravity-forms-component.php', 'target' => 'GravityFormsComponent.php'],
            ['source' => 'feed-addon.php',              'target' => $class . 'FeedAddOn.php'],
            ['source' => 'filter-entry-notes.php',      'target' => 'FilterEntryNotes.php'],
        ]);

        if ( $this->options['gf_settings_page']['value'] ) {

            // The Settings helper extends the shared AbstractSettings (same base
            // create:admin generates), so both can coexist in one plugin.
            $abstract_path = getcwd() . '/src/Abstract/';
            if ( ! is_dir( $abstract_path ) ) {
                mkdir( $abstract_path, 0755, true );
            }
            $this->processFiles( APP_ROOT . 'stubs/admin/', $abstract_path, [
                ['source' => 'abstract-settings.php', 'target' => 'AbstractSettings.php'],
            ]);

            $this->processFiles( $stub_path, $component_path, [
                ['source' => 'settings.php',      'target' => $class . 'Settings.php'],
                ['source' => 'settings-page.php', 'target' => $class . 'SettingsPage.php'],
            ]);
        }

        $output->writeln( "<info>Gravity Forms add-on '{$class}FeedAddOn' generated in src/GravityForms.</info>" );
        $output->writeln( '<comment>Next: edit feed_settings_fields() / process_feed() in the FeedAddOn, and make sure Gravity Forms is active.</comment>' );

        return 0;
    }
}
