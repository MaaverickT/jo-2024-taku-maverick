<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'utilisateur est fourni dans l'URL utilisateur
if (!isset($_GET['id_utilisateur'])) {
    $_SESSION['error'] = "ID de l'utilisateur manquant.";
    header("Location: manage-users.php");
    exit();
}


$id_utilisateur = filter_input(INPUT_GET, 'id_utilisateur', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'utilisateur est un entier valide
if (!$id_utilisateur && $id_utilisateur !== 0) {
    $_SESSION['error'] = "ID de l'utilisateur invalide.";
    header("Location: manage-users.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomUtilisateur = filter_input(INPUT_POST, 'nomUtilisateur', FILTER_SANITIZE_STRING);
    $prenomUtilisateur = filter_input(INPUT_POST, 'prenomUtilisateur', FILTER_SANITIZE_STRING);
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    

    // Vérifiez si le nom de l'utilisateur est vide
    if (empty($nomUtilisateur) || empty($prenomUtilisateur) || empty($login) || empty($password)) { 
        $_SESSION['error'] = "L'un des champs ne peut pas être vide.";
        header("Location: add-users.php");
        exit();
    }

    try {
        // Vérifiez si l'utilisateur existe déjà
        $queryCheck = "SELECT id_utilisateur FROM UTILISATEUR WHERE nom_utilisateur, prenom_utilisateur, login, password = :nomUtilisateur, :prenomUtilisateur, :login, :password AND id_utilisateur <> :idUtilisateur";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomUtilisateur", $nomUtilisateur, PDO::PARAM_STR);
        $statementCheck->bindParam(":prenomUtilisateur", $prenomUtilisateur, PDO::PARAM_STR);
        $statementCheck->bindParam(":password", $password, PDO::PARAM_STR);
        $statementCheck->bindParam(":login", $login, PDO::PARAM_STR);
        $statementCheck->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'utilisateur existe déjà.";
            header("Location: modify-users.php?id_utilisateur=$id_utilisateur");
            exit();
        }

        // Requête pour mettre à jour l'utilisateur
        $query = "UPDATE UTILISATEUR SET nom_utilisateur = :nomUtilisateur, prenom_utilisateur = :prenomUtilisateur,  login = :login, password = :password WHERE id_utilisateur = :idUtilisateur";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomUtilisateur", $nomUtilisateur, PDO::PARAM_STR);
        $statement->bindParam(":prenomUtilisateur", $prenomUtilisateur, PDO::PARAM_STR);
        $statement->bindParam(":login", $login, PDO::PARAM_STR);
        $statement->bindParam(":password", $password, PDO::PARAM_STR);
        $statement->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'utilisateur a été modifié avec succès.";
            header("Location: manage-users.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de.";
            header("Location: modify-users.php?id_utilisateur=$id_utilisateur");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-users.php?id_utilisateur=$id_utilisateur");
        exit();
    }
}

// Récupérez les informations de l'user pour affichage dans le formulaire
try {
    $queryUtilisateur = "SELECT nom_utilisateur, prenom_utilisateur, login, password FROM UTILISATEUR WHERE id_utilisateur = :idUtilisateur";
    $statementUtilisateur = $connexion->prepare($queryUtilisateur);
    $statementUtilisateur->bindParam(":nomUtilisateur", $nomUtilisateur, PDO::PARAM_INT);
    $statementUtilisateur->bindParam(":prenomUtilisateur", $prenomUtilisateur, PDO::PARAM_INT);
    $statementUtilisateur->bindParam(":login", $login, PDO::PARAM_INT);
    $statementUtilisateur->bindParam(":password", $password, PDO::PARAM_INT);
    $statementUtilisateur->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
    $statementUtilisateur->execute();

    if ($statementUtilisateur->rowCount() > 0) {
        $utilisateur = $statementUtilisateur->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Utilisateur non trouvé.";
        header("Location: manage-users.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-users.php");
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
        <h1>Modifier un Utilisateur</h1>
        <?php 
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <!-- FORMULAIRE POUR LA DEMANDE DE MODIFICATION D'UN UTILISATEUR -->
        <form action="modify-users.php?id_utilisateur=<?php echo $id_utilisateur; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce utilisateur?')">
            <label for=" nomUtilisateur">Nom de l'Utilisateur :</label>
            <input type="text" name="nomUtilisateur" id="nomUtilisateur"
                value="<?php echo htmlspecialchars($nomUtilisateur['nom_utilisateur']); ?>" required>

                <label for=" prenomUtilisateur">Prenom de l'Utilisateur :</label>
            <input type="text" name="prenomUtilisateur" id="prenomUtilisateur"
                value="<?php echo htmlspecialchars($prenomUtilisateur['prenom_utilisateur']); ?>" required>

                <label for=" login">login</label>
            <input type="text" name="login" id="login"
                value="<?php echo htmlspecialchars($login['login']); ?>" required>

                <label for="password">Mot de passe</label>
            <input type="text" name="password" id="password"
                value="<?php echo htmlspecialchars($password['password']); ?>" required>
            <input type="submit" value="Modifier l'utilisateur">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-users.php">Retour à la gestion des Utilisateurs</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>