<?php
namespace flip_baj\main;

include ('header.php');

?>
<script type="text/javascript" src="../public/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../public/js/datatables.min.js"></script>
<script type="text/javascript" src="../public/js/utils.js"></script>

<script type="text/javascript" src="../public/js/selectionacheteur.js"></script>
<script type="text/javascript" src="../public/js/modaleacheteur.js"></script>

<ul class="filariane ms-2">
	<li><a href="index.php">Home</a></li>
	<li><a href="#">Selection du vendeur</a></li>
</ul>

<main class="container">

	<h2 class="text-center">Choix du vendeur</h2>
	<!-- Essaye d'afficher les acheteurs ici  -->
    <div id="id-form-acheteur" class="" style="display: none;">
        <div class="row">
            <div class="">
                <h2 id="" class="table">Liste des acheteurs</h2>
            </div>
            <div class="col">
                <table id="acheteurSelection" class="table table-striped">
                    <thead>
                        <tr>
							<th>Nom</th>
							<th>Prénom</th>
							<th>Email</th>
							<th>Tel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Les données seront chargées ici via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<?php
include ('footer.php');
?>
