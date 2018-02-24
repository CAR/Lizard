<?php
/*
* Lizard in PHP
*
* Lizard by Matthias Hamann, Matthias Krause, Willi Meier
* Lizard in PHP by Christian A. Gorke <christiangorke.de>
*/

class Lizard {

	private $K = array();
	private $IV = array();
	private $S = array();
	private $B = array();
	private $t = 0;
	private $z = array();
	private $L = array();
	private $Q = array();
	private $T = array();
	private $Ttilde = array();
	private $a257 = false;
	private $keystream = array();


	public function __construct($key, $iv, $length = 0) {
		$this->z[$this->t] = 0;
		$this->L[$this->t] = 0;
		$this->Q[$this->t] = 0;
		$this->T[$this->t] = 0;
		$this->Ttilde[$this->t] = 0;

		$this->initialization($key, $iv);
		if ($length > 0) $this->keystreamGeneration($length);
	}

	private function initialization($key, $iv) {
		//Phase 1
		$this->loadKey($key);
		$this->loadIV($iv);
		$this->initRegisters();

		//Phase 2
		for ($this->t = 0; $this->t <= 127; $this->t++) $this->mixing();

		//Phase 3
		$this->keyadd();

		//Phase4
		for($this->t = 129; $this->t <= 256; $this->t++) $this->diffusion();
	}

	private function loadKey($key) {
		for ($i = 0; $i <= 119; $i++) $this->K[$i] = $key[$i];
	}

	private function loadIV($iv) {
		for ($i = 0; $i <= 63; $i++) $this->IV[$i] = $iv[$i];
	}

	private function initRegisters() {
		for ($i = 0; $i <= 63; $i++) $this->B[0][$i] = $this->K[$i] ^ $this->IV[$i];
		for ($i = 64; $i <= 89; $i++) $this->B[0][$i] = $this->K[$i];
		for ($i = 0; $i <= 28; $i++) $this->S[0][$i] = $this->K[$i+90];
		$this->S[0][29] = $this->K[119] ^ 1;
		$this->S[0][30] = 1;
	}

	private function mixing() {
		$this->z[$this->t] = $this->a();

		for ($i = 0; $i <= 88; $i++) $this->B[$this->t+1][$i] = $this->B[$this->t][$i+1];
		$this->B[$this->t+1][89] = $this->z[$this->t] ^ $this->NFSR2();

		for ($i = 0; $i <= 29; $i++) $this->S[$this->t+1][$i] = $this->S[$this->t][$i+1];
		$this->S[$this->t+1][30] = $this->z[$this->t] ^ $this->NFSR1();
	}

	private function a() {
		$this->L[$this->t] = $this->B[$this->t][7] ^ $this->B[$this->t][11] ^ $this->B[$this->t][30] ^ $this->B[$this->t][40]
								^ $this->B[$this->t][45] ^ $this->B[$this->t][54] ^ $this->B[$this->t][71];
		$this->Q[$this->t] = $this->B[$this->t][4]*$this->B[$this->t][21] ^ $this->B[$this->t][9]*$this->B[$this->t][52]
								^ $this->B[$this->t][18]*$this->B[$this->t][37] ^ $this->B[$this->t][44]*$this->B[$this->t][76];
		$this->T[$this->t] = $this->B[$this->t][5] ^ $this->B[$this->t][8]*$this->B[$this->t][82] ^ $this->B[$this->t][34]*$this->B[$this->t][67]*$this->B[$this->t][73]
								^ $this->B[$this->t][2]*$this->B[$this->t][28]*$this->B[$this->t][41]*$this->B[$this->t][65]
								^ $this->B[$this->t][13]*$this->B[$this->t][29]*$this->B[$this->t][50]*$this->B[$this->t][64]*$this->B[$this->t][75]
								^ $this->B[$this->t][6]*$this->B[$this->t][14]*$this->B[$this->t][26]*$this->B[$this->t][32]*$this->B[$this->t][47]*$this->B[$this->t][61]
								^ $this->B[$this->t][1]*$this->B[$this->t][19]*$this->B[$this->t][27]*$this->B[$this->t][43]*$this->B[$this->t][57]*$this->B[$this->t][66]*$this->B[$this->t][78];
		$this->Ttilde[$this->t] = $this->S[$this->t][23] ^ $this->S[$this->t][3]*$this->S[$this->t][16] ^ $this->S[$this->t][9]*$this->S[$this->t][13]*$this->B[$this->t][48]
								^ $this->S[$this->t][1]*$this->S[$this->t][24]*$this->B[$this->t][38]*$this->B[$this->t][63];

		return $this->L[$this->t] ^ $this->Q[$this->t] ^ $this->T[$this->t] ^ $this->Ttilde[$this->t];
	}

	private function NFSR2() {
		return $this->S[$this->t][0] ^ $this->B[$this->t][0] ^ $this->B[$this->t][24] ^ $this->B[$this->t][49] ^ $this->B[$this->t][79]
				^ $this->B[$this->t][84] ^ $this->B[$this->t][3]*$this->B[$this->t][59] ^ $this->B[$this->t][10]*$this->B[$this->t][12]
				^ $this->B[$this->t][15]*$this->B[$this->t][16] ^ $this->B[$this->t][25]*$this->B[$this->t][53] ^ $this->B[$this->t][35]*$this->B[$this->t][42]
				^ $this->B[$this->t][55]*$this->B[$this->t][58] ^ $this->B[$this->t][60]*$this->B[$this->t][74]	^ $this->B[$this->t][20]*$this->B[$this->t][22]*$this->B[$this->t][23]
				^ $this->B[$this->t][62]*$this->B[$this->t][68]*$this->B[$this->t][72] ^ $this->B[$this->t][77]*$this->B[$this->t][80]*$this->B[$this->t][81]*$this->B[$this->t][83];	
	}

	private function NFSR1() {
		return $this->S[$this->t][0] ^ $this->S[$this->t][2] ^ $this->S[$this->t][5] ^ $this->S[$this->t][6] ^ $this->S[$this->t][15]
				^ $this->S[$this->t][17] ^ $this->S[$this->t][18] ^ $this->S[$this->t][20] ^ $this->S[$this->t][25] ^ $this->S[$this->t][8]*$this->S[$this->t][18]
				^ $this->S[$this->t][8]*$this->S[$this->t][20] ^ $this->S[$this->t][12]*$this->S[$this->t][21] ^ $this->S[$this->t][14]*$this->S[$this->t][19]
				^ $this->S[$this->t][17]*$this->S[$this->t][21] ^ $this->S[$this->t][20]*$this->S[$this->t][22]	^ $this->S[$this->t][4]*$this->S[$this->t][12]*$this->S[$this->t][22]
				^ $this->S[$this->t][4]*$this->S[$this->t][19]*$this->S[$this->t][22] ^ $this->S[$this->t][7]*$this->S[$this->t][20]*$this->S[$this->t][21]
				^ $this->S[$this->t][8]*$this->S[$this->t][18]*$this->S[$this->t][22] ^ $this->S[$this->t][8]*$this->S[$this->t][20]*$this->S[$this->t][22]
				^ $this->S[$this->t][12]*$this->S[$this->t][19]*$this->S[$this->t][22] ^ $this->S[$this->t][20]*$this->S[$this->t][21]*$this->S[$this->t][22]
				^ $this->S[$this->t][4]*$this->S[$this->t][7]*$this->S[$this->t][12]*$this->S[$this->t][21]	^ $this->S[$this->t][4]*$this->S[$this->t][7]*$this->S[$this->t][19]*$this->S[$this->t][21]
				^ $this->S[$this->t][4]*$this->S[$this->t][12]*$this->S[$this->t][21]*$this->S[$this->t][22] ^ $this->S[$this->t][4]*$this->S[$this->t][19]*$this->S[$this->t][21]*$this->S[$this->t][22]
				^ $this->S[$this->t][7]*$this->S[$this->t][8]*$this->S[$this->t][18]*$this->S[$this->t][21] ^ $this->S[$this->t][7]*$this->S[$this->t][8]*$this->S[$this->t][20]*$this->S[$this->t][21]
				^ $this->S[$this->t][7]*$this->S[$this->t][12]*$this->S[$this->t][19]*$this->S[$this->t][21] ^ $this->S[$this->t][8]*$this->S[$this->t][18]*$this->S[$this->t][21]*$this->S[$this->t][22]
				^ $this->S[$this->t][8]*$this->S[$this->t][20]*$this->S[$this->t][21]*$this->S[$this->t][22] ^ $this->S[$this->t][12]*$this->S[$this->t][19]*$this->S[$this->t][21]*$this->S[$this->t][22];
	}

	private function keyadd() {
		for ($i = 0; $i <= 89; $i++) $this->B[129][$i] = $this->B[128][$i] ^ $this->K[$i];
		for ($i = 0; $i <= 29; $i++) $this->S[129][$i] = $this->S[128][$i] ^ $this->K[$i+90];
		$this->S[129][30] = 1;
	}

	private function diffusion() {
		for ($i = 0; $i <= 88; $i++) $this->B[$this->t+1][$i] = $this->B[$this->t][$i+1];
		$this->B[$this->t+1][89] = $this->NFSR2();

		for ($i = 0; $i <= 29; $i++) $this->S[$this->t+1][$i] = $this->S[$this->t][$i+1];
		$this->S[$this->t+1][30] = $this->NFSR1();
	}

	public function keystreamGeneration($length = 1) {
		$this->keystream = array();

		for ($i = 1; $i <= $length; $i++) {
			$this->keystream[] = $this->a();
			$this->diffusion();
			$this->t++;
		}
	}

	// Specification Version
	public function keystreamGenerationSpecification($length = 1) {
		if ($length <= 0) return;

		$length2 = $length - 1;
		if (!$this->a257) {
			$this->z[$this->t] = $this->a(); #z_257
			$this->a257 = true;
			$length2--;
		}

		for ($i = 0; $i <= $length2; $i++) {
			$this->diffusion();
			$this->t++;
			$this->z[$this->t] = $this->a();
		}

		return array_slice($this->z, -$length);
	}

	public function getKeystream() {
		return $this->keystream;
	}

	public static function binArray2hex($bin) {
		$r = 4 - count($bin) % 4;
		for ($i = 1; $i <= $r % 4; $i++) array_unshift($bin, 0);

		$hex = "";
		for ($i = 0; $i < count($bin); $i=$i+4)	$hex .= dechex(bindec($bin[$i].$bin[$i+1].$bin[$i+2].$bin[$i+3]));

		return $hex;
	}

}
?>
