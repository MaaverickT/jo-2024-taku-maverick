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
    <title>Liste des Athletes - Jeux Olympiques 2024</title>
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
                <li><a href="../../admin/admin-gender/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../../admin/admin-gender/manage-users.php">Gestion Utilisateur</a></li>
                <li><a href="../../admin/admin-gender/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../../admin/admin-gender/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../../admin/admin-gender/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../../admin/admin-gender/manage-gender.php">Gestion Genres</a></li>
                <li><a href="../../admin/admin-gender/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../../admin/admin-gender/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Liste des Athletes</h1>
        <div class="action-buttons">
            <button onclick="openAddAthleteForm()">Ajouter un Athlete</button>
            <!-- Autres boutons... -->
        </div>
        <!-- Tableau des Athletes -->
        <?php
        require_once("../../../database/database.php");

        try {
            // Requête pour récupérer la liste des utilisateur depuis la base de données

            $query = "SELECT id_athlete, nom_athlete, prenom_athlete, nom_pays, nom_genre
            FROM ATHLETE 
            INNER JOIN PAYS ON ATHLETE.id_pays = PAYS.id_pays
            INNER JOIN GENRE ON ATHLETE.id_genre = GENRE.id_genre ORDER BY nom_athlete";
           
       
            
            $statement = $connexion->prepare($query);
            $statement->execute();

            

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                // les Titre du tableau
                echo "<table><tr><th>Prenom</th><th>Pays</th><th>Genre</th><th>Modifier</th><th>Supprimer</th></tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    // Assainir les données avant de les afficher
                    echo "<td>" . htmlspecialchars($row['prenom_athlete'] .' ' . $row['nom_athlete']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_pays']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_genre']) . "</td>";
                    echo "<td><button onclick='openModifyAthleteForm({$row['id_athlete']})'>Modifier</button></td>";
                    echo "<td><button onclick='deleteAthleteConfirmation({$row['id_athlete']})'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun user trouvé.</p>";
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
        function openAddUAthleteForm() {
            // Ouvrir une fenêtre pop-up avec le formulaire de modification
            // L'URL contien un paramètre "id"
            window.location.href = 'add-athletes.php';
        }

        function openModifyUAthleteForm(id_athlete) {
            // Ajoutez ici le code pour afficher un formulaire stylisé pour modifier un sport
            // alert(id_sport);
            window.location.href = 'modify-athletes.php?id_athlete=' + id_athlete;
        }

        function deleteAthleteConfirmation(id_athlete) {
            // Ajoutez ici le code pour afficher une fenêtre de confirmation pour supprimer un sport
            if (confirm("Êtes-vous sûr de vouloir supprimer cet athlete?")) {
                // Ajoutez ici le code pour la suppression du sport
                // alert(id_sport);
                window.location.href = 'delete-athletes.php?id_athlete=' + id_athlete;
            }
        }
    </script>
</body>

</html>