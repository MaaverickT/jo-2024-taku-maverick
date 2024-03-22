<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_STRING);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_STRING);
   

    // Vérifiez si l'un des éléments de l'utilisateur est vide
    if (empty($nomAthlete) || empty($prenomAthlete)) { 
        $_SESSION['error'] = "L'un des champs ne peut pas être vide.";
        header("Location: add-athletes.php");
        exit();
    }

    try {
        // Vérifiez si l'utilisateur existe déjà
        $queryCheck = "SELECT id_athlete FROM ATHLETE WHERE nom_athlete = :nom_athlete";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nom_athlete", $nom_athlete, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'athlète'existe déjà.";
            header("Location: add-athletes.php");
            exit();
        } else {

            // Requête pour ajouter un utilisateur
            $query = "INSERT INTO ATHLETE (nom_athlete, prenom_athlete) VALUES (:nom_athlete, :prenom_athlete)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
            $statement->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
            $statement->bindParam(":nomGenre", $nomGenre, PDO::PARAM_STR);
    

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'Athlete' a été ajouté avec succès.";
                header("Location: manage-athletes.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'Athlete";
                header("Location: add-athletes.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-athletes.php");
        exit();
    }
}
// Afficher les erreurs en PHP
// (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
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
    <title>Ajouter un Athlete- Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="manage-sports.php">Gestion Sports</a></li>
                <li><a href="manage-users.php">Gestion Utilisateurs</a></li>
                <li><a href="manage-places.php">Gestion Lieux</a></li>
                <li><a href="manage-events.php">Gestion Calendrier</a></li>
                <li><a href="manage-countries.php">Gestion Pays</a></li>
                <li><a href="manage-gender.php">Gestion Genres</a></li>
                <li><a href="manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter un Athlete</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
         <!-- AJOUT D'UN Athlete AVEC LE FORMULAIRE -->
        <form action="add-athletes.php" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet Athlete?')">
            <label for=" nomAthlete">Nom de l'Athlete</label>
            <input type="text" name="nomAthlete" id="nomAthlete" required>

            <label for=" prenomAthlete">Prenom de l'Athlete</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete" required>

            <label for="resultat">Resultat</label>
            <input type="text" name="resultat" id="resultat" required>

            <label for="nomEpreuve">Epreuve</label>
            <input type="text" name="nomEpreuve" id="nomEpreuve" required>


            <input type="submit" value="Ajouter l'Athlete">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des athletes</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>
