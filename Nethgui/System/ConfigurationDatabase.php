<?php
/**
 * Nethgui
 *
 * @package Core
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 */

/**
 * Read and write parameters into SME DB
 *
 * Ths class implements an interface to SME database executing the command /sbin/e-smith/db with sudo.
 * The class needs /etc/sudoers configurazione. In the sudoers file you must have something like this:
 * <code>
 * Cmnd_Alias SME = /sbin/e-smith/db, /sbin/e-smith/signal-event
 * www ALL=NOPASSWD: SME
 * </code>
 *
 * Before use any method in the class, the method st($db) must be called. 
 *
 * @package Core
 * 
 * 
 */
class Nethgui_System_ConfigurationDatabase implements Nethgui_Authorization_PolicyEnforcementPointInterface
{

    /**
     * @var PolicyDecisionPointInterface;
     */
    private $policyDecisionPoint;

    /**
     * @var SME DB database command
     * */
    private $command = "/usr/bin/sudo /sbin/e-smith/db";

    /**
     * @var $db Database name, it's translated into the db file path. For example: /home/e-smith/db/testdb
     * */
    private $db;

    /**
     * @var $canRead Read flag permission, it's true if the current user can read the database, false otherwise
     * */
    private $canRead = FALSE;

    /**
     * @var $canWrite Write flag permission, it's true if the current user can write the database, false otherwise
     * */
    private $canWrite = FALSE;

    /**
     * Keeps User object acting on this database. 
     * @var Nethgui_Core_UserInterface
     */
    private $user;

    /**
     * setPolicyDecisionPoint 
     * 
     * @param Nethgui_Authorization_PolicyDecisionPointInterface $pdp 
     * @access public
     * @return void
     */
    public function setPolicyDecisionPoint(Nethgui_Authorization_PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
        $this->authorizeDbAccess();
    }

    /**
     * Return current getPolicyDecisionPoint 
     * 
     * @access public
     * @return policyDecisionPoint
     */
    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    /**
     * Construct an object to access a SME Configuration database file
     * with $user's privileges.
     * 
     * @param string $database Database name
     */
    public function __construct($database, Nethgui_Core_UserInterface $user)
    {
        if ( ! $database)
            throw new Exception("You must provide a valid database name.");

        $this->db = $database;
        $this->user = $user;
    }

    private function authorizeDbAccess()
    {
        $requestRead = new Nethgui_Authorization_AccessControlRequest($this->user, $this->db, 'READ');
        $responseRead = $this->policyDecisionPoint->authorizeRequest($requestRead);
        if ($responseRead) {
            $this->canRead = TRUE;
        }

        $requestWrite = new Nethgui_Authorization_AccessControlRequest($this->user, $this->db, 'WRITE');
        $responseWrite = $this->policyDecisionPoint->authorizeRequest($requestWrite);
        if ($responseWrite) {
            $this->canWrite = TRUE;
        }
    }

    /**
     * Retrieve all keys from the database. If needed, you can use filter the results by type and key name. 
     *
     * @param string $type (optional) type of the key
     * @param string $filter (optional) case insensitive fulltext search on key value
     * @access public
     * @return array associative array in the form "[KeyName] => array( [type] => [TypeValue], [PropName1] => [PropValue1], [PropName2] => [PropValue2], ...) 
     */
    public function getAll($type = NULL, $filter = NULL)
    {
        if ( ! $this->canRead)
            throw new Exception("Permission Denied");

        $result = array();
        $output = shell_exec($this->command . " " . $this->db . " print");
        if ($output != "")
        {
            foreach (explode("\n", $output) as $line) {
                $line = trim($line);
                if ($line)
                {
                    $tokens = explode("=", $line);
                    $key = $tokens[0];
                    $tokens = explode("|", $tokens[1]);
                    if ( ! is_null($type) && $tokens[0] != $type)
                        continue;
                    if ( ! is_null($filter) && stristr($key, $filter) === FALSE)
                        continue;

                    $result[$key]['type'] = $tokens[0];
                    for ($i = 1; $i <= count($tokens); $i ++ ) { //skip type
                        if (isset($tokens[$i])) //avoid outbound tokens
                            $result[$key][trim($tokens[$i])] = trim($tokens[ ++ $i]);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Retrieve a key from the database. 
     * Act like : /sbin/e-smith/db dbfile get key
     *
     * @param string $key the key to read
     * @access public
     * @return array associative array in the form [PropName] => [PropValue]
     */
    public function getKey($key)
    {
        if ( ! $this->canRead)
            throw new Exception("Permission Denied");

        $result = array();
        $output = shell_exec($this->command . " " . $this->db . " get " . escapeshellarg($key));
        if ($output != "")
        {
            $tokens = explode("|", $output);
            for ($i = 1; $i <= count($tokens); $i ++ ) { //skip type
                if (isset($tokens[$i])) //avoid outbound tokens
                    $result[trim($tokens[$i])] = trim($tokens[ ++ $i]);
            }
        }
        return $result;
    }

    /**
     * Set a database key with type and properties.
     * Act like: /sbin/e-smith/db dbfile set key type [prop1 val1] [prop2 val2] ... 
     * 
     * @param string $key Key to write
     * @param string $type Type of the key
     * @param string $props Array of properties in the form [PropName] => [PropValue]
     * @access public
     * @return bool TRUE on success, FALSE otherwise
     *
     */
    public function setKey($key, $type, $props)
    {
        if ( ! $this->canWrite)
            throw new Exception("Permission Denied");

        $params = " set " . escapeshellarg($key) . " " . escapeshellarg($type) . " " . $this->propsToString($props);
        exec($this->command . " " . $this->db . " $params", $output, $ret);
        return ($ret == 0);
    }

    /**
     * Delete a key and all its properties 
     * Act like: /sbin/e-smith/db dbfile delete key
     * 
     * @param mixed $key 
     * @access public
     * @return void
     */
    public function deleteKey($key)
    {
        if ( ! $this->canWrite)
            throw new Exception("Permission Denied");

        exec($this->command . " " . $this->db . " delete " . escapeshellarg($key), $output, $ret);
        return ($ret == 0);
    }

    /**
     * Return the type of a key
     * Act like: /sbin/e-smith/db dbfile gettype key
     * 
     * @param string $key the key to retrieve
     * @access public
     * @return string the type of the key
     */
    public function getType($key)
    {
        if ( ! $this->canRead)
            throw new Exception("Permission Denied");
        return trim(shell_exec($this->command . " " . $this->db . " gettype " . escapeshellarg($key)));
    }

    /**
     * Set the type of a key 
     * Act like: /sbin/e-smith/db dbfile settype key type
     * 
     * @param string $key the key to change
     * @param string $type the new type
     * @access public
     * @return bool true on success, FALSE otherwise
     */
    public function setType($key, $type)
    {
        if ( ! $this->canWrite)
            throw new Exception("Permission Denied");

        exec($this->command . " " . $this->db . " settype " . escapeshellarg($key) . " " . escapeshellarg($type), $ret);
        return ($ret == 0);
    }

    /**
     * Read the value of the given property
     * Act like: /sbin/e-smith/db dbfile getprop key prop
     * 
     * @param string $key the parent property key
     * @param string $prop the name of the property
     * @access public
     * @return string the value of the property
     */
    public function getProp($key, $prop)
    {
        if ( ! $this->canRead)
            throw new Exception("Permission Denied");

        return trim(shell_exec($this->command . " " . $this->db . " getprop " . escapeshellarg($key) . " " . escapeshellarg($prop)));
    }

    /**
     * Set one or more properties under the given key
     * Act like: /sbin/e-smith/db dbfile setprop key prop1 val1 [prop2 val2] [prop3 val3] ...
     * 
     * @param string $key the property parent key
     * @param array $props an associative array in the form [PropName] => [PropValue]  
     * @access public
     * @return bool TRUE on success, FALSE otherwise
     */
    public function setProp($key, $props)
    {
        if ( ! $this->canWrite)
            throw new Exception("Permission Denied");

        $params = " setprop " . escapeshellarg($key) . " " . $this->propsToString($props);
        exec($this->command . " " . $this->db . " $params ", $output, $ret);
        return ($ret == 0);
    }

    /**
     * Delete one or more properties under the given key 
     * Act like: sbin/e-smith/db dbfile delprop key prop1 [prop2] [prop3] ...
     * 
     * @param string $key the property parent key
     * @param array $props a simple array containg the properties to be deleted
     * @access public
     * @return bool TRUE on success, FALSE otherwise
     */
    public function delProp($key, $props)
    {
        if ( ! $this->canWrite)
            throw new Exception("Permission Denied");

        $params = " delprop " . escapeshellarg($key) . " " . join(" ", $props);
        exec($this->command . " " . $this->db . " $params", $output, $ret);
        return ($ret == 0);
    }

    /**
     * Transform an associative array in the form [PropName] => [PropValue] into a string "PropName PropValue". The function escapes all values to prevent shell injection 
     * 
     * @param array $props in the form [PropName] => [PropValue]
     * @access private
     * @return string a safe string like "PropName PropValue ..."
     */
    private function propsToString($props)
    {
        $ret = "";
        foreach ($props as $key => $value)
            $ret .= " " . escapeshellarg($key) . " " . escapeshellarg($value) . " ";
        return $ret;
    }

}
