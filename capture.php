/*
 *    This code was made in 2014.
 *
 *
 */

<?php

//Oppretter forbindelse med databasen.
include 'databasetilkobling.php';
?>

// Setter standard HTML-innstillinger
<html>
  <head>
    <title>Capture NETS transactions</title>
    <meta charset="UTF-8">
 </head>
 <body>
 
<?php

// Dette er parametre vi har fått fra NETS for å identifisere oss.
$merchantId = 528965;
$token = urlencode("p!2BF6j*");

// Legger spørringen i egen variabel for å få god oversikt.
$sql = "SELECT TransactionID, Capture FROM capture WHERE Capture > '0'";

// Utfører spørringen mot databasen ved å bruke funksjonen mysql_query.
$query = mysql_query($sql)
    or die (mysql_error());
    
// Nå skal vi hente ut spørringen og legge den i en array med funksjonen mysql_fetch_array.
// Når man vil hente ut alle radene er det vanlig å spise alle ved å la spørringen gå i en loop helt til det ikke er fler rader.
// Legger arrayen i en variabel, $orderInfo, fordi jeg senere skal hente ut data fra spørringen.

while ($orderInfo = mysql_fetch_array($query)) {

/*
 * Tolk alt dette inni loopen som at jeg vil hente ut en rad, og utføre en betaling
 * Dataene i arrayen blir organisert etter overskriftene i databasen, som er TransactionID og Capture
 * Legger da transaksjons-ID og beløp i en variabel til bruk senere.
 * NETS krever sum med to desimaler, dog uten desimaler. Sum 300 blir 30000, derfor gange med 100.
 * Hadde jeg lagt transactions-ID rett i URL-en som skal brukes senere, hadde det ikke fungert.
 * Må urlencode denne.
 */
	
	$transactionId = $orderInfo['TransactionID'];
	$captureAmount = $orderInfo['Capture'] * 100;
	$transactionId_URL = urlencode($transactionId);
	
$xml = simplexml_load_string(file_get_contents('https://epayment.nets.eu/Netaxept/Process.aspx?merchantId='.$merchantId.'&token='.$token.'&transactionId='.$transactionId_URL.'&transactionAmount='.$captureAmount.'&operation=CAPTURE'));
	
 // Basically får jeg PHP til å kjøre websiden (load_string) epayment... og så vil jeg gjerne hente responskoden som NETS sender tilbake, bruker file_get_contents.
 // Lagrer responskoden i $captureCode for å vise den senere
 
	$captureCode = $xml->ResponseCode;
	
 // Dersom responsekoden er OK, får jeg tilbakemelding om at beløpet er captured. Hvis ikke vil jeg gjerne vise capturecode og feilmelding.
	
	if($captureCode == "OK"){
		echo '<font color="green">' . $captureAmount . ' captured successfully with transaction ID ' . $transactionId . '.</font><br /><br />';
			} else {
			
		$captureCode = $xml->Error->Result->ResponseCode;
		$captureText = $xml->Error->Result->ResponseText;

		echo '<font color="red">Transaction of ' . $orderInfo['Capture'] . ' with transaction ID </font><b>' . $transactionId . '</b><font color="red"> failed to be captured.</font><br />';
		echo 'CAPTURE code: '.$captureCode . '<br />';
		echo 'CAPTURE response: ' . $captureText . '<br /><br />';

		}
	}
	

?>

 </body>
</html>


		
		
