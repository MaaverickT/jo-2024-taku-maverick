<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

$login = $_SESSION['login'];
$nom_utilisateur = $_SESSION['prenom_utilisateur'];
$prenom_utilisateur = $_SESSION['nom_utilisateur'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Liste des Lieux d'épreuves -  Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .action-buttons button {
            background-color: #1b1b1b;
            color: #d7c378;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .action-buttons button:hover {
            background-color: #d7c378;
            color: #1b1b1b;
        }
    </style>


</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
            <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="manage-sports.php">Gestion Sports</a></li>
                <li><a href="manage-places.php">Gestion Lieux</a></li>
                <li><a href="manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../../admin/admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="manage-gender.php">Gestion Genres</a></li>
                <li><a href="manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Liste des Lieux</h1>
        <div class="action-buttons">
            <button onclick="openAddLieuForm()">Ajouter un Lieu</button>
            <!-- Autres boutons... -->
        </div>
        <!-- Tableau des ls -->
        <?php
        require_once("../../../database/database.php");

        try {
            // Requête pour récupérer la liste des lieux depuis la base de données
            $query = "SELECT * FROM LIEU ORDER BY nom_lieu";
            $statement = $connexion->prepare($query);
            $statement->execute();
            

            // Vérifier s'il y a des résultats
            //TITRE DES TABLEAUX
            if ($statement->rowCount() > 0) {
                echo "<table>
                <tr>
                <th>Nom Lieu</th>
                <th>Adresse Lieu</th>
                <th>Code Postal</th>
                <th>Ville</th>
                <th>Modifier</th>
                <th>Supprimer</th></tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    // Assainir les données avant de les afficher
                    echo "<td>" . htmlspecialchars($row['nom_lieu']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['adresse_lieu']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['cp_lieu']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ville_lieu']) . "</td>";
                    echo "<td><button onclick='openModifyLieuForm({$row['id_lieu']})'>Modifier</button></td>";
                    echo "<td><button onclick='deleteLieuConfirmation({$row['id_lieu']})'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun Lieu trouvé.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        // Afficher les erreurs en PHP
// (fonctionne à condition d’avoir activé l’option en local)
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        ?>
        <p class="paragraph-link">
            <a class="link-home" href="../admin.php">Accueil administration</a>
        </p>

    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
    <script>
        function openAddLieuForm() {
            // Ouvrir une fenêtre pop-up avec le formulaire de modification
            // L'URL contien un paramètre "id"
            window.location.href = 'add-places.php';
        }

        function openModifyLieuForm(id_lieu) {
            // Ajoutez ici le code pour afficher un formulaire stylisé pour modifier un sport
            //alert(id_lieu);
            window.location.href = 'modify-places.php?id_lieu=' + id_lieu;
        }
       
        function deleteLieuConfirmation(id_lieu) {
            // Ajoutez ici le code pour afficher une fenêtre de confirmation pour supprimer un sport
            if (confirm("Êtes-vous sûr de vouloir supprimer ce Lieu?")) {
                // Ajoutez ici le code pour la suppression du sport
                // alert(id_lieu);
                window.location.href = 'delete-places.php?id_lieu=' + id_lieu;
            }
        }
    </script>
</body>

</html>
