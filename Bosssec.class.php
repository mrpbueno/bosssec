<?php
/**
 * Boss-Secretary Module for FreePBX.
 *
 * This module allows creating call forwarding rules from a boss extension
 * to a secretary extension, with a whitelist for calls that can be
 * directed straight to the boss.
 *
 * @package   FreePBX\modules
 * @author    mrpbueno
 * @copyright 2025 https://github.com/mrpbueno/bosssec
 * @license   GPLv3
 */

namespace FreePBX\modules;
use FreePBX\BMO;
use FreePBX\FreePBX_Helpers;
use PDO;
use Exception;

/**
 * Main class for the Boss-Secretary module.
 *
 * Implements the business logic, database interactions,
 * dialplan hooks, and the user interface for the module.
 */
class Bosssec extends FreePBX_Helpers implements BMO
{
    /**
     * Class constructor.
     *
     * Initializes the object, checks for the FreePBX instance, and assigns
     * the database object.
     *
     * @param object|null $freepbx The FreePBX object instance.
     * @throws \Exception If the FreePBX object is not provided.
     */
    public function __construct($freepbx = null)
    {
        if ($freepbx == null) {
            throw new \Exception("Not given a FreePBX Object");
        }
        $this->FreePBX = $freepbx;
        $this->db = $freepbx->Database;
    }

    /**
     * Module installation method.
     *
     * Called when the module is installed or upgraded.
     * Can be used to create tables, set initial configurations, etc.
     */
    public function install() {}

    /**
     * Module uninstallation method.
     *
     * Called when the module is removed.
     * Should clean up any created data, settings, or files.
     */
    public function uninstall() {}

    /**
     * Processes form submissions (Add, Edit, Delete).
     *
     * This method is called by FreePBX before the module page is rendered.
     * It handles POST/GET actions to add, edit, or delete rules.
     *
     * @param string $page The name of the current page being displayed.
     */
    public function doConfigPageInit($page) 
    {
        $action = $this->getReq('action', '');
        $id = (int) $this->getReq('id', 0);
        $redirect_url = 'config.php?display=' . $page;
        if (empty($action)) {
            return;
        }

        $data = [
            'boss_name' => $this->getReq('boss_name'),
            'boss_extension' => $this->getReq('boss_extension'),
            'secretary_extension' => $this->getReq('secretary_extension'),
            'whitelist' => $this->getReq('whitelist'),
            'enabled' => (int) $this->getReq('enabled', 1)
        ];

        if ($action === 'add' || $action === 'edit') {
            $exclude_id = ($action === 'edit') ? $id : null;
            if ($this->is_duplicate_boss($data['boss_extension'], $exclude_id)) {
                $_SESSION['toast_message'] = ['message' => _("The selected boss extension is already in use by another rule."), 'title' => _('Duplicate Boss'), 'level' => 'error'];
                redirect($redirect_url);
                return;
            }
        }

        $success = false;

        switch ($action) {
            case 'add':
                if ($this->add_config($data)) {
                    needreload();
                    $success = true;
                    $_SESSION['toast_message'] = ['message' => _('Rule added successfully!'), 'title' => _('Success'), 'level' => 'success'];
                }                
                break;
            case 'edit':
                if ($this->update_config($id, $data)) {
                    needreload();
                    $success = true;
                    $_SESSION['toast_message'] = ['message' => _('Rule successfully updated!'), 'title' => _('Success'), 'level' => 'success'];
                }                
                break;
            case 'delete':
                if ($this->delete_config($id)) {
                    needreload();
                    $success = true;
                    $_SESSION['toast_message'] = ['message' => _('Rule successfully deleted!'), 'title' => _('Success'), 'level' => 'success'];
                }                
                break;
        }

        if (!$success) {
            $_SESSION['toast_message'] = ['message' => _('An error occurred while processing the request.'), 'title' => _('Error'), 'level' => 'error'];
        }

        redirect($redirect_url);
    }

    /**
     * Sets permissions for AJAX requests.
     *
     * This method is a BMO hook that authorizes specific AJAX requests.
     * Returns true to allow access or false to deny it.
     *
     * @param string $req The requested AJAX command.
     * @param array  &$settings The settings associated with the request.
     * @return bool True if the request is allowed, false otherwise.
     */
    public function ajaxRequest($req, &$settings) 
    {        
        if ($req == 'getjson') {
            return true;
        }
        return false;
    }

    /**
     * Handles AJAX requests.
     *
     * This method is called to process the logic of an authorized AJAX request.
     * It checks the 'jdata' parameter and, if it is 'grid', returns the configuration data.
     *
     * @return array|bool The configuration data as an array, or false if the request is invalid.
     */
    public function ajaxHandler() 
    {
        if ($_REQUEST['jdata'] != 'grid') {
            return false;
        }
        return $this->get_configs();
    }

    /**
     * Gets the action bar buttons for the module page.
     *
     * This BMO hook defines the buttons (Delete, Reset, Submit)
     * to be displayed in the action bar, depending on the current view (form or grid).
     *
     * @param array $request The request array (usually $_REQUEST).
     * @return array An array containing the button definitions.
     */
    public function getActionBar($request)
    {
        switch($request['display']) {
            case 'bosssec':
                $buttons = [
                    'delete' => ['name' => 'delete', 'id' => 'delete', 'value' => _('Delete'),],
                    'reset' => ['name' => 'reset', 'id' => 'reset', 'value' => _("Reset"),],
                    'submit' => ['name' => 'submit', 'id' => 'submit', 'value' => _("Submit"),],
                ];

                if (!isset($request['id']) || trim($request['id']) == '') {
                    unset($buttons['delete']);
                }
                if (empty($request['view']) || $request['view'] != 'form') {
                    $buttons = [];
                }
                break;
        }
        return $buttons;
    }    

    /**
     * Fetches all Boss-Secretary rule configurations from the database.
     *
     * @return array An associative array with all the rules.
     */
    public function get_configs() 
    {
        $sql = "SELECT id, boss_name, boss_extension, secretary_extension, enabled FROM bosssec_config";
        return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetches a specific rule configuration by its ID.
     *
     * @param int $id The ID of the rule to fetch.
     * @return array|false An associative array with the rule data or false if not found.
     */
    public function get_config($id) 
    {
        $sql = "SELECT * FROM bosssec_config WHERE id = ?";
        $sth = $this->db->prepare($sql);
        $sth->execute([$id]);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Adds a new Boss-Secretary rule to the database.
     *
     * @param array $data The data for the new rule to be inserted.
     * @return bool True on success, false on error.
     */
    public function add_config($data) 
    {
        try {
            $sql = "INSERT INTO bosssec_config (boss_name, boss_extension, secretary_extension, whitelist, enabled) VALUES (:boss_name, :boss_extension, :secretary_extension, :whitelist, :enabled)";
            $sth = $this->db->prepare($sql);
            $sth->bindValue(':boss_name', $data['boss_name']);
            $sth->bindValue(':boss_extension', $data['boss_extension']);
            $sth->bindValue(':secretary_extension', $data['secretary_extension']);
            $sth->bindValue(':whitelist', $data['whitelist']);
            $sth->bindValue(':enabled', $data['enabled'], \PDO::PARAM_INT);
            return $sth->execute();
        } catch (\PDOException $e) {
            freepbx_log(FPBX_LOG_ERROR, _("Error adding Boss-Secretary rule: ") . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing Boss-Secretary rule in the database.
     *
     * @param int   $id   The ID of the rule to be updated.
     * @param array $data The new data for the rule.
     * @return bool True on success, false on error.
     */
    public function update_config($id, $data) 
    {
        try {
            $sql = "UPDATE bosssec_config SET boss_name = :boss_name, boss_extension = :boss_extension, secretary_extension = :secretary_extension, whitelist = :whitelist, enabled = :enabled WHERE id = :id";
            $sth = $this->db->prepare($sql);
            $sth->bindValue(':boss_name', $data['boss_name']);
            $sth->bindValue(':boss_extension', $data['boss_extension']);
            $sth->bindValue(':secretary_extension', $data['secretary_extension']);
            $sth->bindValue(':whitelist', $data['whitelist']);
            $sth->bindValue(':enabled', $data['enabled'], \PDO::PARAM_INT);
            $sth->bindValue(':id', $id, \PDO::PARAM_INT);
            return $sth->execute();
        } catch (\PDOException $e) {
            freepbx_log(FPBX_LOG_ERROR, _("Error updating Boss-Secretary rule: ") . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a Boss-Secretary rule from the database.
     *
     * @param int $id The ID of the rule to be deleted.
     * @return bool True on success, false on error.
     */
    public function delete_config($id) 
    {
        try {
            $sql = "DELETE FROM bosssec_config WHERE id = :id";
            $sth = $this->db->prepare($sql);
            $sth->bindValue(':id', $id, \PDO::PARAM_INT);
            return $sth->execute();
        } catch (\PDOException $e) {
            freepbx_log(FPBX_LOG_ERROR, _("Error deleting Boss-Secretary rule: ") . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a boss extension already exists in the database.
     *
     * @param string $boss_extension The boss extension to check.
     * @param int|null $exclude_id   An optional ID to exclude from the check (used for updates).
     * @return bool True if a duplicate is found, false otherwise.
     */
    private function is_duplicate_boss($boss_extension, $exclude_id = null)
    {
        $sql = "SELECT id FROM bosssec_config WHERE boss_extension = :boss_extension";
        if ($exclude_id !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':boss_extension', $boss_extension);
        if ($exclude_id !== null) {
            $sth->bindValue(':exclude_id', $exclude_id, \PDO::PARAM_INT);
        }
        $sth->execute();
        return $sth->fetchColumn() !== false;
    }

    /**
     * Signals FreePBX that this module needs to interact with the dialplan.
     *
     * This BMO hook, by returning true, informs FreePBX that the `doDialplanHook`
     * method should be called during dialplan generation.
     *
     * @return bool Always returns true to enable the dialplan hook.
     */
    public function myDialplanHooks()
    {
        return true;
    }

    /**
     * Adds the custom Boss-Secretary logic to the Asterisk dialplan.
     *
     * This method is the core of the module, where the call forwarding logic is
     * effectively inserted into the Asterisk dialplan. It intercepts calls
     * to the boss's extension and forwards them to the secretary, unless the
     * originating number is on the whitelist.
     *
     * @param object $ext    The dialplan object (`extensions`) to add the logic to.
     * @param string $engine The name of the telephony engine (always 'asterisk').
     * @param int    $priority The execution priority of the hook.
     */
    public function doDialplanHook(&$ext, $engine, $priority)
    {
        $configs = $this->db->query("SELECT boss_extension, secretary_extension, whitelist FROM bosssec_config WHERE enabled = 1")->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($configs)) {
            return;
        }

        foreach ($configs as $config) {
            $boss = $config['boss_extension'];
            $secretary = $config['secretary_extension'];
            $whitelist_str = $config['whitelist'];
            $subroutine_context = 'boss-secretary-' . $boss;
            $ext->splice('ext-local', $boss, 1, new \ext_goto(1, 's', $subroutine_context));            
            $ext->add($subroutine_context, 's', 1, new \ext_noop("Call to Boss {$boss}. Checking CallerID: \${CALLERID(num)}"));            
            $whitelist_array = array_filter(array_map('trim', preg_split('/[\s,]+|\r\n|\r|\n/', $whitelist_str)));
            $whitelist_array[] = $secretary;
            $whitelist_regex = implode('|', array_unique($whitelist_array));            
            if (!empty($whitelist_regex)) {
                $ext->add($subroutine_context, 's', '', new \ext_setvar('CS_RESULT', "\${REGEX(\"{$whitelist_regex}\",\"\${CALLERID(num)}\")}"));
                $ext->add($subroutine_context, 's', '', new \ext_gotoif('$["${CS_RESULT}" = "1"]', 'route-to-boss', 'route-to-secretary'));
            } else {
                $ext->add($subroutine_context, 's', '', new \ext_goto('route-to-secretary'));
            }            
            $ext->add($subroutine_context, 's', 'route-to-boss', new \ext_noop("Whitelist OK. Returning call to ext-local,{$boss},3"));            
            $ext->add($subroutine_context, 's', '', new \ext_goto(3, $boss, 'ext-local'));            
            $ext->add($subroutine_context, 's', 'route-to-secretary', new \ext_noop("Forwarding call to ext-local,{$secretary},1"));
            $ext->add($subroutine_context, 's', '', new \ext_goto(1, $secretary, 'ext-local'));
        }
    }

    /**
     * Renders and displays the content of the module's configuration page.
     *
     * This method determines which view should be shown to the user:
     * the grid with the list of rules or the form to add/edit a rule.
     *
     * @return string The full HTML content of the page to be rendered by FreePBX.
     */
    public function showPage()
    {
        $view = isset($_GET['view']) ? $_GET['view'] : 'grid';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

        $content = '';
        $subhead = '';

        if ($view === 'form') {
            $vars = [];
            if ($id) {
                $subhead = _('Edit Boss-Secretary Rule');
                $vars = $this->get_config($id);
            } else {
                $subhead = _('Add New Boss-Secretary Rule');
            }            
            if (function_exists('core_devices_list')) {                
                $vars['devices'] = core_devices_list();
            } else {                
                $vars['devices'] = [];
            }
            $content = load_view(__DIR__ . '/views/form.php', $vars);
        } else {
            $subhead = _('Boss-Secretary Rule List');
            $content = load_view(__DIR__ . '/views/grid.php');
        }
        
        return load_view(__DIR__ . '/views/default.php', array(
            'subhead' => $subhead,
            'content' => $content
        ));
    }
}
