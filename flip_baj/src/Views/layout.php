<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Bourse aux jeux</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="shortcut icon" href="img/Logo_FdJ_tete.ico.gif" />

    <!-- CSS (chemins adaptés pour pointer depuis public/index.php) -->
    <link rel="stylesheet" href="css/css-mint.css">
    <link rel="stylesheet" href="css/datatables.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/bootstrap-icons.css" />
    <link rel="stylesheet" href="css/custom.css">

    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <header class="mb-1 bg-bleu text-white d-flex justify-content-between align-items-center">
        <div class="col-8">
            <div class="">
                <img src="img/bulle-flip-2.png" width="100px" alt="Bulle Flip" /> 
                <a href="?page=home" class="text-decoration-none text-white"> Bourse aux jeux </a>
            </div>
        </div>
        <div class="col-4 text-end">
            <div class="font-light text-small me-2">&copy; Woopy On Off – Flip Parthenay</div>
        </div>
    </header>

    <!-- Contenu dynamique de la page -->
    <main>
        <?= $content ?? '' ?>
    </main>

</body>
</html>
