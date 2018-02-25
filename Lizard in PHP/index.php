<?php
include("lizard.php");

$key = "";
if (isset($_GET['key'])) $key = preg_replace('/[^0-9a-f]/', '', strtolower(trim($_GET['key'])));

$iv = "";
if (isset($_GET['iv'])) $iv = preg_replace('/[^0-9a-f]/', '', strtolower(trim($_GET['iv'])));

$minlength = 1;
$maxlength = 10000;
$length = 128;
if (isset($_GET['length'])) $length = preg_replace('/[^0-9]/', '', trim($_GET['length']));
if ($length < $minlength) $length = $minlength;
elseif ($length > $maxlength) $length = $maxlength;

$testformjump = "";
$keystream = "";

if (strlen($key) == 30 && strlen($iv) == 16) {
	$Lizard = new Lizard(Lizard::hex2binArray($key), Lizard::hex2binArray($iv), $length);
	$keystream = $Lizard->getKeystream();

	if (isset($_GET['api'])) {
		header('Content-Type: application/json');
		echo json_encode($keystream);
		exit;
	}

	$keystream = "0x".strtoupper(Lizard::binArray2hex($keystream));
}
elseif ($key == "" && $iv == "") {
	$key = array();
	$iv = array();

	for ($i = 0; $i <= 119; $i++) $key[$i] = rand(0,1);
	for ($i = 0; $i <= 63; $i++) $iv[$i] = rand(0,1);

	$Lizard = new Lizard($key, $iv, $length);
	$keystream = $Lizard->getKeystream();
	
	$key = Lizard::binArray2hex($key);
	$iv = Lizard::binArray2hex($iv);
	$keystream = "0x".strtoupper(Lizard::binArray2hex($keystream));	
}

if (isset($_GET['key']) || isset($_GET['iv']) || isset($_GET['length'])) $testformjump = ' onload="location.href=\'#testform\'"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Lizard in PHP">
	<meta name="author" content="Christian A. Gorke">
	<link rel="icon" href="img/favicon.ico">
	<title>Lizard</title>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<style type="text/css">
	html {
		position: relative;
		min-height: 100%;
	}
	body {
		margin-top: 15px;
		margin-bottom: 10px;
	}
	.container {
		width: auto;
		max-width: 680px;
		padding: 0 15px;
	}
	pre{
		display: block;
		font-family: monospace;
		padding: 9.5px;
		margin: 0 0 10px;
		font-size: 13px;
		line-height: 1.42857143;
		color: #333;
		word-break: break-all;
		word-wrap: break-word;
		background-color: #f5f5f5;
		border: 1px solid #ccc;
		border-radius: 4px;
	}
	pre code {
		line-height: 2;
	}
	.hexinput {
		font-family: monospace;
	}
</style>
</head>
<body class="bg-light"<?php echo $testformjump?>>
<main role="main" class="container">
<img class="d-block mx-auto mb-5" src="img/lizard_icon.png" alt="" width="90" height="90">
<h1>Lizard in PHP</h1>
<p class="lead">This is an implementation of the lightweight stream cipher <a href="https://tosc.iacr.org/index.php/ToSC/article/view/584">Lizard</a> in <a href="https://www.php.net">PHP</a>.</p>
<p>The authors of Lizard are Matthias Hamann, Matthias Krause, and Willi Meier. The author of Lizard in PHP is <a href="https://www.christiangorke.de">Christian A. Gorke</a>.</p>

<h2 class="mt-5"><a name="testform"></a>Test Form</h2>
<p>Here you can test Lizard in PHP with your own key and initialization vector.
The keystream will be generated and displayed after you pressed the submit button.</p>
<p>Input formats must be 120 bit key as 30 hex values and 64 bit IV as 16 hex values.
The keystream output length can be chosen between 1 bit and <?php echo $maxlength; ?> bits.</p>
<p>This form works completely without JavaScript, making it as accessible as possible.
Hence, you have to check the correctness of the values by yourself.
Furthermore, you can directly fill the values in the input fields below by passing arguments to the variables <code>key</code>, <code>iv</code>, and <code>length</code> in the adress bar of your browser.
If no values for key and IV are input at the same time, they get filled with randomly chosen values.</p>
<form method="get">
	<div class="form-group row">
		<label for="key" class="col-sm-2 col-form-label">Key</label>
		<div class="input-group col-sm-7 hexinput">
			<div class="input-group-prepend">
				<div class="input-group-text">0x</div>
			</div>
			<input type="text" class="form-control" name="key" id="key" maxlength="30" value="<?php echo $key; ?>" placeholder="Enter key">
		</div>
	</div>
	<div class="form-group row">
		<label for="iv" class="col-sm-2 col-form-label">IV</label>
		<div class="input-group col-sm-5 hexinput">
			<div class="input-group-prepend">
				<div class="input-group-text">0x</div>
			</div>
			<input type="text" class="form-control" name="iv" id="iv" maxlength="16" value="<?php echo $iv; ?>" placeholder="Enter IV">
		</div>
	</div>
	<div class="form-group row">
		<label for="length" class="col-sm-2 col-form-label">Length</label>
		<div class="input-group col-sm-3">			
			<input type="text" class="form-control" name="length" id="length" maxlength="<?php echo strlen((string) $maxlength); ?>" value="<?php echo $length; ?>" required min="<?php echo $minlength; ?>" max="<?php echo $maxlength; ?>">
			<div class="input-group-append">
				<div class="input-group-text">Bits</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label for="keystream">Resulting Keystream</label>
		<textarea class="form-control hexinput" id="keystream" rows="<?php echo strlen((string) $maxlength)+1; ?>" readonly><?php echo $keystream; ?></textarea>
	</div>
	<button type="submit" class="btn btn-primary">Submit</button>
</form>

<h2 class="mt-5">API</h2>
<p>You can fetch the keystream generated by Lizard in PHP directly by accessing its API at
<pre><code><?php echo $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); ?></code></pre>
Send the following parameters as HTTP GET request and you will receive as response a JSON array consisting of the number of requested keystream bits.
The parameter values are case insensitive.</p>
<table class="table">
	<thead>
		<th scope="col">Parameter</th>
		<th scope="col">Type</th>
		<th scope="col">Values</th>
		<th scope="col">Description</th>
	</thead>
	<tbody>
		<tr>
			<th scope="row">api</th>
			<td><i>any</i></td>
			<td><i>any</i></td>
			<td>This parameter must be set</td>
		</tr>
		<tr>
			<th scope="row">key</th>
			<td>string(30)</td>
			<td>[0-9a-f]</td>
			<td>Key as hex value without 0x prefix</td>
		</tr>
		<tr>
			<th scope="row">iv</th>
			<td>string(16)</td>
			<td>[0-9a-f]</td>
			<td>IV as hex value without 0x prefix</td>
		</tr>
		<tr>
			<th scope="row">length</th>
			<td>integer(<?php echo strlen((string) $maxlength); ?>)</td>
			<td><?php echo $minlength; ?>-<?php echo $maxlength; ?></td>
			<td>Returned keystream length in bits</td>
		</tr>
	</tbody>
</table>
<p>Try <a href="?key=123abc456def789123abc456def789&iv=1515151515151515&length=16&api">this example link</a> to see the API response in action.</p>

<h2 class="mt-5">Usage in PHP</h2>
<p>First, include the <code>lizard.php</code> file which contains the Lizard php class in your main php file.</p>
<p>Lizard in PHP comes with two modes to generate keystream bits: <i>a priori known keystream length</i> and <i>on the fly generated keystream</i>.
The output is given as an array consisting of the generated keystream bits.
To transform the array into a hex value, the static function <code>binArray2hex()</code> is provided by Lizard in PHP.
If you want to pass parameters to Lizard, you need to transform them first into an array of bits each, which may be done using the static function <code>hex2binArray()</code>.</p>
<p>The <code>Lizard</code> class is designed to be be easily extendable regarding output and encryption functions, feel free to add your own requirements.</p>

<h3 class="mt-4">A Priori Known Keystream Length</h3>
<p>For a given key <code>$key</code> (input format: array of 120 bits), initialization vector <code>$iv</code> (input format: array of 64 bits), and keystream output length <code>$length</code> (input format: non-negative integer), a keystream depending on these three values is generated by the Lizard algorithm.
The result can be fetched using <code>$Lizard->getKeystream()</code>.</p>
<pre>
<?php echo preg_replace('#\R+#', '', highlight_string('<?php
$Lizard = new Lizard($key, $iv, $length);
$keystream = $Lizard->getKeystream();
?>', TRUE)); ?>
</pre>

<h3 class="mt-4">On the Fly Generated Keystream</h3>
<p>For a given key <code>$key</code> (input format: array of 120 bits) and initialization vector <code>$iv</code> (input format: array of 64 bits) an instance of the <code>Lizard</code> class is generated.
Then, using <code>$Lizard->keystreamGeneration($x)</code> generates <code>$x</code> (input format: non-negative integer) bits of the keystream which can be used multiple times.
Any further execution of <code>$Lizard->keystreamGeneration($y)</code> will return the next <code>$y</code> (input format: non-negative integer) keystream bits which will follow after the first <code>$x</code> bits.</p>
<pre>
<?php echo preg_replace('#\R+#', '', highlight_string('<?php
$Lizard = new Lizard($key, $iv);
$Lizard->keystreamGeneration(100);                  // generate 100 bit keystream
$keystream1 = $Lizard->getKeystream();
$Lizard->keystreamGeneration(100);                  // generate next 100 bit keystream
$keystream2 = $Lizard->getKeystream();
$keystream = array_merge($keystream1, $keystream2); // concatenate all 200 bits keystream
?>', TRUE)); ?>
</pre>

<h2 class="mt-5">Source Code</h2>
<p>The source code of Lizard in PHP is hosted on <a href="https://github.com/CAR/Lizard">GitHub</a>. You can access is there for free.</p>

<h2 class="mt-5">Test Vectors</h2>
<p>These are the test vectors given in the Lizard publication.
The values appended by "(reference)" represent the reference value of the publication, while the rest is computed on the fly by Lizard in PHP.
If everything is implemented correctly, these values must match.</p>

<h3 class="mt-4">Test Vector 1</h3>
<?php
$key = array();
$iv = array();
$length = 128;

/* Test Vector 1 */
for ($i = 0; $i <= 119; $i++) $key[$i] = 0;
for ($i = 0; $i <= 63; $i++) $iv[$i] = 0;

$Lizard = new Lizard($key, $iv, $length);
$keystream = $Lizard->getKeystream();
?>
<pre>
Key:                   0x<?php echo strtoupper(Lizard::binArray2hex($key)); ?><br>
Key (reference):       0x000000000000000000000000000000<br>
IV:                    0x<?php echo strtoupper(Lizard::binArray2hex($iv)); ?><br>
IV (reference):        0x0000000000000000<br>
Keystream:             0x<?php echo strtoupper(Lizard::binArray2hex($keystream)); ?><br>
Keystream (reference): 0xB6304CA4CA276B3355EC2E10968E84B3
</pre>

<h3 class="mt-4">Test Vector 2</h3>
<?php
$key = array();
$iv = array();
$length = 128;

/* Test Vector 2 */
for ($i = 0; $i <= 63; $i++) $key[$i] = 0;
for ($i = 64; $i <= 119; $i++) $key[$i] = 1;
for ($i = 0; $i <= 63; $i++) $iv[$i] = 1;

$Lizard = new Lizard($key, $iv, $length);
$keystream = $Lizard->getKeystream();
?>
<pre>
Key:                   0x<?php echo strtoupper(Lizard::binArray2hex($key)); ?><br>
Key (reference):       0x0000000000000000FFFFFFFFFFFFFF<br>
IV:                    0x<?php echo strtoupper(Lizard::binArray2hex($iv)); ?><br>
IV (reference):        0xFFFFFFFFFFFFFFFF<br>
Keystream:             0x<?php echo strtoupper(Lizard::binArray2hex($keystream)); ?><br>
Keystream (reference): 0x4D190941816F942358F0D164F4ECEB09
</pre>

<h3 class="mt-4">Test Vector 3</h3>
<?php
$key = array();
$iv = array();
$length = 128;

/* Test Vector 3 */
for ($i = 0; $i <= 29; $i++) {
	$n = sprintf("%04d", decbin($i%16));
	$key[$i*4+0] = (int)$n[0];
	$key[$i*4+1] = (int)$n[1];
	$key[$i*4+2] = (int)$n[2];
	$key[$i*4+3] = (int)$n[3];
}
for ($i = 0; $i <= 15; $i++) {
	$n = sprintf("%04d", decbin(($i+10)%16));
	$iv[$i*4+0] = (int)$n[0];
	$iv[$i*4+1] = (int)$n[1];
	$iv[$i*4+2] = (int)$n[2];
	$iv[$i*4+3] = (int)$n[3];
}

$Lizard = new Lizard($key, $iv, $length);
$keystream = $Lizard->getKeystream();
?>
<pre>
Key:                   0x<?php echo strtoupper(Lizard::binArray2hex($key)); ?><br>
Key (reference):       0x0123456789ABCDEF0123456789ABCD<br>
IV:                    0x<?php echo strtoupper(Lizard::binArray2hex($iv)); ?><br>
IV (reference):        0xABCDEF0123456789<br>
Keystream:             0x<?php echo strtoupper(Lizard::binArray2hex($keystream)); ?><br>
Keystream (reference): 0x983311A97831586548209DAFBF26FC93
</pre>
</main>
<footer class="my-5 pt-5 text-muted text-center text-small">
	<p class="mb-1">&copy; 2018 <a href="https://www.christiangorke.de">Christian A. Gorke</a></p>
</footer>
</body>
</html>
