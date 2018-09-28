<?php
$fp=fopen("https://esuvidhabotapp.herokuapp.com/mpqr_private_key.pem","r");
$priv_key=fread($fp,filesize("https://esuvidhabotapp.herokuapp.com/mpqr_private_key.pem"));
fclose($fp);
// $passphrase is required if your key is encoded (suggested)
$res = openssl_get_privatekey($priv_key);
//echo $res;
class DumpHTTPRequestToFile {
	public function execute($targetFile) {
		$data = sprintf(
			"%s %s %s\n\nHTTP headers:\n",
			$_SERVER['REQUEST_METHOD'],
			$_SERVER['REQUEST_URI'],
			$_SERVER['SERVER_PROTOCOL']
		);
		$data .= "RES " . ': ' . $priv_key . "\n";
		foreach ($this->getHeaderList() as $name => $value) {
			if($name == "X-Mc-Qraccept-Webhook-Timestamp")
			{
				$webHookTimestamp = $value;
				$data .= $name . ': ' . $value . "\n";
			}
			if($name  == "X-Mc-Qraccept-Webhook-Key")
			{
				openssl_private_decrypt($value,$newsource,$res);
				$data .= $name . ': ' . $value . "\n";
				$data .= "Encrypted Key" . ': ' . $newsource . "\n";
			}
			if($name  == "X-Mc-Qraccept-Webhook-Nonce")
			{
				$webHookKey = $value;
				$data .= $name . ': ' . $value . "\n";
			}
		}
		$data .= "\nRequest body:\n";
		file_put_contents(
			$targetFile,
			$data . file_get_contents('php://input') . "\n"
		);
		echo("Done!\n\n");
	}
	private function getHeaderList() {
		$headerList = [];
		foreach ($_SERVER as $name => $value) {
			if (preg_match('/^HTTP_/',$name)) {
				// convert HTTP_HEADER_NAME to Header-Name
				$name = strtr(substr($name,5),'_',' ');
				$name = ucwords(strtolower($name));
				$name = strtr($name,' ','-');
				// add to list
				$headerList[$name] = $value;
			}
		}
		return $headerList;
	}
}
(new DumpHTTPRequestToFile)->execute('./mpqrfb.txt');
?>
