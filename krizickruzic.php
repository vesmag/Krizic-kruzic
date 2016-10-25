<?php

$errormsg = false;

class krizickruzic {
    public $polja = array("11"=>"", "12"=>"", "13"=>"",   "21"=>"", "22"=>"", "23"=>"", "31"=>"", "32"=>"", "33"=>"");
    public $red; //X ili O
    public $igracNaRedu; //igrac1 ili igrac2

    function __construct() {
        $this->red = "X";
        $this->igracNaRedu = "igrac1";
    }
    
    //funkcija obraduje imena dobivena u formi, pregledava jesu li u ispravnom formatu i ako jesu, stvara dva igraca s prikljucenim imenima te zove funkciju za ispis polja igre
	function obradiImena ($ime1, $ime2) {
		global $errormsg;
		if (!preg_match ("/^[A-Za-zŠĐČĆŽšđčćž]{1,20}$/", $ime1) || !preg_match ("/^[A-Za-zŠĐČĆŽšđčćž]{1,20}$/", $ime1)) {
			$errormsg = "Unos imena igrača nije uspio: imena moraju sadržavati samo slova, minimalno jedno, maksimalno njih 20. Pokušajte ponovo.";
			obradiGresku();
		}
		else {
			$_SESSION['igrac1'] = $ime1;
			$_SESSION['igrac1-score'] = 0;
			$_SESSION['igrac2'] = $ime2;
			$_SESSION['igrac2-score'] = 0;
			$this->ispisIgre();
		}
	}	
	
	//funkcija pregledava sve moguce pobjednicke kombinacije i ako ju nadje, sprema pobjednicka polja i vraca 1
	//ako funkcija ne nađe pobjednicku kombinaciju, gleda jesu li sva polja iskoristena - ako jesu, vraca "Nema pobjednika"; ako nisu, igra se nastavlja
	function isGameOver() {

		if ($this->polja["11"] !== "" && $this->polja["11"] === $this->polja["12"] && $this->polja["12"] === $this->polja["13"]) {
			$_SESSION['pobjednickaPolja'] = "11,12,13";
			return $this->polja["11"];
		}
		if ($this->polja["21"] !== "" && $this->polja["21"] === $this->polja["22"] && $this->polja["22"] === $this->polja["23"]) {
			$_SESSION['pobjednickaPolja'] = "21,22,23";
			return $this->polja["21"];
		}
		if ($this->polja["31"] !== "" && $this->polja["31"] === $this->polja["32"] && $this->polja["32"] === $this->polja["33"]) {
			$_SESSION['pobjednickaPolja'] = "31,32,33";
			return $this->polja["31"];
		}
		if ($this->polja["11"] !== "" && $this->polja["11"] === $this->polja["21"] && $this->polja["21"] === $this->polja["31"]) {
			$_SESSION['pobjednickaPolja'] = "11,21,31";
			return $this->polja["11"];
		}
		if ($this->polja["12"] !== "" && $this->polja["12"] === $this->polja["22"] && $this->polja["22"] === $this->polja["32"]) {
			$_SESSION['pobjednickaPolja'] = "12,22,32";
			return $this->polja["12"];
		}
		if ($this->polja["13"] !== "" && $this->polja["13"] === $this->polja["23"] && $this->polja["23"] === $this->polja["33"]) {
			$_SESSION['pobjednickaPolja'] = "13,23,33";
			return $this->polja["13"];
		}
		if ($this->polja["11"] !== "" && $this->polja["11"] === $this->polja["22"] && $this->polja["22"] === $this->polja["33"]) {
			$_SESSION['pobjednickaPolja'] = "11,22,33";
			return $this->polja["11"];
		}
		if ($this->polja["13"] !== "" && $this->polja["13"] === $this->polja["22"] && $this->polja["22"] === $this->polja["31"]) {
			$_SESSION['pobjednickaPolja'] = "13,22,31";
			return $this->polja["13"];
		}

		$br = 0;
		foreach ($this->polja as $polje) {
			if ($polje === "") break;
			$br++;
		}
		if ($br === 9) return "Nema pobjednika";
		
		return false;
	}
	
	//funkcija obraduje trenutni potez, gleda koje je polje odabrao igrac i tu celiju oznacava kao iskoristenu
	function obradiPotez ($red) {
		//oznaceno polje je oblika "red,stupac"
		$oznacenoPolje = explode(",", $_POST["celija"]);
		$celija = $oznacenoPolje[0].$oznacenoPolje[1];

		//postavljamo oznaceno polje oznakom X ili O te u sessionu oznacimo celiju kao iskoristenu
		$this->polja[$celija] = $red;
		$_SESSION['c'.$celija] = true;

		//provjeravamo jesmo li dosli do kraja igre
		$kraj = $this->isGameOver();
		if ($kraj && $kraj !== "Nema pobjednika") {
			$_SESSION['gameOver'] = true;
			if ($this->igracNaRedu === "igrac1") $_SESSION['igrac1-score']++;
			if ($this->igracNaRedu === "igrac2") $_SESSION['igrac2-score']++;
			$this->ispisIgre();
		}

		else if ($kraj === "Nema pobjednika") {
			$_SESSION['gameOver'] = "Nema pobjednika";
			$this->ispisIgre();
		}

		//ako nismo: prebacivanje reda na drugog igraca i ispis igre
		else {
			if ($red === "X") {
				$this->red = "O";
				$this->igracNaRedu = "igrac2";
			}
			if ($red === "O") {
				$this->red = "X";
				$this->igracNaRedu = "igrac1";
			}
			$this->ispisIgre();
		}
	}
	
	//funkcija koja ispisuje tablicu 3x3 u kojoj se nalaze polja igre
	//svaka celija je jedna forma koja u sebi nosi hidden input cije je ime "celija", a value "red,stupac" te odredjene celije
	//u svakom ispisu se kao ime buttona ispisuje vrijednost koja je trenutno u $this->$polje(ij) - X ili O
	function ispisIgre() {
	?>
		<html>
			<head>
				<meta charset="utf-8" />
				<title>Križić kružić</title>
				<link rel="stylesheet" type="text/css" href="style.css">
				<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Nunito"/ />
			</head>
			<body>
				<?php
				echo "<h1>Igra križić-kružić</h1>";
				?>
				<div class="score">
					<h3>Trenutni rezultat</h3>
					<table class="score">
						<tr>
							<th><?php echo $_SESSION['igrac1']; ?></th>
							<th><?php echo $_SESSION['igrac2']; ?></th>
						</tr>
						<tr>
							<td><?php echo $_SESSION['igrac1-score']; ?></td>
							<td><?php echo $_SESSION['igrac2-score']; ?></td>
						</tr>
					</table>
				</div>
				<table class="igra">
					<?php
					for ($i=1; $i<=3; ++$i) {
						echo "<tr>";
						for ($j=1; $j<=3; ++$j) {
					?>
						<td>
							<form method="POST" action="<?php $_SERVER["PHP_SELF"]?>">
								<input type="hidden" name="celija" value="<?php echo $i.",".$j;?>">
								<button type="submit" class="igra"
									<?php
									//ako je polje jedno od pobjednickih, ispisuje se sa zelenom pozadinom
									if (isset($_SESSION['pobjednickaPolja'])) {
										if (strpos($_SESSION['pobjednickaPolja'],$i.$j) !== false)
											echo "style=\"background-color:#009933\"";
									}
									//ako je polje vec aktivno, ispisujemo ga kao disabled button
									if (isset($_SESSION['c'.$i.$j]) || isset($_SESSION['gameOver'])) echo "disabled"; ?>>
									<?php echo $this->polja["$i$j"];?>
								</button>
							</form>
						</td>
					<?php
						}
					}
					?>
				</table>
				<?php
				if (!isset($_SESSION['gameOver']))
					echo "<h3>Na redu je ".$_SESSION[$this->igracNaRedu],"</h3>";
				
				//ako je igra gotova ispisujemo malu formicu ispod tablice koja nas salje na ponovni unos imena ili restarta igru s istim igracima, ovisno o izboru
				if (isset($_SESSION['gameOver'])) {
					if ($_SESSION['gameOver']==="Nema pobjednika")
						echo "<div class=\"zavrsni-tekst\">Ovu igru ste izjednačeni. Hoćemo još jednu?</div>";
					else
						echo "<div class=\"zavrsni-tekst\">Bravo ".$_SESSION[$this->igracNaRedu].", ti si pobjednik ove partije!</div>";
				?>
				<div class="zavrsni-gumbi">
					<form method="POST" action="<?php $_SERVER['PHP_SELF']?>">
						<input type="hidden" name="ponoviIgru" />
						<button class="zavrsni1" type="submit">Ponovi igru</button>
					</form>
					<form method="POST" action="<?php $_SERVER['PHP_SELF']?>">
						<input type="hidden" name="promijeniIgrace" />
						<button class="zavrsni2" type="submit">Promijeni igrače</button>
					</form>
				</div>
				<?php
					}
				?>
			</body>
		</html>

	<?php
	}


}

//funkcija ispisuje welcome screen, ispis forme u koju se unose imena igraca
function ispisFormeZaImena () {
    global $errormsg;
    ?>
    <html>
        <head>
            <meta charset="utf-8" />
            <title>Križić kružić</title>
			<link rel="stylesheet" type="text/css" href="style.css">
            <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Nunito"/ />
        </head>
        <body>
            <h1>Igra križić-kružić</h1>
            <form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
                <div class="unos-igraca">
                    Ime prvog igrača (X):</br><input type="text" name="igrac1" /></br></br>
                    Ime drugog igrača (O):</br><input type="text" name="igrac2" /></br></br>
                    <input type="hidden" name="unosImena" />
                    <button class="kreni" type="submit">Kreni!</button>
                </div>
        </body>
    </html>
<?php
    }

//ako je doslo do greske pri unosu imena, poziva se ova funkcija koja ispisuje errormsg kao alert
function obradiGresku () {
    global $errormsg;
    ?>
    <html>
        <head>
            <meta charset="utf-8" />
            <title>Križić kružić</title>
            <link rel="stylesheet" type="text/css" href="style.css">
        </head>
        <body>
            <?php echo "<script type='text/javascript'>alert('".$errormsg."')</script>"; ?>
        </body>
    </html>
    <?php
    ispisFormeZaImena();
}



session_start();

//ako je igrac odlucio ponoviti igru s istim igracima, drzimo samo imena igraca i njihov score u sessionu, ostalo brisemo i ispisujemo novu igru
if (isset($_POST["ponoviIgru"])) {
    $igrac1 = $_SESSION['igrac1'];
    $igrac2 = $_SESSION['igrac2'];
    $igrac1score = $_SESSION['igrac1-score'];
    $igrac2score = $_SESSION['igrac2-score'];

    session_unset();
    session_destroy();

    session_start();
    $_SESSION['igrac1'] = $igrac1;
    $_SESSION['igrac2'] = $igrac2;
    $_SESSION['igrac1-score'] = $igrac1score;
    $_SESSION['igrac2-score'] = $igrac2score;

    $igra = new krizickruzic();
    $_SESSION["igra"] = $igra;
    $igra->ispisIgre();
}

//ako je igrac odlucio promijeniti igrace, unistavamo cijeli session te ih saljemo na pocetni ekran za unos imena
if (isset($_POST['promijeniIgrace'])) {
    session_unset();
    session_destroy();

    session_start();
}

if (!isset($_SESSION["igra"])) {
    $igra = new krizickruzic();
    $_SESSION["igra"] = $igra;
}
else
    $igra = $_SESSION["igra"];

//ako nije postavljena zastavica forme za unos imena niti su imena postavljena, nismo još ispisali formu za imena
if (!isset($_POST["unosImena"]) && (!isset($_SESSION["igrac1"]) || !isset($_SESSION["igrac2"])))
    ispisFormeZaImena();
//ako je zastavica unosImena dignuta, trenutno obrađujemo formu za ispis imena
else if (isset($_POST["unosImena"]))
    $igra->obradiImena($_POST["igrac1"], $_POST["igrac2"]);

//postavljena zastavica unutar nekog od polja igre, treba pronaći kojeg i obraditi ga
if (isset($_POST["celija"])) {
    $igra->obradiPotez($igra->red);
}


?>
