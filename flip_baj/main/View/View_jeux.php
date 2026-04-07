<?php
namespace flip_baj\view\View_jeux;
include ('../constantes.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bourse aux jeux</title>

    <!--  Javascript files -->
    <link rel="stylesheet" href="view_jeu.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css" />

</head>
<body>
    <header
		class="mb-1 bg-bleu text-white d-flex justify-content-between align-items-center">
		<div class="col-8">
			<div class="">
				<img src="../img/bulle-flip-2.png" width="90px" />
                <a href="" class="text-decoration-none text-white">Bourse aux jeux</a>
			</div>
		</div>
	</header>

    <main class="container">

        <div>
            <h2 class="text-center h2_view_jeux"> Liste des jeux disponible</h2>
        </div>
        <div class="row">
            <div class="col">
                <table id="vendeurs" class="table">
                        <thead>
                        <tr>
                            <th class= "View_jeux_nom">Nom</th>
                            <th class = "View_jeux_qtt">Quantité</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
    <footer>

    </footer>
</body>
</html>