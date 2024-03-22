<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'utilisateur est fourni dans l'URL sport
if (!isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "ID de l'athlete manquant.";
    header("Location: manage-users.php");
    exit();
}


$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'athlete est un entier valide
if (!$id_athlete && $id_athlete !== 0) {
    $_SESSION['error'] = "ID de l'athlete invalide.";
    header("Location: manage-athletes.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_STRING);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_STRING);
    $nomGenre = filter_input(INPUT_POST, 'nomGenre', FILTER_SANITIZE_STRING);
    $nomPays = filter_input(INPUT_POST, 'nomPays', FILTER_SANITIZE_STRING);


    // Vérifiez si le nom de l'utilisateur est vide
    if (empty($nomAthlete) || empty($prenomAthlete) || empty($nomGenre)  || empty($nomPays)) { 
        $_SESSION['error'] = "L'un des champs ne peut pas être vide.";
        header("Location: add-athletes.php");
        exit();
    }

    try {
        // Vérifiez si l'utilisateur existe déjà
        $queryCheck = "SELECT id_athlete' FROM ATHLETE WHERE nom_athlete, prenom_athlete = :nomAthlete, :prenomAthlete AND id_athlete <> :idAthlete";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statementCheck->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statementCheck->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'Athlete existe déjà.";
            header("Location: modify-athletes.php?id_athlete=$id_athlete");
            exit();
        }

        // Requête pour mettre à jour l'athlète
        $query = "UPDATE ATHLETE SET nom_athlete prenom_athlete AND GENRE SET nom_genre = :nomAthlete, :prenomAthlete WHERE id_athlete AND :nomGenre WHERE nom_genre = :idAthlete";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
        $statement->bindParam(":idGenre", $id_genre, PDO::PARAM_INT);


        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'Athlete a été modifié avec succès.";
            header("Location: manage-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de.";
            header("Location: modify-athletes.php?id_athlete=$id_athlete");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-athletes.php?id_athlete=$id_athlete");
        exit();
    }
}

// Récupérez les informations de l'user pour affichage dans le formulaire
try {
    $queryAthlete = "SELECT nom_athlete, prenom_athlete FROM ATHLETE WHERE id_athlete = :idAthlete";
    $statementAthlete = $connexion->prepare($queryAthlete);
    $statementAthlete->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_INT);
    $statementAthlete->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_INT);
    $statementAthlete->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
    $statementGenre->bindParam(":idGenre", $id_genre, PDO::PARAM_INT);

    if ($statementAthlete->rowCount() > 0) {
        $athlete = $statementAthlete->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "athlete non trouvé.";
        header("Location: manage-athletes.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-athletes.php");
    exit();
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
    <title>Modifier un Utiliateur - Jeux Olympiques 2024</title>
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
                <li><a href="manage-users.php">Gestion Utilisateur</a></li>
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
        <h1>Modifier un Athlete</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <!-- FORMULAIRE POUR LA DEMANDE DE MODIFICATION D'UN UTILISATEUR -->
        <form action="modify-athletes.php?id_athlete=<?php echo $id_athlete; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce athlete?')">
            <label for=" nomAthlete">Nom de l'Athlete :</label>
            <input type="text" name="nomAthlete" id="nomAthlete"
                value="<?php echo htmlspecialchars($nomAthlete['nom_athlete']); ?>" required>

                <label for=" prenomAthlete">Prenom de l'Athlete :</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete"
            value="<?php echo htmlspecialchars($prenomAthlete['prenom_athlete']); ?>" required>


            <label for=" nomGenre">Genre :</label>
            <input type="text" name="nomGenre" id="nomGenre"
                value="<?php echo htmlspecialchars($nomGenre['nom_genre']); ?>" required>

                <label for=" nomPays">Pays :</label>
            <input type="text" name="nomPays" id="nomPays"
                value="<?php echo htmlspecialchars($nomPays['nom_pays']); ?>" required>


            <input type="submit" value="Modifier l'Athlete">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des Athletes</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>