<?php
/**
 * This file contains abstract class for installation new plugins
 *
 * @package CONTENIDO Plugins
 * @subpackage PluginManager
 * @version SVN Revision $Rev:$
 *
 * @author Frederic Schneider
 * @copyright four for business AG <www.4fb.de>
 * @license http://www.contenido.org/license/LIZENZ.txt
 * @link http://www.4fb.de
 * @link http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');
class PimPluginSetupInstall extends PimPluginSetup {

    // Initializing variables
    // Plugin specific data
    // Foldername of installed plugin
    private $PluginFoldername;

    // All area entries from database in an array
    private $PluginInstalledAreas = array();

    // Classes
    // Class variable for PimPluginCollection
    protected $_PimPluginCollection;

    // Class variable for PimPluginRelationsCollection
    protected $_PimPluginRelationsCollection;

    // Class variable for cApiAreaCollection;
    protected $_ApiAreaCollection;

    // Class variable for cApiActionCollection
    protected $_ApiActionCollection;

    // Class variable for cApiFileCollection
    protected $_ApiFileCollection;

    // Class variable for cApiFrameFileCollection
    protected $_ApiFrameFileCollection;

    // Class variable for cApiNavMainCollection
    protected $_ApiNavMainCollection;

    // Class variable for cApiNavSubCollection
    protected $_ApiNavSubCollection;

    // Class variable for cApiTypeCollection
    protected $_ApiTypeCollection;

    // GET and SET methods for installation routine
    /**
     * Set variable for plugin foldername
     *
     * @access public
     * @param string $foldername
     * @return string
     */
    private function _setPluginFoldername($foldername) {
        return $this->PluginFoldername = cSecurity::escapeString($foldername);
    }

    /**
     * Initializing and set variable for PimPluginCollection class
     *
     * @access private
     * @return PimPluginCollection
     */
    private function _setPimPluginCollection() {
        return $this->_PimPluginCollection = new PimPluginCollection();
    }

    /**
     * Initializing and set variable for PimPluginRelationsCollection class
     *
     * @access private
     * @return PimPluginRelationsCollection
     */
    private function _setPimPluginRelationsCollection() {
        return $this->_PimPluginRelationsCollection = new PimPluginRelationsCollection();
    }

    /**
     * Initializing and set variable for cApiAreaCollection
     *
     * @access private
     * @return cApiAreaCollection
     */
    private function _setApiAreaCollection() {
        return $this->_ApiAreaCollection = new cApiAreaCollection();
    }

    /**
     * Initializing and set variable for cApiActionCollection
     *
     * @access private
     * @return cApiActionCollection
     */
    private function _setApiActionCollection() {
        return $this->_ApiActionCollection = new cApiActionCollection();
    }

    /**
     * Initializing and set variable for cApiAFileCollection
     *
     * @access private
     * @return cApiFileCollection
     */
    private function _setApiFileCollection() {
        return $this->_ApiFileCollection = new cApiFileCollection();
    }

    /**
     * Initializing and set variable for cApiFrameFileCollection
     *
     * @access private
     * @return cApiFrameFileCollection
     */
    private function _setApiFrameFileCollection() {
        return $this->_ApiFrameFileCollection = new cApiFrameFileCollection();
    }

    /**
     * Initializing and set variable for cApiNavMainFileCollection
     *
     * @access private
     * @return cApiNavMainCollection
     */
    private function _setApiNavMainCollection() {
        return $this->_ApiNavMainCollection = new cApiNavMainCollection();
    }

    /**
     * Initializing and set variable for cApiNavSubCollection
     *
     * @access private
     * @return cApiNavSubCollection
     */
    private function _setApiNavSubCollection() {
        return $this->_ApiNavSubCollection = new cApiNavSubCollection();
    }

    /**
     * Initializing and set variable for cApiTypeCollection
     *
     * @access private
     * @return cApiNavSubCollection
     */
    private function _setApiTypeCollection() {
        return $this->_ApiTypeCollection = new cApiTypeCollection();
    }

    /**
     * Get method for foldername of installed plugin
     *
     * @return string
     */
    protected function _getPluginFoldername() {
        return $this->PluginFoldername;
    }

    /**
     * Get method for installed areas
     *
     * @return multitype:
     */
    protected function _getInstalledAreas() {
        return $this->PluginInstalledAreas;
    }

    // Begin of installation routine
    /**
     * Construct function
     *
     * @access public
     * @return void
     */
    public function __construct() {

        // Initializing and set classes
        // PluginManager classes
        $this->_setPimPluginCollection();
        $this->_setPimPluginRelationsCollection();

        // cApiClasses
        $this->_setApiAreaCollection();
        $this->_setApiActionCollection();
        $this->_setApiFileCollection();
        $this->_setApiFrameFileCollection();
        $this->_setApiNavMainCollection();
        $this->_setApiNavSubCollection();
        $this->_setApiTypeCollection();
    }

    /**
     * Installation method
     *
     * @access public
     * @return void
     */
    public function install() {

        // Does this plugin already exist?
        $this->_installCheckUuid();

        // Requirement checks
        $this->_installCheckRequirements();

        // Add new plugin: *_plugins
        $this->_installAddPlugin();

        // Get all area names from database
        $this->_installFillAreas();

        // Add new CONTENIDO areas: *_area
        $this->_installAddAreas();

        // Add new CONTENIDO actions: *_actions
        $this->_installAddActions();

        // Add new CONTENIDO frames: *_frame_files and *_files
        $this->_installAddFrames();

        // Add new CONTENIDO main navigations: *_nav_main
        $this->_installAddNavMain();

        // Add new CONTENIDO sub navigations: *_nav_sub
        $this->_installAddNavSub();

        // Add specific sql queries
        $this->_installAddSpecificSql();

        // Add new CONTENIDO content types: *_type
        $this->_installAddContentTypes();

        // Add new modules
        $this->_installAddModules();

        // Add plugin dir for uploaded plugins
        if (parent::getMode() == 2) {
            $this->_installAddDir();
        }

        // Success message for new plugins
        if (parent::getMode() <= 2) {
            parent::info(i18n('The plugin has been successfully installed. To apply the changes please login into backend again.', 'pim'));
        }
    }

    /**
     * Check uuId: You can install a plugin only for one time
     *
     * @access private
     * @return void
     */
    private function _installCheckUuid() {
        $this->_PimPluginCollection->setWhere('uuid', parent::$_XmlGeneral->uuid);
        $this->_PimPluginCollection->query();
        if ($this->_PimPluginCollection->count() > 0) {
            parent::error(i18n('You can install this plugin only for one time.', 'pim'));
        }
    }

    /**
     * This function checks requirements for one plugin
     *
     * @access private
     * @return void
     */
    private function _installCheckRequirements() {

        // Get config variables
        $cfg = cRegistry::getConfig();

        // Check min CONTENIDO version
        if (version_compare($cfg['version'], parent::$_XmlRequirements->contenido->attributes()->minversion, '<')) {
            parent::error(i18n('You have to install CONTENIDO <strong>', 'pim') . parent::$_XmlRequirements->contenido->attributes()->minversion . i18n('</strong> or higher to install this plugin!', 'pim'));
        }

        // Check max CONTENIDO version
        if (parent::$_XmlRequirements->contenido->attributes()->maxversion) {

            if (version_compare($cfg['version'], parent::$_XmlRequirements->contenido->attributes()->maxversion, '>')) {
                parent::error(i18n('Your current CONTENIDO version is to new - max CONTENIDO version: ' . parent::$_XmlRequirements->contenido->attributes()->maxversion . '', 'pim'));
            }
        }

        // Check PHP version
        if (version_compare(phpversion(), parent::$_XmlRequirements->attributes()->php, '<')) {
            parent::error(i18n('You have to install PHP <strong>', 'pim') . parent::$_XmlRequirements->attributes()->php . i18n('</strong> or higher to install this plugin!', 'pim'));
        }

        // Check extensions
        if (count(parent::$_XmlRequirements->extension) != 0) {

            for ($i = 0; $i < count(parent::$_XmlRequirements->extension); $i++) {

                if (!extension_loaded(parent::$_XmlRequirements->extension[$i]->attributes()->name)) {
                    parent::error(i18n('The plugin could not find the PHP extension <strong>', 'pim') . parent::$_XmlRequirements->extension[$i]->attributes()->name . i18n('</strong>. Because this is required by the plugin, it can not be installed.', 'pim'));
                }
            }
        }

        // Check classes
        if (count(parent::$_XmlRequirements->class) != 0) {

            for ($i = 0; $i < count(parent::$_XmlRequirements->class); $i++) {

                if (!class_exists(parent::$_XmlRequirements->class[$i]->attributes()->name)) {
                    parent::error(i18n('The plugin could not find the class <strong>', 'pim') . parent::$_XmlRequirements->class[$i]->attributes()->name . i18n('</strong>. Because this is required by the plugin, it can not be installed.', 'pim'));
                }
            }
        }

        // Check functions
        if (count(parent::$_XmlRequirements->function) != 0) {

            for ($i = 0; $i < count(parent::$_XmlRequirements->function); $i++) {

                if (!function_exists(parent::$_XmlRequirements->function[$i]->attributes()->name)) {
                    parent::error(i18n('The plugin could not find the function <strong>', 'pim') . parent::$_XmlRequirements->function[$i]->attributes()->name . i18n('</strong>. Because this is required by the plugin, it can not be installed.', 'pim'));
                }
            }
        }
    }

    /**
     * Add entries at *_plugins
     *
     * @access private
     * @return void
     */
    private function _installAddPlugin() {
        // Add entry at *_plugins
        $pimPlugin = $this->_PimPluginCollection->create(parent::$_XmlGeneral->plugin_name, parent::$_XmlGeneral->description, parent::$_XmlGeneral->author, parent::$_XmlGeneral->copyright, parent::$_XmlGeneral->mail, parent::$_XmlGeneral->website, parent::$_XmlGeneral->version, parent::$_XmlGeneral->plugin_foldername, parent::$_XmlGeneral->uuid, parent::$_XmlGeneral->attributes()->active);

        // Get Id of new plugin
        $pluginId = $pimPlugin->get('idplugin');

        // Set pluginId
        parent::setPluginId($pluginId);

        // Set foldername of new plugin
        $this->_setPluginFoldername(parent::$_XmlGeneral->plugin_foldername);
    }

    /**
     * Get all area names from database
     *
     * @access private
     * @return void
     */
    private function _installFillAreas() {
        $oItem = $this->_ApiAreaCollection;
        $this->_ApiAreaCollection->select(null, null, 'name');
        while (($areas = $this->_ApiAreaCollection->next()) !== false) {
            $this->PluginInstalledAreas[] = $areas->get('name');
        }
    }

    /**
     * Add entries at *_area
     *
     * @access private
     * @return void
     */
    private function _installAddAreas() {

        // Initializing attribute array
        $attributes = array();

        // Get Id of plugin
        $pluginId = parent::_getPluginId();

        $areaCount = count(parent::$_XmlArea->area);
        for ($i = 0; $i < $areaCount; $i++) {

            $attributes = array();

            // Build attributes
            foreach (parent::$_XmlArea->area[$i]->attributes() as $key => $value) {
                $attributes[$key] = $value;
            }

            // Security check
            $area = cSecurity::escapeString(parent::$_XmlArea->area[$i]);

            // Add attributes "parent" and "menuless" to an array
            $attributes = array(
                'parent' => cSecurity::escapeString($attributes['parent']),
                'menuless' => cSecurity::toInteger($attributes['menuless'])
            );

            // Fix for parent attribute
            if (empty($attributes['parent'])) {
                $attributes['parent'] = 0;
            }

            // Create a new entry
            $item = $this->_ApiAreaCollection->create($area, $attributes['parent'], 1, 1, $attributes['menuless']);

            // Set a relation
            $this->_PimPluginRelationsCollection->create($item->get('idarea'), $pluginId, 'area');

            // Add new area to all area array
            $this->PluginInstalledAreas[] = $area;
        }
    }

    /**
     * Add entries at *_actions
     *
     * @access private
     * @return void
     */
    private function _installAddActions() {
        $actionCount = count(parent::$_XmlActions->action);
        for ($i = 0; $i < $actionCount; $i++) {

            $attributes = array();

            // Build attributes
            foreach (parent::$_XmlActions->action[$i]->attributes() as $key => $value) {
                $attributes[$key] = $value;
            }

            // Set relevant value if it is empty
            if (empty($attributes['relevant'])) {
                $attributes['relevant'] = 1;
            }

            // Add attributes "area" and "relevant" to an safe array
            $attributes = array(
                'area' => cSecurity::escapeString($attributes['area']),
                'relevant' => cSecurity::toInteger($attributes['relevant'])
            );

            // Security check for action name
            $action = cSecurity::escapeString(parent::$_XmlActions->action[$i]);

            // Check for valid area
            if (!in_array($attributes['area'], $this->_getInstalledAreas())) {
                parent::error(i18n('Defined area', 'pim') . ' <strong>' . $attributes['area'] . '</strong> ' . i18n('are not found on your CONTENIDO installation. Please contact your plugin author.', 'pim'));
            }

            // Create a new entry
            $this->_ApiActionCollection->create($attributes['area'], $action, '', '', '', $attributes['relevant']);
        }
    }

    /**
     * Add entries at *_frame_files and *_files
     *
     * @access private
     * @return void
     */
    private function _installAddFrames() {

        // Initializing attribute array
        $attributes = array();

        $frameCount = count(parent::$_XmlFrames->frame);
        for ($i = 0; $i < $frameCount; $i++) {

            // Build attributes with security checks
            foreach (parent::$_XmlFrames->frame[$i]->attributes() as $sKey => $sValue) {
                $attributes[$sKey] = cSecurity::escapeString($sValue);
            }

            // Check for valid area
            if (!in_array($attributes['area'], $this->_getInstalledAreas())) {
                parent::error(i18n('Defined area', 'pim') . ' <strong>' . $attributes['area'] . '</strong> ' . i18n('are not found on your CONTENIDO installation. Please contact your plugin author.', 'pim'));
            }

            // Create a new entry at *_files
            $file = $this->_ApiFileCollection->create($attributes['area'], $attributes['name'], $attributes['filetype']);

            // Create a new entry at *_frame_files
            if (!empty($attributes['frameId'])) {
                $this->_ApiFrameFileCollection->create($attributes['area'], $attributes['frameId'], $file->get('idfile'));
            }
        }
    }

    /**
     * Add entries at *_nav_main
     *
     * @access private
     * @return void
     */
    private function _installAddNavMain() {

        // Get Id of plugin
        $pluginId = parent::_getPluginId();

        $navCount = count(parent::$_XmlNavMain->nav);
        for ($i = 0; $i < $navCount; $i++) {
            // Security check
            $location = cSecurity::escapeString(parent::$_XmlNavMain->nav[$i]);

            // Create a new entry at *_nav_main
            $navMain = $this->_ApiNavMainCollection->create($location);

            // Set a relation
            $this->_PimPluginRelationsCollection->create($navMain->get('idnavm'), $pluginId, 'navm');
        }
    }

    /**
     * Add entries at *_nav_sub
     *
     * @access private
     * @return void
     */
    private function _installAddNavSub() {

        // Initializing attribute array
        $attributes = array();

        // Get Id of plugin
        $pluginId = parent::_getPluginId();

        $navCount = count(parent::$_XmlNavSub->nav);
        for ($i = 0; $i < $navCount; $i++) {

            // Build attributes
            foreach (parent::$_XmlNavSub->nav[$i]->attributes() as $key => $value) {
                $attributes[$key] = $value;
            }

            // Convert area to string
            $attributes['area'] = cSecurity::toString($attributes['area']);

            // Check for valid area
            if (!in_array($attributes['area'], $this->_getInstalledAreas())) {
                parent::error(i18n('Defined area', 'pim') . ' <strong>' . $attributes['area'] . '</strong> ' . i18n('are not found on your CONTENIDO installation. Please contact your plugin author.', 'pim'));
            }

            // Create a new entry at *_nav_sub
            $item = $this->_ApiNavSubCollection->create($attributes['navm'], $attributes['area'], $attributes['level'], parent::$_XmlNavSub->nav[$i], 1);

            // Set a relation
            $this->_PimPluginRelationsCollection->create($item->get('idnavs'), $pluginId, 'navs');
        }
    }

    /**
     * Add specific sql queries
     *
     * @access private
     * @return void
     */
    private function _installAddSpecificSql() {
        $cfg = cRegistry::getConfig();
        $db = cRegistry::getDb();

        if (parent::getMode() == 1) { // Plugin is already extracted
            $tempSqlFilename = $cfg['path']['contenido'] . $cfg['path']['plugins'] . $this->_getPluginFoldername() . DIRECTORY_SEPARATOR . 'plugin_install.sql';
        } elseif (parent::getMode() == 2 || parent::getMode() == 4) { // Plugin
                                                                      // is
                                                                      // uploaded
                                                                      // or /
                                                                      // and
                                                                      // update
                                                                      // mode
            $tempSqlFilename = parent::$_PimPluginArchiveExtractor->extractArchiveFileToVariable('plugin_install.sql', 0);
        }

        if (!cFileHandler::exists($tempSqlFilename)) {
            return;
        }

        $tempSqlContent = cFileHandler::read($tempSqlFilename);
        $tempSqlContent = str_replace("\r\n", "\n", $tempSqlContent);
        $tempSqlContent = explode("\n", $tempSqlContent);
        $tempSqlLines = count($tempSqlContent);

        $pattern = '/(CREATE TABLE IF NOT EXISTS|INSERT INTO|UPDATE|ALTER TABLE) ' . parent::$_SqlPrefix . '\b/';

        for ($i = 0; $i < $tempSqlLines; $i++) {
            if (preg_match($pattern, $tempSqlContent[$i])) {
                $tempSqlContent[$i] = str_replace(parent::$_SqlPrefix, $cfg['sql']['sqlprefix'] . '_pi', $tempSqlContent[$i]);
                $db->query($tempSqlContent[$i]);
            }
        }
    }

    /**
     * Add content types (*_type)
     *
     * @access private
     * @return void
     */
    private function _installAddContentTypes() {

        // Get Id of plugin
        $pluginId = parent::_getPluginId();

        $pattern = '/^CMS_.+/';

        $typeCount = count(parent::$_XmlContentType->type);
        for ($i = 0; $i < $typeCount; $i++) {

            $type = cSecurity::toString(parent::$_XmlContentType->type[$i]);

            if (preg_match($pattern, $type)) {

                // Create new content type
                $item = $this->_ApiTypeCollection->create($type, '');

                // Set a relation
                $this->_PimPluginRelationsCollection->create($item->get('idtype'), $pluginId, 'ctype');
            }
        }
    }

    /**
     * Add modules
     *
     * @access private
     * @return void
     */
    private function _installAddModules() {
        $cfg = cRegistry::getConfig();
        $module = new cApiModule();

        // Set path to modules path
        $modulesPath = $cfg['path']['contenido'] . $cfg['path']['plugins'] . $this->_getPluginFoldername() . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR;

        if (!is_dir($modulesPath)) {
            return false;
        }

        foreach (new DirectoryIterator($modulesPath) as $modulesFiles) {

            if (substr($modulesFiles->getBasename(), -4) == ".zip") {

                // Import founded module
                $module->import($modulesFiles->getBasename(), $modulesFiles->getBasename(), false);
            }
        }

        cFileHandler::recursiveRmdir($modulesPath);
    }

    /**
     * Add plugin dir
     *
     * @access private
     * @return void
     */
    private function _installAddDir() {
        $cfg = cRegistry::getConfig();

        // Build the new plugin dir
        $tempPluginDir = $cfg['path']['contenido'] . $cfg['path']['plugins'] . parent::$_XmlGeneral->plugin_foldername . DIRECTORY_SEPARATOR;

        // Set destination path
        try {
            parent::$_PimPluginArchiveExtractor->setDestinationPath($tempPluginDir);
        } catch (cException $e) {
            parent::$_PimPluginArchiveExtractor->destroyTempFiles();
        }

        // Extract Zip archive files into the new plugin dir
        try {
            parent::$_PimPluginArchiveExtractor->extractArchive();
        } catch (cException $e) {
            parent::$_PimPluginArchiveExtractor->destroyTempFiles();
        }
    }

}
?>