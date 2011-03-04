<?php

/**
 * NethGui
 *
 * @package NethGuiFramework
 * @author Giacomo Sanchietti 
 */

/**
 * Read and write parameters into SME DB
 *
 * @package NethGuiFramework
 * @subpackage TODO
 * TODO class documentation
 */
final class NethGui_Core_MockHostConfiguration implements NethGui_Core_HostConfigurationInterface, NethGui_Authorization_PolicyEnforcementPointInterface
{

    /**
     * @var PolicyDecisionPointInterface;
     */
    private $policyDecisionPoint;

    /**
    * @var SME DB database command
    **/
    private $command = "/usr/bin/sudo /sbin/e-smith/db";

    /**
    * @var $db DB name
    **/
    private $db = null;

    /**
    * @var $canRead
    **/
    private $canRead = FALSE;
    
    /**
    * @var $canWrite
    **/
    private $canWrite = FALSE;

    /**
     *
     * @var UserInterface
     */
    private $user;

    public function setPolicyDecisionPoint(NethGui_Authorization_PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
        $request = new NethGui_Authorization_AccessControlRequest($this->user, $this->db, 'READ');
        $response = $this->policyDecisionPoint->authorizeRequest($request);

        if ( $response )
	     $this->canRead = TRUE;

        $request = new NethGui_Authorization_AccessControlRequest($this->user, $this->db, 'WRITE');
        $response = $this->policyDecisionPoint->authorizeRequest($request);

        if ( $response )
             $this->canWrite = TRUE;

    }

    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    public function setUser(NethGui_Core_UserInterface $user)
    {
        $this->user = $user;
    }

    public function setDB($db)
    {
       if(!$db)
            throw new Exception("Can't find NethServer database");
        $this->db = $db;

        //TODO: eliminare quando funzioneranno i policyDecisionPoint
        $this->canRead = TRUE;
        $this->canWrite = TRUE;
    }

    /**
    * /sbin/e-smith/db dbfile get key
    */
    public function getKey($key)
    {
        if(!$this->db)
            throw new Exception("No database selected");
        
        if(!$this->canRead)
             throw new Exception("Permission Denied");

        $result = array();
        $output = shell_exec($this->command." ".$this->db." get ".escapeshellarg($key));
        if($output != "")
        {
            $tokens = split("\|",$output);
            for($i=1;$i<=count($tokens);$i++) //skip type
            {
                if(isset($tokens[$i])) //avoid outbound tokens
                    $result[trim($tokens[$i])]=trim($tokens[++$i]);
            }
        }
        return $result;
    }

    /** 
    * /sbin/e-smith/db dbfile set key type [prop1 val1] [prop2 val2] ...
    */
    public function setKey($key,$type,$props)
    { 
        if(!$this->db)
            throw new Exception("No database selected");
        
        if(!$this->canWrite)
             throw new Exception("Permission Denied");
       
        $params = " set ".escapeshellarg($key)." ".escapeshellarg($type)." ".$this->propsToString($props);
        exec($this->command." ".$this->db." $params", $output, $ret);
        return ($ret == 0);
    }

    /** 
    * /sbin/e-smith/db dbfile delete key
    */
    public function deleteKey($key)
    {
        if(!$this->db)
            throw new Exception("No database selected");
        
        if(!$this->canWrite)
             throw new Exception("Permission Denied");

        exec($this->command." ".$this->db." delete ".escapeshellarg($key), $output, $ret);
        return ($ret == 0);
    }

    /**
    * /sbin/e-smith/db dbfile gettype key
    */
    public function getType($key)
    {
        if(!$this->db)
            throw new Exception("No database selected");
        
        if(!$this->canRead)
             throw new Exception("Permission Denied");
        return trim(shell_exec($this->command." ".$this->db." gettype ".escapeshellarg($key)));
    }

    /**
    * /sbin/e-smith/db dbfile settype key type
    */
    public function setType($key,$type)
    {
        if(!$this->db)
            throw new Exception("No database selected");
        
        if(!$this->canWrite)
             throw new Exception("Permission Denied");

        exec($this->command." ".$this->db." settype ".escapeshellarg($key)." ".escapeshellarg($type),$ret);
        return ($ret == 0);
    }


    /**
    * /sbin/e-smith/db dbfile getprop key prop
    */
    public function getProp($key,$prop)
    {
        if(!$this->db)
            throw new Exception("No database selected");
        
        if(!$this->canRead)
             throw new Exception("Permission Denied");

        return trim(shell_exec($this->command." ".$this->db." getprop ".escapeshellarg($key)." ".escapeshellarg($prop)));
    }


    /**
    * /sbin/e-smith/db dbfile setprop key prop1 val1 [prop2 val2] [prop3 val3] ...
    */
    public function setProp($key,$props)
    {
        if(!$this->db)
            throw new Exception("No database selected");
        
        if(!$this->canWrite)
             throw new Exception("Permission Denied");

        $params = " setprop ".escapeshellarg($key)." ".$this->propsToString($props);
        exec($this->command." ".$this->db." $params ",$output, $ret);
        return ($ret == 0);
    }


    /**
    * sbin/e-smith/db dbfile delprop key prop1 [prop2] [prop3] ...
    */
    public function delProp($key,$props)
    {
        if(!$this->db)
            throw new Exception("No database selected");
        
        if(!$this->canWrite)
             throw new Exception("Permission Denied");

        $params = " delprop ".escapeshellarg($key)." ".join(" ",$props);
        exec($this->command." ".$this->db." $params", $output, $ret);
        return ($ret == 0);
    }
  

    private function propsToString($props)
    {
        $ret = "";
        foreach($props as $key=>$value)
             $ret .= " ".escapeshellarg($key)." ".escapeshellarg($value)." ";
        return $ret;
    }
}
