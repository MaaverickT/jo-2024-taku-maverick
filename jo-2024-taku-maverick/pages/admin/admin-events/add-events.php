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
    $categories = filter_input(INPUT_POST, 'categories', FILTER_SANITIZE_SPECIAL_CHARS);
    $nomEvent = filter_input(INPUT_POST, 'nomEvent', FILTER_SANITIZE_SPECIAL_CHARS);
    $dateEvent = filter_input(INPUT_POST, 'dateEvent', FILTER_SANITIZE_SPECIAL_CHARS);
    $heureEvent = filter_input(INPUT_POST, 'heureEvent', FILTER_SANITIZE_SPECIAL_CHARS);
    $lieux = filter_input(INPUT_POST, 'lieux', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si les champs requis sont vides
    if (empty($categories) || empty($nomEvent) || empty($dateEvent) || empty($heureEvent) || empty($lieux)) {
        $_SESSION['error'] = "Un champ ne peut pas être vide.";
        header("Location: add-event.php");
        exit();
    }

    try {
        // Vérifiez si le calendrier de l'epreuve existe déjà
        $queryCheck = "SELECT nom_epreuve FROM EPREUVE WHERE nom_epreuve = :nomEvent";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomEvent", $nomEvent, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'épreuve existe déjà.";
            header("Location: add-event.php");
            exit();
        } else {
            // Requête pour ajouter une epreuve dans le calendrier
            $query = "INSERT INTO EPREUVE (nom_epreuve, date_epreuve, heure_epreuve, id_lieu, id_sport) VALUES (:nomEvent, :dateEvent, :heureEvent, :lieux, :categories)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":nomEvent", $nomEvent, PDO::PARAM_STR);
            $statement->bindParam(":dateEvent", $dateEvent, PDO::PARAM_STR);
            $statement->bindParam(":heureEvent", $heureEvent, PDO::PARAM_STR);
            $statement->bindParam(":lieux", $lieux, PDO::PARAM_STR);
            $statement->bindParam(":categories", $categories, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'épreuve a été ajouté avec succès.";
                header("Location: manage-events.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'épreuve.";
                header("Location: add-event.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-event.php");
        exit();
    }
}
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
    <title>Ajouter une epreuve au calendrier - Jeux Olympiques 2024</title>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="./manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-genders.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter une epreuve au calendrier</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-event.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cette épreuve?')">
            <label for="categorieEvent">Choississez une catégorie :</label>
            <select name="categories" id="categorieEvent">
                <?php
                try {
                    // Recuperer tout le contenue de la table SPORT
                    $query = "SELECT * FROM SPORT";
                    $stmt = $connexion->prepare($query);
                    $stmt->execute();

                    // Affiche toute les sports dans un option 
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . $row['id_sport'] . "'>" . $row['nom_sport'] . "</option>";
                    }
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                ?>
            </select>

            <label for="nomEvent">Nom de l'épreuve :</label>
            <input type="text" name="nomEvent" id="nomEvent" required>

            <label for="dateEvent">Date de l'evenement :</label>
            <input type="date" name="dateEvent" id="dateEvent" required>

            <label for="heureEvent">Heure de l'evenement :</label>
            <input type="time" name="heureEvent" id="heureEvent" required>

            <label for="lieuEvent">Choississez un lieu :</label>
            <select name="lieux" id="lieuEvent">
                <?php
                try {
                    // Recuperer tout le contenue de la table LIEU
                    $query = "SELECT * FROM LIEU";
                    $stmt = $connexion->prepare($query);
                    $stmt->execute();

                    // Affiche toute les lieux dans un option 
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . $row['id_lieu'] . "'>" . $row['nom_lieu'] . "</option>";
                    }
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                ?>
            </select>

            <input type="submit" value="Ajouter l'épreuve">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion des evenements</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>




<?php
/* session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomEpreuve = filter_input(INPUT_POST, 'nomEpreuve', FILTER_SANITIZE_STRING);
    $dateEpreuve = filter_input(INPUT_POST, 'dateEpreuve', FILTER_SANITIZE_STRING);
    $heureEpreuve = filter_input(INPUT_POST, 'heureEpreuve', FILTER_SANITIZE_STRING);
    



    // Vérifiez si le nom du pays est vide
    if (empty($nomEpreuve) || empty($dateEpreuve) || empty($heureEpreuve)) {
        $_SESSION['error'] = "L'un des champs ne peut pas être vide.";
        header("Location: add-events.php");
        exit();
    }


    try {
        // Vérifiez si l'heure de l'épreuve existe déjà
        $queryCheck = "SELECT id_epreuve FROM EPREUVE WHERE nom_epreuve = :nomEpreuve";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomEpreuve", $dateEpreuve, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'épreuve'existe déjà.";
            header("Location: add-events.php");
            exit();
        } else {

            // Requête pour ajouter un utilisateur

            $queryAdresse = "INSERT INTO LIEU (adresse_lieu) VALUES (:adresseLieu)";
            $statementAdresse = $connexion->prepare($queryAdresse);
            $statementAdresse->bindParam(":adresseLieu", $adresseLieu, PDO::PARAM_STR);
            $statementAdresse->execute();

            // Récupérer l'ID généré pour l'adresse du lieu
            $idAdresseLieu= $connexion->lastInsertId();



            $query = "INSERT INTO EPREUVE (nom_epreuve, date_epreuve, heure_epreuve, id_adresse_lieu)) VALUES (:nomEpreuve, :dateEpreuve, :heureEpreuve, ::idAdresseLieu)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":dateEpreuve", $dateEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":heureEpreuve", $heureEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":idAdresseLieu", $idAdresseLieu, PDO::PARAM_STR);


            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'épreuve a été ajouté avec succès.";
                header("Location: manage-events.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'évent.";
                header("Location: add-events.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-events.php");
        exit();
    }
}
// Afficher les erreurs en PHP
// (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1); */
?>
<!-- <!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Ajouter un Evenement - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
        <nav>
             Menu vers les pages sports, events, et results -->
         <!--    <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="manage-sports.php">Gestion Sports</a></li>
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
        <h1>Ajouter un Evenement</h1> -->
        <?php
       /*  if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        } -->
        ?>
        <form action="add-events.php" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet événement?')">
            <label for=" nomEpreuve">Nom de l'évent :</label>
            <input type="text" name="nomEpreuve" id="nomEpreuve" required>

            <label for=" dateEpreuve">Date de l'évent :</label>
            <input type="text" name="dateEpreuve" id="dateEpreuve" required>

            <label for=" heureEpreuve">Heure de l'évent :</label>
            <input type="text" name="heureEpreuve" id="heureEpreuve" required>

            <label for="adresseLieu">Lieu :</label>
            <input type="text" name="adresseLieu" id="adresseLieu" required>

            <input type="submit" value="Ajouter le L'event">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion des events</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024"> */
    //    </figure>
    //</footer>

//</body>

//</html>


