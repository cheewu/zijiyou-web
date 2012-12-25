<?php
if(!defined('IN_SYSTEM')) {	exit('Access Denied'); }




/**
 * Show Gearman Server Status
 *
 */
class GearmanServerStatus {

    /**
     * @var string
     */
    public $host = "127.0.0.1";
    /**
     * @var int
     */
    public $port = 4730;

    /**
     * @param string $host
     * @param int $port
     */
    public function __construct($host=null,$port=null){
        if( !is_null($host) ){
            $this->host = $host;
        }
        if( !is_null($port) ){
            $this->port = $port;
        }
    }

    /**
     * @return array | null
     */
    public function getStatus()
    {
        $status = null;
        $handle = fsockopen($this->host,$this->port,$errorNumber,$errorString,5);
        if($handle!=null){
            fwrite($handle,"status\n");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if( $line==".\n"){
                    break;
                }
                if( preg_match("~^(.*)[ \t](\d+)[ \t](\d+)[ \t](\d+)~", $line, $matches) ){
                    $function = $matches[1];
                    $status['operations'][$function] = array(
                        'function' => $function,
                        'total' => $matches[2],
                        'running' => $matches[3],
                        'connectedWorkers' => $matches[4],
                    );
                }
            }
            /*
            fwrite($handle,"workers\n");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if( $line==".\n"){
                    break;
                }
                // FD IP-ADDRESS CLIENT-ID : FUNCTION
                if( preg_match("~^(\d+)[ \t](.*?)[ \t](.*?) : ?(.*)~", $line, $matches) ){
                    $fd = $matches[1];
                    $status['connections'][$fd] = array(
                        'fd' => $fd,
                        'ip' => $matches[2],
                        'id' => $matches[3],
                        'function' => $matches[4],
                    );
                }
            }
            */
            fclose($handle);
        }

        return $status;
    }
    
    /**
     * Get Versions
     * @return string
     */
    public function getVersion()
    {
    	$handle = fsockopen($this->host, $this->port, $errorNumber, $errorString, 5);
        if( !$handle ) { return ''; }
        
        fwrite($handle, "version\n");
		$v = fgets($handle);
        fclose($handle);
        if( strlen($v) > 3 && substr($v, 0, 3) === 'OK ' ) {
        	return substr($v, 3);
        }
        return $v;
    }
    
}



/**
 * 输出Gearmand Status
 * Enter description here ...
 */
function echo_gearman_status($host=null, $port=null)
{
	/**
	 * Gearmand Server Status
	 * @see <http://gearman.org/index.php?id=protocol>
	 */
	
	$gearman = new GearmanServerStatus($host, $port);
	$status = $gearman->getStatus();
	
	
	echo '<html><head><title>Gearman Status</title>';
	echo "<style type='text/css'>body{font-family: 'Trebuchet MS';color: #444;background: #f9f9f9;width:960px;}h1{background: #eee;border: 1px solid #ddd;padding: 3px;text-shadow: #ccc 1px 1px 0;color: #756857;text-transform:uppercase;}h2{padding: 3px;text-shadow: #ccc 1px 1px 0;color: #ACA39C;text-transform:uppercase;border-bottom: 1px dotted #ddd;display: inline-block;}hr{color: transparent;}table{width: 100%;border: 1px solid #ddd;border-spacing:0px;}table th{border-bottom: 1px dotted #ddd;background: #eee;padding: 5px;font-size: 15px;text-shadow: #fff 1px 1px 0;}table td{text-align: center;padding: 5px;font-size: 13px;color: #444;text-shadow: #ccc 1px 1px 0;}</style>";
	echo '</head><body>';
	
	echo '<h1>Gearman Server Status for '.$gearman->host.':'.$gearman->port.'</h1>';
	
	echo 'version: '.$gearman->getVersion().'<br/>';
	
	/**
	workers
	
	    This sends back a list of all workers, their file descriptors,
	    their IPs, their IDs, and a list of registered functions they can
	    perform. The list is terminated with a line containing a single
	    '.' (period). The format is:
	
	    FD IP-ADDRESS CLIENT-ID : FUNCTION ...
	 */
	if( !empty($status['connections']) ) {
		echo '<h2>Workers</h2>';
		echo "<table border='0' style='width:800px'><tr><th>File Descriptor</th><th>IP Address</th><th>Client ID</th><th>Function</th></tr>";
		foreach ($status['connections'] as $_k=>$_v)
		{
			echo '<tr>';
				echo '<td>'.$_v['fd'].'</td>';
				echo '<td>'.$_v['ip'].'</td>';
				echo '<td>'.$_v['id'].'</td>';
				echo '<td>'.$_v['function'].'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	
	/**
	status
	
	    This sends back a list of all registered functions.  Next to
	    each function is the number of jobs in the queue, the number of
	    running jobs, and the number of capable workers. The columns are
	    tab separated, and the list is terminated with a line containing
	    a single '.' (period). The format is:
	
	    FUNCTION\tTOTAL\tRUNNING\tAVAILABLE_WORKERS
	 */
	if( !empty($status['operations']) ) {
		echo '<h2>Status</h2>';
		echo "<table border='0' style='width:800px'><tr><th>Function</th><th>Total</th><th>Running</th><th>Available Workers</th></tr>";
		foreach ($status['operations'] as $_k=>$_v)
		{
			echo '<tr>';
				echo '<td>'.$_v['function'].'</td>';
				echo '<td>'.$_v['total'].'</td>';
				echo '<td>'.$_v['running'].'</td>';
				echo '<td>'.$_v['connectedWorkers'].'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	
	echo '</body>';
	echo '</html>';
		
}