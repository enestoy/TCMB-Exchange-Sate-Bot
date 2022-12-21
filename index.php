<?php
	try{
		$VeritabaniBaglantisi 	= new PDO("mysql:host=localhost;dbname=tcmb_doviz;charset=UTF8", "root", "");
	}catch(PDOException $Hata){
		die("Bağlantı Hatası: " . $Hata->getMessage());
	}
?>


<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>TCMB Döviz Kurları Bot</title>
</head>

<body>
    <?php
    $Link   = "https://www.tcmb.gov.tr/kurlar/today.xml";
    $Icerik = simplexml_load_file($Link);
    ?>
    <div class="container">
        <table class="table table-hover table-dark table-striped mt-5">

            <thead>
                <tr>

                    <th scope="col">Adı</th>
                    <th scope="col">Kısa Adı</th>
                    <th scope="col">Birimi</th>
                    <th scope="col">Alış</th>
                    <th scope="col">Satış</th>
                    <th scope="col">Efektif Alış</th>
                    <th scope="col">Efektif Satış</th>
                </tr>
            </thead>
            <?php
		$Liste 		= $Icerik->Currency;
		foreach($Liste as $Satir){
			$Adi 			= $Satir->Isim;
			$KisaAdi 		= $Satir["CurrencyCode"];
			$Birim 			= $Satir->Unit;
			$Alis 			= $Satir->ForexBuying;
			$Satis 			= $Satir->ForexSelling;
			$EfektifAlis 	= $Satir->BanknoteBuying;
			$EfektifSatis 	= $Satir->BanknoteSelling;
			$Zaman 			= time();	
			$Kontrol 		= $VeritabaniBaglantisi->prepare("SELECT * FROM dovizkurlari WHERE id=?");
			$Kontrol->execute(["1"]);
			$KontrolSayisi 	= $Kontrol->rowCount();
			if($KontrolSayisi > 0){
				$KontrolVerisi 	= $Kontrol->fetch(PDO::FETCH_ASSOC);
				if(($KontrolVerisi["zaman"]+86300) < $Zaman){
					// Güncelleşme süresi 1 günden önce olduysa yeniden güncelliyoruz.
					$Sorgu 	= $VeritabaniBaglantisi->prepare("UPDATE dovizkurlari SET alis=?, satis=?, efektifalis=?, efektifsatis=?, zaman=? WHERE kodu=?");
						$Sorgu->execute([$Alis, $Satis, $EfektifAlis, $EfektifSatis, $Zaman, $KisaAdi]);
						$SorguSayisi 	= $Sorgu->rowCount();
						if($SorguSayisi < 1){
							echo "Bilinmeyen bir hatayla karşılaştık.";
							die();
					}
				}
			}
		?>
            <tbody>
            <tr>
				<td scope="row"><?php echo $Adi ?></td>
				<td ><?php echo $KisaAdi ?></td>
				<td ><?php echo $Birim ?></td>
				<td ><?php echo $Alis ?></td>
				<td ><?php echo $Satis ?></td>
				<td ><?php echo $EfektifAlis ?></td>
				<td><?php echo $EfektifSatis ?></td>
			</tr>
            </tbody>
            <?php
		}
		$VeritabaniBaglantisi 	= null;
		?>
        </table>
    </div>


    <?php

    ?>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>